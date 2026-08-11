<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaginationBoostSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->pousserPlusDeStockBas();
            $this->ajouterPlusDeFactures();
        });
    }

    /**
     * Pousse davantage de produits existants sous leur seuil d'alerte
     * pour avoir largement plus de 5 éléments à paginer.
     */
    private function pousserPlusDeStockBas(): void
    {
        $candidats = Produit::whereColumn('stock', '>', 'seuil_alerte')
            ->inRandomOrder()
            ->limit(15)
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
                'raison' => 'Ajustement inventaire (démo - test pagination)',
            ]);
        }
    }

    /**
     * Ajoute davantage de factures, réparties sur tous les utilisateurs
     * (y compris l'employé standard) pour tester la pagination du dashboard employé.
     */
    private function ajouterPlusDeFactures(): void
    {
        $clients = Client::inRandomOrder()->limit(20)->get();
        $produits = Produit::all();
        $auteurs = User::whereIn('email', [
            'admin@facturation.com',
            'user@facturation.com',
            'employe2@facturation.com',
        ])->get();

        $compteurJour = [];

        // Beaucoup de factures pour l'employé "user@facturation.com" spécifiquement
        $userPrincipal = $auteurs->firstWhere('email', 'user@facturation.com');

        for ($i = 0; $i < 40; $i++) {
            $date = fake()->dateTimeBetween('-3 months', '-1 day');
            $client = $clients->random();
            $auteur = $i < 20 && $userPrincipal ? $userPrincipal : $auteurs->random();
            $typeDocument = fake()->boolean(60) ? 'recu' : 'pro-forma';

            $facture = Facture::create([
                'client_id' => $client->id,
                'user_id' => $auteur->id,
                'type_document' => $typeDocument,
                'status' => $typeDocument === 'recu' ? 'recu' : 'pro-forma',
                'numero_facture' => $this->numeroUnique($typeDocument === 'recu' ? 'R' : 'PF', $date, $compteurJour),
                'total' => 0,
                'montant_paye' => 0,
                'modifiable' => $typeDocument === 'pro-forma',
                'remise' => 0,
                'tva' => 18,
                'date_echeance' => (clone $date)->modify('+' . fake()->numberBetween(-10, 20) . ' days'),
            ]);
            $facture->created_at = $date;
            $facture->updated_at = $date;
            $facture->save();

            $subtotal = 0;
            $lignesProduits = $produits->random(fake()->numberBetween(1, 3));

            foreach (collect([$lignesProduits])->flatten() as $produit) {
                $quantite = fake()->numberBetween(1, 3);

                if ($typeDocument === 'recu') {
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

                if ($typeDocument === 'recu') {
                    MouvementStock::create([
                        'produit_id' => $produit->id,
                        'type' => 'sortie',
                        'quantite' => $quantite,
                        'raison' => "Vente facture {$facture->numero_facture}",
                    ]);
                }
            }

            $totalTtc = (int) round($subtotal * 1.18);
            $facture->update(['total' => $totalTtc]);

            if ($typeDocument === 'recu' && fake()->boolean(50)) {
                $montantPaye = fake()->boolean(70) ? $totalTtc : (int) round($totalTtc * fake()->randomFloat(2, 0.3, 0.7));

                Paiement::create([
                    'facture_id' => $facture->id,
                    'montant' => $montantPaye,
                    'mode' => fake()->randomElement(['especes', 'mobile_money', 'virement', 'carte']),
                    'date_paiement' => $date,
                ]);

                $facture->update([
                    'montant_paye' => $montantPaye,
                    'status' => $montantPaye >= $totalTtc ? 'payee' : 'partiellement_payee',
                ]);
            }
        }
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
