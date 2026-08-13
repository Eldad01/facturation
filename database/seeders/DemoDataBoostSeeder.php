<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CommandeAchat;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\LigneCommandeAchat;
use App\Models\LigneFacture;
use App\Models\LignePaiement;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\ReceptionAchat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataBoostSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $users = [
                'admin' => User::where('email', 'admin@facturation.com')->first(),
                'employe1' => User::where('email', 'user@facturation.com')->first(),
                'employe2' => User::where('email', 'employe2@facturation.com')->first(),
            ];

            $clients = $this->seedMoreClients();
            $fournisseurs = $this->seedMoreFournisseurs();
            $produits = $this->seedMoreProduits();
            $this->linkProduitsFournisseurs($produits, $fournisseurs);
            $this->seedMoreCommandesAchat($fournisseurs, $produits);
            $this->seedMoreFactures($clients, $produits, $users);

            $this->pousserStockBasExistant();
            $this->pousserRetardExistant();
        });
    }

    private function seedMoreClients(): \Illuminate\Support\Collection
    {
        $clients = collect();

        for ($i = 0; $i < 15; $i++) {
            $hasEmail = fake()->boolean(65);
            $nom = strtoupper(fake()->lastName());
            $prenom = ucfirst(fake()->firstName());

            $clients->push(Client::create([
                'nom' => $nom,
                'prenom' => $prenom,
                'telephone' => '+226' . fake()->unique()->numerify('7#######'),
                'email' => $hasEmail ? strtolower($prenom . '.' . $nom . '@' . fake()->safeEmailDomain()) : null,
                'adresse' => fake()->boolean(70) ? fake()->streetAddress() . ', Ouagadougou' : null,
            ]));
        }

        return $clients->merge(Client::inRandomOrder()->limit(10)->get());
    }

    private function seedMoreFournisseurs(): \Illuminate\Support\Collection
    {
        $noms = [
            'Quincaillerie du Faso', 'Grossiste Nord', 'Import Sahel Plus', 'Distributions Zongo',
        ];

        $fournisseurs = collect();

        foreach ($noms as $nom) {
            $fournisseurs->push(Fournisseur::create([
                'nom' => $nom,
                'email' => fake()->boolean(80) ? strtolower(str_replace([' ', '&', "'"], ['', 'et', ''], $nom)) . '@fournisseur.bf' : null,
                'telephone' => '+226' . fake()->numerify('7#######'),
                'adresse' => fake()->streetAddress(),
                'ville' => fake()->randomElement(['Ouagadougou', 'Bobo-Dioulasso', 'Koudougou']),
                'code_postal' => fake()->numerify('####'),
                'pays' => 'Burkina Faso',
                'notes' => fake()->boolean(30) ? 'Partenaire fiable, délais respectés.' : null,
                'actif' => fake()->boolean(90),
            ]));
        }

        return $fournisseurs->merge(Fournisseur::inRandomOrder()->limit(6)->get());
    }

    private function seedMoreProduits(): \Illuminate\Support\Collection
    {
        $catalogue = [
            ['nom' => 'Ciment blanc 25kg', 'categorie' => 'Matériaux', 'unite' => 'sac', 'prix_achat' => 6500, 'prix_vente' => 7800],
            ['nom' => 'Clou 5cm (boîte 1kg)', 'categorie' => 'Matériaux', 'unite' => 'boîte', 'prix_achat' => 1200, 'prix_vente' => 1600],
            ['nom' => 'Fil de fer galvanisé (rouleau)', 'categorie' => 'Matériaux', 'unite' => 'rouleau', 'prix_achat' => 8500, 'prix_vente' => 10200],
            ['nom' => 'Interrupteur simple', 'categorie' => 'Électronique', 'unite' => 'unité', 'prix_achat' => 800, 'prix_vente' => 1200],
            ['nom' => 'Prise électrique murale', 'categorie' => 'Électronique', 'unite' => 'unité', 'prix_achat' => 900, 'prix_vente' => 1400],
            ['nom' => 'Robinet de cuisine', 'categorie' => 'Matériaux', 'unite' => 'unité', 'prix_achat' => 7500, 'prix_vente' => 9800],
            ['nom' => 'Carrelage 30x30', 'categorie' => 'Matériaux', 'unite' => 'm²', 'prix_achat' => 3500, 'prix_vente' => 4500],
            ['nom' => 'Colle carrelage 25kg', 'categorie' => 'Matériaux', 'unite' => 'sac', 'prix_achat' => 4800, 'prix_vente' => 5900],
            ['nom' => 'Gants de travail', 'categorie' => 'Textile', 'unite' => 'paire', 'prix_achat' => 900, 'prix_vente' => 1500],
            ['nom' => 'Casque de chantier', 'categorie' => 'Textile', 'unite' => 'unité', 'prix_achat' => 3200, 'prix_vente' => 4500],
            ['nom' => 'Brouette', 'categorie' => 'Matériaux', 'unite' => 'unité', 'prix_achat' => 28000, 'prix_vente' => 35000],
            ['nom' => 'Pelle ronde', 'categorie' => 'Matériaux', 'unite' => 'unité', 'prix_achat' => 3500, 'prix_vente' => 4800],
            ['nom' => 'Râteau', 'categorie' => 'Matériaux', 'unite' => 'unité', 'prix_achat' => 2800, 'prix_vente' => 3900],
            ['nom' => 'Seau plastique 15L', 'categorie' => 'Hygiène', 'unite' => 'unité', 'prix_achat' => 1000, 'prix_vente' => 1500],
            ['nom' => 'Corde nylon 20m', 'categorie' => 'Matériaux', 'unite' => 'unité', 'prix_achat' => 2200, 'prix_vente' => 3000],
            ['nom' => 'Biscuits sachet familial', 'categorie' => 'Alimentation', 'unite' => 'paquet', 'prix_achat' => 900, 'prix_vente' => 1300],
            ['nom' => 'Café soluble 200g', 'categorie' => 'Alimentation', 'unite' => 'boîte', 'prix_achat' => 2500, 'prix_vente' => 3200],
            ['nom' => 'Détergent poudre 1kg', 'categorie' => 'Hygiène', 'unite' => 'paquet', 'prix_achat' => 1100, 'prix_vente' => 1600],
            ['nom' => 'Ventilateur de table', 'categorie' => 'Électronique', 'unite' => 'unité', 'prix_achat' => 12000, 'prix_vente' => 16500],
            ['nom' => 'Bouilloire électrique', 'categorie' => 'Électronique', 'unite' => 'unité', 'prix_achat' => 9500, 'prix_vente' => 13000],
        ];

        $produits = collect();

        foreach ($catalogue as $index => $item) {
            $seuilAlerte = fake()->numberBetween(5, 20);

            // Nettement plus de produits sous le seuil cette fois (~45%)
            $stockInitial = fake()->boolean(45)
                ? fake()->numberBetween(0, max(0, $seuilAlerte - 1))
                : fake()->numberBetween($seuilAlerte + 10, $seuilAlerte + 200);

            $produit = Produit::create([
                'nom' => $item['nom'],
                'sku' => 'SKU-' . strtoupper(fake()->bothify('??###')),
                'categorie' => $item['categorie'],
                'famille' => $item['categorie'],
                'unite' => $item['unite'],
                'description' => fake()->boolean(40) ? fake()->sentence(8) : null,
                'prix_achat' => $item['prix_achat'],
                'prix_vente' => $item['prix_vente'],
                'stock' => 0,
                'seuil_alerte' => $seuilAlerte,
            ]);

            if ($stockInitial > 0) {
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'type' => 'entree',
                    'quantite' => $stockInitial,
                    'raison' => 'Stock initial (import démo)',
                ]);
            }

            $produits->push($produit->fresh());
        }

        return $produits->merge(Produit::inRandomOrder()->limit(15)->get());
    }

    private function linkProduitsFournisseurs($produits, $fournisseurs): void
    {
        foreach ($produits as $produit) {
            $nbFournisseurs = fake()->numberBetween(1, 2);
            $choisis = $fournisseurs->random(min($nbFournisseurs, $fournisseurs->count()));
            $choisis = $choisis instanceof \Illuminate\Support\Collection ? $choisis : collect([$choisis]);

            foreach ($choisis as $fournisseur) {
                $fournisseur->produits()->syncWithoutDetaching([
                    $produit->id => [
                        'prix_achat' => $produit->prix_achat,
                        'delai_livraison' => fake()->numberBetween(2, 15),
                        'reference_fournisseur' => strtoupper(fake()->bothify('REF-####')),
                        'quantite_minimum' => fake()->numberBetween(5, 50),
                        'prefere' => fake()->boolean(50),
                    ],
                ]);
            }
        }
    }

    private function seedMoreCommandesAchat($fournisseurs, $produits): void
    {
        $statuts = [
            'brouillon', 'brouillon',
            'confirmee', 'confirmee', 'confirmee',
            'reçue_partiellement', 'reçue_partiellement',
            'reçue', 'reçue', 'reçue',
        ];

        foreach ($statuts as $i => $statut) {
            $fournisseur = $fournisseurs->random();
            $dateCommande = fake()->dateTimeBetween('-3 months', '-1 week');

            $commande = CommandeAchat::create([
                'numero' => 'CMD-' . $dateCommande->format('YmdHis') . $i . fake()->numberBetween(100, 999),
                'fournisseur_id' => $fournisseur->id,
                'statut' => 'brouillon',
                'date_commande' => $dateCommande,
                'date_reception_prevue' => (clone $dateCommande)->modify('+10 days'),
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            ]);

            $lignesProduits = $produits->random(fake()->numberBetween(2, 4));
            foreach (collect([$lignesProduits])->flatten() as $produit) {
                $quantite = fake()->numberBetween(10, 100);
                LigneCommandeAchat::create([
                    'commande_achat_id' => $commande->id,
                    'produit_id' => $produit->id,
                    'quantite' => $quantite,
                    'prix_unitaire' => $produit->prix_achat,
                    'montant' => $quantite * $produit->prix_achat,
                ]);
            }

            if ($statut === 'brouillon') {
                continue;
            }

            $commande->update(['statut' => 'confirmee']);

            if ($statut === 'confirmee') {
                continue;
            }

            $dateReception = (clone $dateCommande)->modify('+' . fake()->numberBetween(3, 12) . ' days');
            $reception = ReceptionAchat::create([
                'numero' => 'REC-' . $dateReception->format('YmdHis') . $i . fake()->numberBetween(100, 999),
                'commande_achat_id' => $commande->id,
                'date_reception' => $dateReception,
                'notes' => null,
            ]);

            foreach ($commande->lignes as $ligne) {
                $quantiteRecue = $statut === 'reçue_partiellement'
                    ? (int) floor($ligne->quantite * fake()->randomFloat(2, 0.3, 0.7))
                    : $ligne->quantite;

                if ($quantiteRecue <= 0) {
                    continue;
                }

                $ligne->receptionsLignes()->create([
                    'reception_achat_id' => $reception->id,
                    'quantite_reçue' => $quantiteRecue,
                ]);

                $ligne->update(['quantite_reçue' => $quantiteRecue]);

                MouvementStock::create([
                    'produit_id' => $ligne->produit_id,
                    'type' => 'entree',
                    'quantite' => $quantiteRecue,
                    'raison' => "Réception achat - Commande {$commande->numero}",
                ]);
            }

            $commande->refresh();
            $statutFinal = $commande->quantite_reçue >= $commande->quantite_total ? 'reçue' : 'reçue_partiellement';
            $commande->update([
                'statut' => $statutFinal,
                'date_reception' => $statutFinal === 'reçue' ? $dateReception : null,
            ]);
        }
    }

    private function seedMoreFactures($clients, $produits, $users): void
    {
        $compteurJour = [];
        $auteurs = array_values(array_filter($users));

        // --- Reçus : payés, partiels, impayés ---
        $repartitionRecu = array_merge(
            array_fill(0, 12, 'payee'),
            array_fill(0, 6, 'partiellement_payee'),
            array_fill(0, 5, 'impayee')
        );

        foreach ($repartitionRecu as $etat) {
            $date = fake()->dateTimeBetween('-4 months', '-1 day');
            $client = $clients->random();
            $auteur = fake()->randomElement($auteurs);

            $facture = Facture::create([
                'client_id' => $client->id,
                'user_id' => $auteur->id,
                'type_document' => 'recu',
                'status' => 'recu',
                'numero_facture' => $this->numeroUnique('R', $date, $compteurJour),
                'total' => 0,
                'montant_paye' => 0,
                'modifiable' => false,
                'remise' => fake()->boolean(20) ? fake()->numberBetween(500, 3000) : 0,
                'tva' => 18,
                'date_echeance' => (clone $date)->modify('+' . fake()->numberBetween(7, 30) . ' days'),
            ]);
            $facture->created_at = $date;
            $facture->updated_at = $date;
            $facture->save();

            $this->creerLignesFacture($facture, $produits, 'recu');

            if ($etat === 'payee') {
                $this->payerLignesFacture($facture, $facture->lignes->pluck('quantite', 'id')->toArray(), $date);
            } elseif ($etat === 'partiellement_payee') {
                $this->payerPartiellement($facture, $date, 0.3, 0.7);
            }
        }

        // --- Pro-formas : nettement plus de retard cette fois (~55%) ---
        $repartitionProforma = array_merge(
            array_fill(0, 11, 'en_retard'),
            array_fill(0, 9, 'a_jour')
        );

        foreach ($repartitionProforma as $etat) {
            $date = fake()->dateTimeBetween('-2 months', '-2 days');
            $client = $clients->random();
            $auteur = fake()->randomElement($auteurs);

            $echeance = $etat === 'en_retard'
                ? fake()->dateTimeBetween('-6 weeks', '-1 day')
                : fake()->dateTimeBetween('+3 days', '+3 weeks');

            $facture = Facture::create([
                'client_id' => $client->id,
                'user_id' => $auteur->id,
                'type_document' => 'pro-forma',
                'status' => 'pro-forma',
                'numero_facture' => $this->numeroUnique('PF', $date, $compteurJour),
                'total' => 0,
                'montant_paye' => 0,
                'modifiable' => true,
                'remise' => 0,
                'tva' => 18,
                'date_echeance' => $echeance,
            ]);
            $facture->created_at = $date;
            $facture->updated_at = $date;
            $facture->save();

            $this->creerLignesFacture($facture, $produits, 'pro-forma');
        }

        // --- Quelques factures annulées supplémentaires ---
        for ($i = 0; $i < 4; $i++) {
            $date = fake()->dateTimeBetween('-2 months', '-1 week');
            $client = $clients->random();
            $auteur = fake()->randomElement($auteurs);

            $facture = Facture::create([
                'client_id' => $client->id,
                'user_id' => $auteur->id,
                'type_document' => 'pro-forma',
                'status' => 'annule',
                'numero_facture' => $this->numeroUnique('PF', $date, $compteurJour),
                'total' => 0,
                'montant_paye' => 0,
                'modifiable' => false,
                'remise' => 0,
                'tva' => 18,
                'date_echeance' => (clone $date)->modify('+15 days'),
            ]);
            $facture->created_at = $date;
            $facture->updated_at = $date;
            $facture->save();

            $this->creerLignesFacture($facture, $produits, 'pro-forma');
        }
    }

    private function creerLignesFacture(Facture $facture, $produits, string $type): int
    {
        $lignesProduits = $produits->random(fake()->numberBetween(1, 4));
        $subtotal = 0;

        foreach (collect([$lignesProduits])->flatten() as $produit) {
            $quantite = fake()->numberBetween(1, 5);

            if ($type === 'recu') {
                $stockDispo = $produit->fresh()->stock;
                if ($stockDispo < 1) {
                    continue;
                }
                $quantite = min($quantite, $stockDispo);
            }

            $prixUnitaire = $produit->prix_vente;
            $totalLigne = $quantite * $prixUnitaire;
            $subtotal += $totalLigne;

            LigneFacture::create([
                'facture_id' => $facture->id,
                'produit_id' => $produit->id,
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'total_ligne' => $totalLigne,
                'remise' => 0,
            ]);

            if ($type === 'recu') {
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'type' => 'sortie',
                    'quantite' => $quantite,
                    'raison' => "Vente facture {$facture->numero_facture}",
                ]);
            }
        }

        $totalHt = max(0, $subtotal - $facture->remise);
        $totalTtc = (int) round($totalHt * (1 + $facture->tva / 100));

        $facture->update(['total' => $totalTtc]);

        return $totalTtc;
    }

    /**
     * Règle un pourcentage aléatoire des unités de la facture (paiement partiel),
     * en respectant la règle "une pièce se paie en entier, jamais en tranches".
     */
    private function payerPartiellement(Facture $facture, \DateTime $date, float $min, float $max): void
    {
        $lignes = $facture->lignes;
        $totalUnites = $lignes->sum('quantite');

        if ($totalUnites <= 1) {
            return;
        }

        $fraction = fake()->randomFloat(2, $min, $max);
        $unitesAPayer = max(1, min($totalUnites - 1, (int) round($totalUnites * $fraction)));

        $selections = [];
        $restant = $unitesAPayer;

        foreach ($lignes->shuffle() as $ligne) {
            if ($restant <= 0) {
                break;
            }
            $qte = min($ligne->quantite, $restant);
            $selections[$ligne->id] = $qte;
            $restant -= $qte;
        }

        $this->payerLignesFacture($facture, $selections, $date);
    }

    /**
     * Enregistre un paiement couvrant les quantités données par ligne
     * (reproduit exactement la logique de PaiementController::store()).
     *
     * @param array<int,int> $selections [ligne_facture_id => quantite]
     */
    private function payerLignesFacture(Facture $facture, array $selections, \DateTime $date): void
    {
        $selections = array_filter($selections, fn($q) => $q > 0);

        if (empty($selections)) {
            return;
        }

        $datePaiement = (clone $date)->modify('+' . fake()->numberBetween(0, 5) . ' days');

        $paiement = Paiement::create([
            'facture_id' => $facture->id,
            'montant' => 0,
            'mode' => fake()->randomElement(['especes', 'mobile_money', 'virement', 'carte']),
            'reference' => fake()->boolean(50) ? strtoupper(fake()->bothify('PAY-####??')) : null,
            'date_paiement' => $datePaiement,
        ]);
        $paiement->created_at = $datePaiement;
        $paiement->updated_at = $datePaiement;
        $paiement->save();

        $montantTotal = 0;

        foreach ($selections as $ligneId => $quantite) {
            $ligne = LigneFacture::find($ligneId);
            $prixUnitaireNet = $ligne->quantite > 0 ? $ligne->total_ligne / $ligne->quantite : 0;
            $nouvelleQuantitePayee = $ligne->quantite_payee + $quantite;

            if ($nouvelleQuantitePayee >= $ligne->quantite) {
                $montantDejaPaye = LignePaiement::where('ligne_facture_id', $ligne->id)->sum('montant');
                $montantLigne = $ligne->total_ligne - $montantDejaPaye;
            } else {
                $montantLigne = (int) round($prixUnitaireNet * $quantite);
            }

            LignePaiement::create([
                'paiement_id' => $paiement->id,
                'ligne_facture_id' => $ligne->id,
                'quantite' => $quantite,
                'montant' => $montantLigne,
            ]);

            $ligne->update(['quantite_payee' => $nouvelleQuantitePayee]);

            $montantTotal += $montantLigne;
        }

        $paiement->update(['montant' => $montantTotal]);

        $facture->montant_paye = $facture->montant_paye + $montantTotal;
        $toutesPayees = $facture->lignes()->whereColumn('quantite_payee', '<', 'quantite')->doesntExist();
        $facture->status = $toutesPayees ? 'payee' : 'partiellement_payee';
        $facture->date_paiement = $toutesPayees ? $datePaiement : null;
        $facture->save();
    }

    private function numeroUnique(string $prefix, \DateTime $date, array &$compteur): string
    {
        $base = $prefix . $date->format('Ymd');

        do {
            $compteur[$base] = ($compteur[$base] ?? 0) + 1;
            $numero = $base . str_pad((string) $compteur[$base], 3, '0', STR_PAD_LEFT);
        } while (Facture::where('numero_facture', $numero)->exists());

        return $numero;
    }

    /**
     * Pousse une partie des produits déjà en base sous leur seuil d'alerte,
     * en plus des nouveaux produits déjà créés bas.
     */
    private function pousserStockBasExistant(): void
    {
        $candidats = Produit::whereColumn('stock', '>', 'seuil_alerte')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        foreach ($candidats as $produit) {
            $cible = fake()->numberBetween(0, max(0, $produit->seuil_alerte - 1));
            $diff = $produit->stock - $cible;

            if ($diff <= 0) {
                continue;
            }

            MouvementStock::create([
                'produit_id' => $produit->id,
                'type' => 'sortie',
                'quantite' => $diff,
                'raison' => 'Ajustement inventaire (démo - stock bas)',
            ]);
        }
    }

    /**
     * Fait passer plusieurs pro-formas déjà en base en retard.
     */
    private function pousserRetardExistant(): void
    {
        $candidats = Facture::where('type_document', 'pro-forma')
            ->where('status', 'pro-forma')
            ->where('date_echeance', '>=', now())
            ->inRandomOrder()
            ->limit(6)
            ->get();

        foreach ($candidats as $facture) {
            $facture->update([
                'date_echeance' => fake()->dateTimeBetween('-5 weeks', '-2 days'),
            ]);
        }
    }
}
