<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Corrige les incohérences des données de démonstration (à lancer après LibrairieDataSeeder).
 *
 *  1. Paiements recalculés en TTC (les anciens ignoraient TVA et remise globale).
 *  2. Échéances antérieures à la date de facture -> date de facture + 30 jours.
 *  3. Reçus vides (aucune ligne, 0 FCFA) -> annulés.
 *  4. Chronologie : utilisateurs, fournisseurs, produits, clients, mouvements de stock,
 *     commandes, réceptions et lignes datés de façon cohérente avec les factures.
 *  5. Liens produit-fournisseur manquants pour les produits déjà commandés.
 *  6. Motifs de mouvements "démo" remplacés par des motifs réalistes.
 *
 * Rejouable : chaque correction est calculée à partir des données de référence
 * (quantités, dates de factures/réceptions), un second passage ne change rien.
 *   php artisan db:seed --class=LibrairieCoherenceSeeder
 */
class LibrairieCoherenceSeeder extends Seeder
{
    private const DEBUT = '2026-04-01 08:00:00';

    public function run(): void
    {
        DB::transaction(function () {
            $nbPaiements = $this->paiementsTtc();
            $nbEcheances = $this->echeances();
            $nbVides = $this->recusVides();
            $this->chronologie();
            $nbLiens = $this->liensFournisseurs();
            $this->motifsMouvements();
            $nbStock = $this->historiqueStockPositif();
            $this->inventairesValides();

            $this->command?->info("Paiements recalculés sur {$nbPaiements} facture(s), {$nbEcheances} échéance(s) corrigée(s), "
                . "{$nbVides} reçu(s) vide(s) annulé(s), {$nbLiens} lien(s) produit-fournisseur ajouté(s), "
                . "{$nbStock} historique(s) de stock rendu(s) positif(s), chronologie remise en ordre.");
        });
    }

    /**
     * Répartit le total TTC de chaque facture sur ses pièces payées, comme le fait
     * PaiementController : montant = quantité x (total_ligne x coefficient) / quantité de la ligne,
     * la dernière pièce d'une ligne absorbe l'arrondi, et le solde final tombe pile sur le total.
     */
    private function paiementsTtc(): int
    {
        $factureIds = DB::table('paiements')->distinct()->pluck('facture_id');
        $n = 0;

        foreach ($factureIds as $factureId) {
            $facture = DB::table('factures')->find($factureId);
            $lignes = DB::table('lignes_facture')->where('facture_id', $factureId)->orderBy('id')->get()->keyBy('id');
            $sommeLignes = $lignes->sum('total_ligne');
            $coef = $sommeLignes > 0 ? $facture->total / $sommeLignes : 0;

            $nouveaux = []; // ligne_paiement id => montant
            foreach ($lignes as $ligne) {
                $ttcLigne = (int) round($ligne->total_ligne * $coef);
                $lps = DB::table('ligne_paiements')->where('ligne_facture_id', $ligne->id)->orderBy('id')->get();
                $qteCumulee = 0;
                $dejaReparti = 0;
                foreach ($lps as $lp) {
                    $qteCumulee += $lp->quantite;
                    $montant = $qteCumulee >= $ligne->quantite
                        ? $ttcLigne - $dejaReparti
                        : (int) round($lp->quantite * $ttcLigne / max(1, $ligne->quantite));
                    $dejaReparti += $montant;
                    $nouveaux[$lp->id] = $montant;
                }
            }

            if (!$nouveaux) {
                continue;
            }

            $toutPaye = $lignes->every(fn ($l) => $l->quantite_payee >= $l->quantite);
            if ($toutPaye) {
                $dernier = max(array_keys($nouveaux));
                $nouveaux[$dernier] += (int) round($facture->total - array_sum($nouveaux));
            }

            foreach ($nouveaux as $lpId => $montant) {
                DB::table('ligne_paiements')->where('id', $lpId)->update(['montant' => $montant]);
            }

            $paiementIds = DB::table('paiements')->where('facture_id', $factureId)->pluck('id');
            foreach ($paiementIds as $pid) {
                DB::table('paiements')->where('id', $pid)->update([
                    'montant' => DB::table('ligne_paiements')->where('paiement_id', $pid)->sum('montant'),
                ]);
            }

            $paye = DB::table('paiements')->where('facture_id', $factureId)->sum('montant');
            if ((float) $paye !== (float) $facture->montant_paye) {
                $n++;
            }
            DB::table('factures')->where('id', $factureId)->update([
                'montant_paye' => $paye,
                'status' => $toutPaye ? 'payee' : 'partiellement_payee',
            ]);
        }

        return $n;
    }

    private function echeances(): int
    {
        return DB::update("update factures set date_echeance = (created_at::date + 30) where date_echeance < created_at::date");
    }

    private function recusVides(): int
    {
        return DB::update("update factures set status = 'annule', modifiable = false
            where type_document = 'recu' and status <> 'annule' and total = 0 and montant_paye = 0
              and not exists (select 1 from lignes_facture l where l.facture_id = factures.id)");
    }

    private function chronologie(): void
    {
        $debut = self::DEBUT;

        // Comptes, fournisseurs et produits existent avant la première vente
        DB::update("update users set created_at = timestamp '$debut' + (id * interval '10 minutes') where created_at > timestamp '$debut' + (id * interval '10 minutes')");
        DB::update("update fournisseurs set created_at = timestamp '2026-04-10 09:00:00' + (id * interval '1 hour') where created_at > timestamp '2026-04-10 09:00:00' + (id * interval '1 hour')");
        DB::update("update produits set created_at = timestamp '2026-04-20 08:00:00' + (id * interval '2 minutes') where created_at > timestamp '2026-04-20 08:00:00' + (id * interval '2 minutes')");

        // Stock initial : juste après la création de la fiche produit
        DB::update("update mouvements_stock m set created_at = p.created_at + interval '5 minutes', updated_at = p.created_at + interval '5 minutes'
            from produits p where p.id = m.produit_id and m.raison like 'Stock initial%'");

        // Un client existe avant sa première facture
        DB::update("update clients c set created_at = s.premiere - interval '2 hours'
            from (select client_id, min(created_at) premiere from factures group by client_id) s
            where s.client_id = c.id and c.created_at > s.premiere - interval '2 hours'");

        // Lignes de facture et paiements datés comme leur facture / leur date de paiement
        DB::update("update lignes_facture l set created_at = f.created_at, updated_at = f.created_at
            from factures f where f.id = l.facture_id and l.created_at <> f.created_at");
        DB::update("update paiements set created_at = date_paiement + time '12:00', updated_at = date_paiement + time '12:00'
            where created_at::date <> date_paiement");
        DB::update("update ligne_paiements lp set created_at = p.created_at, updated_at = p.created_at
            from paiements p where p.id = lp.paiement_id and lp.created_at is distinct from p.created_at");

        // Sorties de stock datées de la vente
        DB::update("update mouvements_stock m set created_at = f.created_at, updated_at = f.created_at
            from factures f where m.raison = 'Vente facture ' || f.numero_facture and m.created_at <> f.created_at");

        // Commandes, réceptions et entrées de stock datées de leurs dates métier
        DB::update("update commandes_achat set created_at = date_commande + time '09:00'
            where created_at <> date_commande + time '09:00'");
        DB::update("update lignes_commande_achat l set created_at = c.created_at
            from commandes_achat c where c.id = l.commande_achat_id and l.created_at is distinct from c.created_at");
        DB::update("update receptions_achat set created_at = date_reception + time '10:00'
            where created_at <> date_reception + time '10:00'");
        DB::update("update lignes_reception_achat l set created_at = r.created_at
            from receptions_achat r where r.id = l.reception_achat_id and l.created_at is distinct from r.created_at");
        DB::update("update mouvements_stock m set created_at = r.created_at, updated_at = r.created_at
            from receptions_achat r join commandes_achat c on c.id = r.commande_achat_id
            where m.raison = 'Réception achat - Commande ' || c.numero and m.created_at <> r.created_at");

        // Ajustements de l'inventaire validé datés de sa validation
        DB::update("update mouvements_stock m set created_at = i.date_validation, updated_at = i.date_validation
            from inventaires i where m.raison = 'Inventaire ' || i.reference and i.date_validation is not null
              and m.created_at <> i.date_validation");
        DB::update("update inventaire_lignes il set created_at = i.created_at, updated_at = i.created_at
            from inventaires i where i.id = il.inventaire_id and il.created_at is distinct from i.created_at");
    }

    private function liensFournisseurs(): int
    {
        return DB::affectingStatement("insert into produit_fournisseur (produit_id, fournisseur_id, prix_achat, delai_livraison, quantite_minimum, prefere, created_at, updated_at)
            select l.produit_id, c.fournisseur_id, round(avg(l.prix_unitaire))::bigint, 7, 1, false, min(c.created_at), min(c.created_at)
            from lignes_commande_achat l join commandes_achat c on c.id = l.commande_achat_id
            where not exists (select 1 from produit_fournisseur pf where pf.produit_id = l.produit_id and pf.fournisseur_id = c.fournisseur_id)
            group by l.produit_id, c.fournisseur_id");
    }

    /**
     * Rejoué dans l'ordre chronologique, le stock d'un produit ne doit jamais passer sous zéro
     * (on ne vend pas un livre qu'on n'a pas encore reçu). Si c'est le cas, on augmente le stock
     * initial du déficit et on compense par le dernier ajustement : le stock final est inchangé.
     * Les écritures passent par DB::table (pas le modèle) pour ne pas re-modifier produits.stock.
     */
    private function historiqueStockPositif(): int
    {
        $deficits = DB::select("select produit_id, -min(cumul) deficit from (
                select produit_id, sum(case when type = 'entree' then quantite else -quantite end)
                    over (partition by produit_id order by created_at, id) cumul
                from mouvements_stock) x
            group by produit_id having min(cumul) < 0");

        foreach ($deficits as $d) {
            $produit = DB::table('produits')->find($d->produit_id);
            $initial = DB::table('mouvements_stock')->where('produit_id', $produit->id)
                ->where('raison', 'Stock initial')->orderBy('created_at')->first();

            if ($initial) {
                DB::table('mouvements_stock')->where('id', $initial->id)->increment('quantite', $d->deficit);
            } else {
                $date = \Carbon\Carbon::parse($produit->created_at)->addMinutes(5);
                DB::table('mouvements_stock')->insert([
                    'produit_id' => $produit->id, 'type' => 'entree', 'quantite' => $d->deficit,
                    'raison' => 'Stock initial', 'prix_unitaire' => $produit->prix_achat,
                    'created_at' => $date, 'updated_at' => $date,
                ]);
            }

            $ajustement = DB::table('mouvements_stock')->where('produit_id', $produit->id)
                ->where('type', 'sortie')->where('raison', 'like', 'Ajustement:%')
                ->orderByDesc('created_at')->orderByDesc('id')->first();

            if ($ajustement) {
                DB::table('mouvements_stock')->where('id', $ajustement->id)->increment('quantite', $d->deficit);
            } else {
                DB::table('mouvements_stock')->insert([
                    'produit_id' => $produit->id, 'type' => 'sortie', 'quantite' => $d->deficit,
                    'raison' => 'Ajustement: correction après recomptage',
                    'created_at' => '2026-08-21 00:01:00', 'updated_at' => '2026-08-21 00:01:00',
                ]);
            }
        }

        return count($deficits);
    }

    /**
     * Inventaire validé : stock théorique = stock réel à l'ouverture de la session (d'après les
     * mouvements), stock compté = théorique + ajustement enregistré à la validation.
     */
    private function inventairesValides(): void
    {
        DB::update("update inventaire_lignes il set
                stock_theorique = s.a_date,
                stock_reel = case when il.stock_reel is null then null else s.a_date + s.ajust end
            from (
                select il2.id,
                    (select coalesce(sum(case when m.type = 'entree' then m.quantite else -m.quantite end), 0)
                       from mouvements_stock m where m.produit_id = il2.produit_id and m.created_at <= i.created_at) a_date,
                    (select coalesce(sum(case when m.type = 'entree' then m.quantite else -m.quantite end), 0)
                       from mouvements_stock m where m.produit_id = il2.produit_id and m.raison = 'Inventaire ' || i.reference) ajust
                from inventaire_lignes il2 join inventaires i on i.id = il2.inventaire_id
                where i.statut = 'validee'
            ) s
            where s.id = il.id");

        // Fiche produit : dernier comptage = celui du dernier inventaire validé
        DB::update("update produits p set dernier_stock_reel = il.stock_reel
            from inventaire_lignes il join inventaires i on i.id = il.inventaire_id
            where il.produit_id = p.id and i.statut = 'validee' and il.stock_reel is not null
              and i.date_validation = (select max(i2.date_validation) from inventaires i2 join inventaire_lignes l2 on l2.inventaire_id = i2.id
                                        where i2.statut = 'validee' and l2.produit_id = p.id and l2.stock_reel is not null)");
    }

    private function motifsMouvements(): void
    {
        DB::update("update mouvements_stock set raison = 'Ajustement: articles abîmés (retrait de la vente)' where raison = 'Ajustement inventaire (démo - stock bas)'");
        DB::update("update mouvements_stock set raison = 'Ajustement: correction après recomptage' where raison = 'Ajustement inventaire (démo - test pagination)'");
        DB::update("update mouvements_stock set raison = 'Stock initial' where raison = 'Stock initial (import démo)'");
    }
}
