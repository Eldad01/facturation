<?php

namespace App\Support;

use Illuminate\Routing\Route;

/**
 * Construit le fil d'Ariane de la page courante à partir du nom de la route.
 *
 * Chaque élément est ['label' => string, 'url' => ?string]. Le dernier élément
 * (page courante) n'a pas d'URL. Le bouton "Retour" pointe vers l'avant-dernier.
 */
class Breadcrumbs
{
    /** @var array<int, array{label: string, url: ?string}> */
    private array $items = [];

    public static function forRoute(?Route $route): array
    {
        if (!$route || !$route->getName() || !auth()->check()) {
            return [];
        }

        return (new self)->build($route->getName(), $route);
    }

    private function add(string $label, ?string $url = null): self
    {
        $this->items[] = ['label' => $label, 'url' => $url];

        return $this;
    }

    private function home(): self
    {
        $url = auth()->user()->isAdmin() ? route('dashboard') : route('dashboard.employe');

        return $this->add('Tableau de bord', $url);
    }

    private function build(string $name, Route $route): array
    {
        $p = fn (string $key) => $route->parameter($key);

        // Pages racines : pas de fil d'Ariane
        if (in_array($name, ['dashboard', 'dashboard.employe', 'login'], true)) {
            return [];
        }

        $this->home();

        [$section] = explode('.', $name);

        switch ($section) {
            case 'clients':
                $this->add('Clients', route('clients.index'));
                $client = $p('client');
                match ($name) {
                    'clients.create' => $this->add('Nouveau client'),
                    'clients.show' => $this->add($client->nomComplet),
                    'clients.edit' => $this->add($client->nomComplet, route('clients.show', $client))->add('Modifier'),
                    default => null,
                };
                break;

            case 'factures':
            case 'devis':
                $facture = $p('facture');
                $onglet = match (true) {
                    $name === 'devis.create' => 'devis',
                    $name === 'factures.create' => 'attente',
                    !$facture => null,
                    $facture->type_document === 'pro-forma' => 'devis',
                    $facture->status === 'payee' => 'recus',
                    default => 'attente',
                };
                $this->add('Factures', route('factures.index', array_filter(['tab' => $onglet])));
                match ($name) {
                    'devis.create' => $this->add('Nouveau devis'),
                    'factures.create' => $this->add('Nouvelle facture'),
                    'factures.show' => $this->add($facture->numero_facture),
                    'factures.edit' => $this->add($facture->numero_facture, route('factures.show', $facture))->add('Modifier'),
                    default => null,
                };
                break;

            case 'produits':
                $this->add('Produits', route('produits.index'));
                $produit = $p('produit');
                if ($produit && $name !== 'produits.show') {
                    $this->add($produit->nom, route('produits.show', $produit));
                }
                match ($name) {
                    'produits.create' => $this->add('Nouveau produit'),
                    'produits.show' => $this->add($produit->nom),
                    'produits.edit' => $this->add('Modifier'),
                    'produits.reapprovisionnement' => $this->add('Réapprovisionnement'),
                    'produits.ajustement' => $this->add('Ajustement de stock'),
                    'produits.mouvements' => $this->add('Mouvements de stock'),
                    default => null,
                };
                break;

            case 'fournisseurs':
                $this->add('Fournisseurs', route('fournisseurs.index'));
                $fournisseur = $p('fournisseur');
                match ($name) {
                    'fournisseurs.create' => $this->add('Nouveau fournisseur'),
                    'fournisseurs.show' => $this->add($fournisseur->nom),
                    'fournisseurs.edit' => $this->add($fournisseur->nom, route('fournisseurs.show', $fournisseur))->add('Modifier'),
                    default => null,
                };
                break;

            case 'commandes_achat':
            case 'receptions_achat':
                $this->add("Commandes d'achat", route('commandes_achat.index'));
                $reception = $p('reception');
                $commande = $p('commande') ?? $reception?->commande;
                if ($name === 'commandes_achat.create') {
                    $this->add('Nouvelle commande');
                } elseif ($name === 'commandes_achat.show') {
                    $this->add($commande->numero);
                } elseif ($commande) {
                    $this->add($commande->numero, route('commandes_achat.show', $commande));
                    match ($name) {
                        'commandes_achat.edit' => $this->add('Modifier'),
                        'receptions_achat.create' => $this->add('Nouvelle réception'),
                        'receptions_achat.show' => $this->add('Réception ' . $reception->numero),
                        default => null,
                    };
                }
                break;

            case 'inventaires':
                $this->add('Inventaires', route('inventaires.index'));
                match ($name) {
                    'inventaires.create' => $this->add('Nouvel inventaire'),
                    'inventaires.show' => $this->add($p('inventaire')->reference),
                    default => null,
                };
                break;

            case 'rapports':
                $this->add('Rapports', route('rapports.index'));
                match ($name) {
                    'rapports.clients' => $this->add('État des clients'),
                    'rapports.factures' => $this->add('État des factures'),
                    'rapports.produits' => $this->add('État des produits'),
                    'rapports.ca_benefices' => $this->add("Chiffre d'affaires & bénéfices"),
                    default => null,
                };
                break;

            case 'historique':
                $this->add('Historique');
                break;

            case 'settings':
                $this->add('Paramètres');
                break;

            default:
                return [];
        }

        // La page courante n'est jamais un lien
        $last = array_key_last($this->items);
        $this->items[$last]['url'] = null;

        return $this->items;
    }
}
