<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReceptionAchat;
use App\Models\CommandeAchat;
use App\Models\LigneCommandeAchat;
use App\Models\MouvementStock;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

class ReceptionAchatController extends Controller
{
    public function create(CommandeAchat $commande)
    {
        if ($commande->statut === 'annulee') {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Impossible de créer une réception pour une commande annulée.');
        }

        $lignes = $commande->lignes()
            ->where('quantite_reçue', '<', DB::raw('quantite'))
            ->with('produit')
            ->get();

        if ($lignes->isEmpty()) {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('info', 'Tous les produits ont déjà été reçus.');
        }

        return view('receptions_achat.create', compact('commande', 'lignes'));
    }

    public function store(Request $request, CommandeAchat $commande)
    {
        $request->validate([
            'date_reception' => 'required|date',
            'lignes' => 'required|array|min:1',
            'lignes.*.ligne_id' => 'required|exists:lignes_commande_achat,id',
            'lignes.*.quantite_reçue' => 'required|integer|min:1',
        ]);

        $numero = 'REC-' . date('YmdHis');

        $reception = ReceptionAchat::create([
            'numero' => $numero,
            'commande_achat_id' => $commande->id,
            'date_reception' => $request->date_reception,
            'notes' => $request->notes,
        ]);

        foreach ($request->lignes as $ligneData) {
            $ligne = LigneCommandeAchat::find($ligneData['ligne_id']);
            $quantiteReçue = (int) $ligneData['quantite_reçue'];

            if ($ligne->quantite_reçue + $quantiteReçue > $ligne->quantite) {
                return redirect()->route('commandes_achat.show', $commande->id)
                    ->with('error', "Quantité reçue dépasse la quantité commandée pour {$ligne->produit->nom}.");
            }

            // Créer la ligne de réception
            $ligne->receptionsLignes()->create([
                'reception_achat_id' => $reception->id,
                'quantite_reçue' => $quantiteReçue,
            ]);

            // Mettre à jour la quantité reçue dans la ligne de commande
            $ligne->update([
                'quantite_reçue' => $ligne->quantite_reçue + $quantiteReçue,
            ]);

            // Créer le mouvement de stock (entrée)
            MouvementStock::create([
                'produit_id' => $ligne->produit_id,
                'type' => 'entree',
                'quantite' => $quantiteReçue,
                'raison' => "Réception achat - Commande {$commande->numero}",
            ]);

            // Log d'activité
            ActivityLogger::stockUpdate(
                $ligne->produit,
                "Réception du produit {$ligne->produit->nom} - Commande {$commande->numero}",
                $quantiteReçue
            );
        }

        // Mettre à jour le statut de la commande
        $commande->refresh();
        if ($commande->quantite_reçue >= $commande->quantite_total) {
            $commande->update(['statut' => 'reçue', 'date_reception' => $request->date_reception]);
        } else {
            $commande->update(['statut' => 'reçue_partiellement']);
        }

        ActivityLogger::created(
            $reception,
            "Création de la réception d'achat {$numero} - Commande {$commande->numero}"
        );

        return redirect()->route('commandes_achat.show', $commande->id)
            ->with('success', 'Réception d\'achat créée et stock mis à jour.');
    }

    public function show(ReceptionAchat $reception)
    {
        $commande = $reception->commande;
        $lignes = $reception->lignes()->with(['ligneCommande.produit'])->get();
        return view('receptions_achat.show', compact('reception', 'commande', 'lignes'));
    }
}
