<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommandeAchat;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\LigneCommandeAchat;
use App\Services\ActivityLogger;

class CommandeAchatController extends Controller
{
    public function index()
    {
        $commandes = CommandeAchat::latest()->paginate(15);
        return view('commandes_achat.index', compact('commandes'));
    }

    public function create()
    {
        $fournisseurs = Fournisseur::where('actif', true)->orderBy('nom')->get();
        return view('commandes_achat.create', compact('fournisseurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date_commande' => 'required|date',
            'date_reception_prevue' => 'nullable|date|after:date_commande',
            'notes' => 'nullable|string|max:1000',
        ]);

        $numero = 'CMD-' . date('YmdHis');

        $commande = CommandeAchat::create([
            'numero' => $numero,
            'fournisseur_id' => $request->fournisseur_id,
            'date_commande' => $request->date_commande,
            'date_reception_prevue' => $request->date_reception_prevue,
            'statut' => 'brouillon',
            'notes' => $request->notes,
        ]);

        ActivityLogger::created($commande, "Création de la commande d'achat {$numero}");

        return redirect()->route('commandes_achat.show', $commande->id)
            ->with('success', 'Commande d\'achat créée.');
    }

    public function show(CommandeAchat $commande)
    {
        $fournisseur = $commande->fournisseur;
        $lignes = $commande->lignes()->with('produit')->get();
        $receptions = $commande->receptions()->latest()->get();
        return view('commandes_achat.show', compact('commande', 'fournisseur', 'lignes', 'receptions'));
    }

    public function edit(CommandeAchat $commande)
    {
        if (!$commande->isEditable()) {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Cette commande ne peut pas être modifiée.');
        }

        $fournisseur = $commande->fournisseur;
        $produits = $fournisseur->produits()->get();
        $lignes = $commande->lignes()->with('produit')->get();

        return view('commandes_achat.edit', compact('commande', 'fournisseur', 'produits', 'lignes'));
    }

    public function update(Request $request, CommandeAchat $commande)
    {
        if (!$commande->isEditable()) {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Cette commande ne peut pas être modifiée.');
        }

        $request->validate([
            'date_reception_prevue' => 'nullable|date|after:date_commande',
            'notes' => 'nullable|string|max:1000',
        ]);

        $commande->update([
            'date_reception_prevue' => $request->date_reception_prevue,
            'notes' => $request->notes,
        ]);

        ActivityLogger::updated($commande, "Modification de la commande {$commande->numero}");

        return redirect()->route('commandes_achat.show', $commande->id)
            ->with('success', 'Commande mise à jour.');
    }

    public function addLigne(Request $request, CommandeAchat $commande)
    {
        if (!$commande->isEditable()) {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Cette commande ne peut pas être modifiée.');
        }

        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1',
            'prix_unitaire' => 'required|integer|min:0',
        ]);

        $produit = Produit::find($request->produit_id);

        LigneCommandeAchat::create([
            'commande_achat_id' => $commande->id,
            'produit_id' => $request->produit_id,
            'quantite' => $request->quantite,
            'prix_unitaire' => $request->prix_unitaire,
            'montant' => $request->quantite * $request->prix_unitaire,
        ]);

        ActivityLogger::updated($commande, "Ajout du produit {$produit->nom} à la commande {$commande->numero}");

        return redirect()->route('commandes_achat.edit', $commande->id)
            ->with('success', 'Ligne ajoutée.');
    }

    public function removeLigne(CommandeAchat $commande, LigneCommandeAchat $ligne)
    {
        if (!$commande->isEditable()) {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Cette commande ne peut pas être modifiée.');
        }

        $produitNom = $ligne->produit->nom;
        $ligne->delete();

        ActivityLogger::updated($commande, "Suppression du produit {$produitNom} de la commande {$commande->numero}");

        return redirect()->route('commandes_achat.edit', $commande->id)
            ->with('success', 'Ligne supprimée.');
    }

    public function confirm(CommandeAchat $commande)
    {
        if (!$commande->canConfirm()) {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Cette commande ne peut pas être confirmée.');
        }

        $commande->update(['statut' => 'confirmee']);

        ActivityLogger::updated($commande, "Confirmation de la commande {$commande->numero}");

        return redirect()->route('commandes_achat.show', $commande->id)
            ->with('success', 'Commande confirmée.');
    }

    public function cancel(CommandeAchat $commande)
    {
        if (!$commande->canCancel()) {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Cette commande ne peut pas être annulée.');
        }

        $commande->update(['statut' => 'annulee']);

        ActivityLogger::updated($commande, "Annulation de la commande {$commande->numero}");

        return redirect()->route('commandes_achat.show', $commande->id)
            ->with('success', 'Commande annulée.');
    }

    public function destroy(CommandeAchat $commande)
    {
        if ($commande->statut !== 'brouillon') {
            return redirect()->route('commandes_achat.show', $commande->id)
                ->with('error', 'Seule une commande en brouillon peut être supprimée.');
        }

        $numero = $commande->numero;
        $commande->delete();

        ActivityLogger::deleted($commande, "Suppression de la commande d'achat {$numero}");

        return redirect()->route('commandes_achat.index')
            ->with('success', 'Commande supprimée.');
    }
}
