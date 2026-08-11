<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\LignePaiement;
use App\Models\Paiement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    public function store(Request $request, Facture $facture)
    {
        $request->validate([
            'lignes' => 'required|array|min:1',
            'lignes.*.ligne_id' => 'required|integer|exists:lignes_facture,id',
            'lignes.*.quantite' => 'required|integer|min:0',
            'mode' => 'required|string|in:especes,carte,virement,mobile_money,autre',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'date_paiement' => 'nullable|date',
        ]);

        $selections = collect($request->lignes)->filter(fn($l) => (int) $l['quantite'] > 0);

        if ($selections->isEmpty()) {
            return back()->withErrors(['lignes' => 'Sélectionnez au moins une pièce à régler.']);
        }

        try {
            $paiement = DB::transaction(function () use ($request, $facture, $selections) {
                $lignes = $facture->lignes()
                    ->whereIn('id', $selections->pluck('ligne_id'))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $paiement = Paiement::create([
                    'facture_id' => $facture->id,
                    'montant' => 0,
                    'mode' => $request->mode,
                    'reference' => $request->reference,
                    'note' => $request->note,
                    'date_paiement' => $request->date_paiement ?: now()->format('Y-m-d'),
                ]);

                $montantTotal = 0;

                foreach ($selections as $selection) {
                    $ligne = $lignes[$selection['ligne_id']] ?? null;
                    $quantiteAPayer = (int) $selection['quantite'];

                    if (!$ligne) {
                        throw new \RuntimeException('Cette ligne n\'appartient pas à cette facture.');
                    }

                    if ($quantiteAPayer > $ligne->quantite_restante) {
                        throw new \RuntimeException(
                            "Quantité invalide pour {$ligne->produit->nom} (reste à payer : {$ligne->quantite_restante})."
                        );
                    }

                    $prixUnitaireNet = $ligne->quantite > 0 ? $ligne->total_ligne / $ligne->quantite : 0;
                    $nouvelleQuantitePayee = $ligne->quantite_payee + $quantiteAPayer;

                    if ($nouvelleQuantitePayee >= $ligne->quantite) {
                        // Dernier règlement de la ligne : absorbe l'arrondi pour tomber pile sur total_ligne
                        $montantDejaPaye = LignePaiement::where('ligne_facture_id', $ligne->id)->sum('montant');
                        $montantLigne = $ligne->total_ligne - $montantDejaPaye;
                    } else {
                        $montantLigne = (int) round($prixUnitaireNet * $quantiteAPayer);
                    }

                    LignePaiement::create([
                        'paiement_id' => $paiement->id,
                        'ligne_facture_id' => $ligne->id,
                        'quantite' => $quantiteAPayer,
                        'montant' => $montantLigne,
                    ]);

                    $ligne->update(['quantite_payee' => $nouvelleQuantitePayee]);

                    $montantTotal += $montantLigne;
                }

                $paiement->update(['montant' => $montantTotal]);

                $facture->montant_paye = $facture->montant_paye + $montantTotal;
                $toutesLignesPayees = $facture->lignes()->whereColumn('quantite_payee', '<', 'quantite')->doesntExist();
                $facture->date_paiement = $toutesLignesPayees ? now()->format('Y-m-d') : $facture->date_paiement;
                $facture->status = $toutesLignesPayees ? 'payee' : 'partiellement_payee';
                $facture->save();

                ActivityLogger::created(
                    $facture,
                    'Paiement de ' . number_format($montantTotal, 0, ',', ' ') . ' CFA pour ' . $facture->numero_facture,
                    $montantTotal
                );

                return $paiement;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['lignes' => $e->getMessage()]);
        }

        return back()->with('success', 'Paiement enregistré avec succès.');
    }
}
