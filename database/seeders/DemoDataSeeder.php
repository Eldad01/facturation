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
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $users = $this->seedUsers();
        $clients = $this->seedClients();
        $fournisseurs = $this->seedFournisseurs();
        $produits = $this->seedProduits();
        $this->linkProduitsFournisseurs($produits, $fournisseurs);
        $this->seedCommandesAchat($fournisseurs, $produits, $users);
        $this->seedFactures($clients, $produits, $users);
    }

    private function seedSettings(): void
    {
        if (Setting::count() > 0) {
            return;
        }

        Setting::create([
            'company_name' => 'Ets Sahel Commerce',
            'address' => 'Secteur 15, Avenue Kwame Nkrumah, Ouagadougou',
            'phone' => '+22625301122',
            'email' => 'contact@sahelcommerce.bf',
            'ifu' => '00012345A',
            'rccm' => 'BF-OUA-2019-B-1234',
            'country' => 'Burkina Faso',
            'national_motto' => 'Unité - Progrès - Justice',
            'devise' => 'FCFA',
            'tva_default' => 18,
            'footer_text' => 'Merci pour votre confiance.',
        ]);
    }

    private function seedUsers(): array
    {
        $employe2 = User::updateOrCreate(
            ['email' => 'employe2@facturation.com'],
            [
                'name' => 'Aïcha Compaoré',
                'password' => Hash::make('user123'),
                'role' => 'employe',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'inactif@facturation.com'],
            [
                'name' => 'Compte Désactivé',
                'password' => Hash::make('user123'),
                'role' => 'employe',
                'is_active' => false,
            ]
        );

        return [
            'admin' => User::where('email', 'admin@facturation.com')->first(),
            'employe1' => User::where('email', 'user@facturation.com')->first(),
            'employe2' => $employe2,
        ];
    }

    private function seedClients(): \Illuminate\Support\Collection
    {
        $clients = collect();

        for ($i = 0; $i < 18; $i++) {
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

        return $clients;
    }

    private function seedFournisseurs(): \Illuminate\Support\Collection
    {
        $noms = [
            'Faso Distribution', 'Sahel Import-Export', 'Burkina Matériaux',
            'Ouaga Textile', 'Groupe Nakoulma & Fils', 'Comptoir du Sahel',
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

        return $fournisseurs;
    }

    private function seedProduits(): \Illuminate\Support\Collection
    {
        $catalogue = [
            ['nom' => 'Sac de ciment 50kg', 'categorie' => 'Matériaux', 'unite' => 'sac', 'prix_achat' => 4500, 'prix_vente' => 5500],
            ['nom' => 'Brique creuse 15x20x40', 'categorie' => 'Matériaux', 'unite' => 'unité', 'prix_achat' => 250, 'prix_vente' => 350],
            ['nom' => 'Fer à béton 12mm', 'categorie' => 'Matériaux', 'unite' => 'barre', 'prix_achat' => 6000, 'prix_vente' => 7200],
            ['nom' => 'Riz parfumé 25kg', 'categorie' => 'Alimentation', 'unite' => 'sac', 'prix_achat' => 12000, 'prix_vente' => 14500],
            ['nom' => 'Huile végétale 5L', 'categorie' => 'Alimentation', 'unite' => 'bidon', 'prix_achat' => 5200, 'prix_vente' => 6000],
            ['nom' => 'Sucre en poudre 1kg', 'categorie' => 'Alimentation', 'unite' => 'paquet', 'prix_achat' => 650, 'prix_vente' => 800],
            ['nom' => 'Lait en poudre 400g', 'categorie' => 'Alimentation', 'unite' => 'boîte', 'prix_achat' => 2200, 'prix_vente' => 2700],
            ['nom' => 'Eau minérale 1.5L', 'categorie' => 'Boissons', 'unite' => 'bouteille', 'prix_achat' => 350, 'prix_vente' => 500],
            ['nom' => 'Jus de fruits 1L', 'categorie' => 'Boissons', 'unite' => 'bouteille', 'prix_achat' => 900, 'prix_vente' => 1200],
            ['nom' => 'Savon de Marseille', 'categorie' => 'Hygiène', 'unite' => 'unité', 'prix_achat' => 400, 'prix_vente' => 600],
            ['nom' => 'Pâte dentifrice', 'categorie' => 'Hygiène', 'unite' => 'tube', 'prix_achat' => 550, 'prix_vente' => 800],
            ['nom' => 'Papier hygiénique (paquet de 4)', 'categorie' => 'Hygiène', 'unite' => 'paquet', 'prix_achat' => 1200, 'prix_vente' => 1600],
            ['nom' => 'Ampoule LED 9W', 'categorie' => 'Électronique', 'unite' => 'unité', 'prix_achat' => 1000, 'prix_vente' => 1500],
            ['nom' => 'Câble électrique 2.5mm (rouleau)', 'categorie' => 'Électronique', 'unite' => 'rouleau', 'prix_achat' => 15000, 'prix_vente' => 18500],
            ['nom' => 'Rallonge électrique 5m', 'categorie' => 'Électronique', 'unite' => 'unité', 'prix_achat' => 3500, 'prix_vente' => 4800],
            ['nom' => 'Pagne wax 6 yards', 'categorie' => 'Textile', 'unite' => 'pièce', 'prix_achat' => 8000, 'prix_vente' => 11000],
            ['nom' => 'Tee-shirt coton', 'categorie' => 'Textile', 'unite' => 'unité', 'prix_achat' => 2000, 'prix_vente' => 3200],
            ['nom' => 'Chaussures de sécurité', 'categorie' => 'Textile', 'unite' => 'paire', 'prix_achat' => 9500, 'prix_vente' => 13000],
            ['nom' => 'Peinture blanche 20L', 'categorie' => 'Matériaux', 'unite' => 'seau', 'prix_achat' => 22000, 'prix_vente' => 27000],
            ['nom' => 'Tuyau PVC 100mm (3m)', 'categorie' => 'Matériaux', 'unite' => 'barre', 'prix_achat' => 4200, 'prix_vente' => 5300],
            ['nom' => 'Farine de blé 25kg', 'categorie' => 'Alimentation', 'unite' => 'sac', 'prix_achat' => 9500, 'prix_vente' => 11200],
            ['nom' => 'Boisson gazeuse 1.5L', 'categorie' => 'Boissons', 'unite' => 'bouteille', 'prix_achat' => 700, 'prix_vente' => 950],
            ['nom' => 'Gel douche 500ml', 'categorie' => 'Hygiène', 'unite' => 'flacon', 'prix_achat' => 1300, 'prix_vente' => 1800],
            ['nom' => 'Multiprise 6 prises', 'categorie' => 'Électronique', 'unite' => 'unité', 'prix_achat' => 4500, 'prix_vente' => 6200],
            ['nom' => 'Casquette brodée', 'categorie' => 'Textile', 'unite' => 'unité', 'prix_achat' => 1500, 'prix_vente' => 2500],
        ];

        $produits = collect();

        foreach ($catalogue as $index => $item) {
            $seuilAlerte = fake()->numberBetween(5, 20);
            // Une poignée de produits volontairement sous le seuil (test des alertes stock)
            $stockInitial = $index % 6 === 0
                ? fake()->numberBetween(0, $seuilAlerte - 1)
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

        return $produits;
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

    private function seedCommandesAchat($fournisseurs, $produits, $users): void
    {
        $statuts = ['brouillon', 'confirmee', 'confirmee', 'reçue_partiellement', 'reçue', 'reçue', 'annulee'];

        foreach ($statuts as $i => $statut) {
            $fournisseur = $fournisseurs->random();
            $dateCommande = fake()->dateTimeBetween('-3 months', '-1 week');

            $commande = CommandeAchat::create([
                'numero' => 'CMD-' . $dateCommande->format('YmdHis') . $i,
                'fournisseur_id' => $fournisseur->id,
                'statut' => 'brouillon',
                'date_commande' => $dateCommande,
                'date_reception_prevue' => (clone $dateCommande)->modify('+10 days'),
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            ]);

            $lignesProduits = $produits->random(fake()->numberBetween(2, 4));
            foreach ($lignesProduits as $produit) {
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

            if ($statut === 'annulee') {
                $commande->update(['statut' => 'annulee']);
                continue;
            }

            if ($statut === 'confirmee') {
                continue;
            }

            if (in_array($statut, ['reçue_partiellement', 'reçue'], true)) {
                $dateReception = (clone $dateCommande)->modify('+' . fake()->numberBetween(3, 12) . ' days');
                $reception = ReceptionAchat::create([
                    'numero' => 'REC-' . $dateReception->format('YmdHis') . $i,
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
    }

    private function seedFactures($clients, $produits, $users): void
    {
        $compteurJour = [];
        $auteurs = array_values(array_filter($users));

        // --- Reçus (ventes) : payés, partiellement payés, impayés ---
        $repartitionRecu = array_merge(
            array_fill(0, 8, 'payee'),
            array_fill(0, 4, 'partiellement_payee'),
            array_fill(0, 3, 'impayee')
        );

        foreach ($repartitionRecu as $i => $etat) {
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

            $this->creerLignesFacture($facture, $produits, 'recu', $date);

            // Avance versée par le client avant livraison/impression (facultative, indépendante des paiements formels)
            if (fake()->boolean(35)) {
                $facture->refresh();
                $facture->update(['avance' => (int) round($facture->total * fake()->randomFloat(2, 0.15, 0.4))]);
            }

            if ($etat === 'payee') {
                $this->payerLignesFacture($facture, $facture->lignes->pluck('quantite', 'id')->toArray(), $date);
            } elseif ($etat === 'partiellement_payee') {
                $this->payerPartiellement($facture, $date, 0.3, 0.7);
            }
            // 'impayee' : rien à payer
        }

        // --- Pro-formas : certaines en retard, d'autres non ---
        $repartitionProforma = array_merge(
            array_fill(0, 3, 'en_retard'),
            array_fill(0, 7, 'a_jour')
        );

        foreach ($repartitionProforma as $i => $etat) {
            $date = fake()->dateTimeBetween('-2 months', '-2 days');
            $client = $clients->random();
            $auteur = fake()->randomElement($auteurs);

            $echeance = $etat === 'en_retard'
                ? fake()->dateTimeBetween('-3 weeks', '-1 day')
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

            $this->creerLignesFacture($facture, $produits, 'pro-forma', $date);
        }

        // --- Quelques factures annulées (sans paiement, cohérent avec la règle métier) ---
        for ($i = 0; $i < 3; $i++) {
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

            $this->creerLignesFacture($facture, $produits, 'pro-forma', $date);
        }
    }

    private function creerLignesFacture(Facture $facture, $produits, string $type, \DateTime $date): int
    {
        $lignesProduits = $produits->random(fake()->numberBetween(1, 4));
        $subtotal = 0;

        foreach (collect([$lignesProduits])->flatten() as $produit) {
            $quantite = fake()->numberBetween(1, 5);

            if ($type === 'recu') {
                // Ne jamais vendre plus que le stock réellement disponible au moment du seed
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
                // Décrémenter immédiatement pour que les lignes suivantes voient le stock à jour
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
}
