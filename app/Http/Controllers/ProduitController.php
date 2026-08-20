<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Produit;
use App\Models\MouvementStock;
use App\Models\LigneCommandeAchat;
use App\Models\LigneFacture;
use App\Services\ActivityLogger;

class ProduitController extends Controller
{
    public function index()
    {
        $produits = Produit::orderBy('nom')->paginate(10); 
        return view('produits.index', compact('produits'));
    }

    public function show(Produit $produit)
    {
        return view('produits.show', compact('produit'));
    }

    public function create()
    {
        return view('produits.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:produits,nom',
            'sku' => 'nullable|string|max:100|unique:produits,sku',
            'categorie' => 'nullable|string|max:100',
            'famille' => 'nullable|string|max:100',
            'unite' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'prix_achat' => 'required|integer|min:1',
            'prix_vente' => 'required|integer|min:1',
            'stock' => 'required|integer|min:0',
            'seuil_alerte' => 'required|integer|min:0',
        ], [
            'nom.unique' => 'Ce produit existe déjà.',
            'sku.unique' => 'Ce SKU est déjà utilisé.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('product_photos', 'public');
        }

        $produit = Produit::create([
            'nom' => $request->nom,
            'sku' => $request->sku,
            'categorie' => $request->categorie,
            'famille' => $request->famille,
            'unite' => $request->unite,
            'description' => $request->description,
            'photo' => $photoPath,
            'prix_achat' => (int) $request->prix_achat,
            'prix_vente' => (int) $request->prix_vente,
            'stock' => 0,
            'seuil_alerte' => (int) $request->seuil_alerte,
        ]);

        if ($request->stock > 0) {
            MouvementStock::create([
                'produit_id' => $produit->id,
                'type' => 'entree',
                'quantite' => (int) $request->stock,
                'raison' => 'Stock initial',
                'prix_unitaire' => (int) $request->prix_achat,
            ]);
        }

        // Enregistrer l'activité
        ActivityLogger::created(
            $produit,
            "Création du produit {$produit->nom} - Stock initial: {$request->stock}"
        );

        return redirect()->route('produits.index')
            ->with('success', 'Produit créé avec succès.');
    }


    public function edit(Produit $produit)
    {
        return view('produits.edit', compact('produit'));
    }

    public function update(Request $request, Produit $produit)
    {
        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('produits', 'nom')->ignore($produit->id),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('produits', 'sku')->ignore($produit->id),
            ],
            'categorie' => 'nullable|string|max:100',
            'famille' => 'nullable|string|max:100',
            'unite' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'prix_achat' => 'required|integer|min:0',
            'prix_vente' => 'required|integer|min:0',
            'seuil_alerte' => 'required|integer|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $oldData = [
            'nom' => $produit->nom,
            'sku' => $produit->sku,
            'categorie' => $produit->categorie,
            'famille' => $produit->famille,
            'unite' => $produit->unite,
            'description' => $produit->description,
            'prix_achat' => $produit->prix_achat,
            'prix_vente' => $produit->prix_vente,
            'stock' => $produit->stock_actuel,
            'seuil_alerte' => $produit->seuil_alerte,
        ];

        $photoPath = $produit->photo;
        if ($request->hasFile('photo')) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('product_photos', 'public');
        }

        // Convert to integers for XOF currency
        $produit->update([
            'nom' => $request->nom,
            'sku' => $request->sku,
            'categorie' => $request->categorie,
            'famille' => $request->famille,
            'unite' => $request->unite,
            'description' => $request->description,
            'photo' => $photoPath,
            'prix_achat' => (int) $request->prix_achat,
            'prix_vente' => (int) $request->prix_vente,
            'seuil_alerte' => (int) $request->seuil_alerte,
        ]);

        $stockChanged = false;
        if ($request->filled('stock')) {
            $diff = (int) $request->stock - $produit->stock_actuel;

            if ($diff !== 0) {
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'type' => $diff > 0 ? 'entree' : 'sortie',
                    'quantite' => abs($diff),
                    'raison' => 'Ajustement stock',
                ]);
                $stockChanged = true;
            }
        }

        // Enregistrer l'activité
        ActivityLogger::updated(
            $produit,
            "Modification du produit {$produit->nom}" . ($stockChanged ? " - Stock ajusté" : ""),
            null,
            $oldData
        );

        return redirect()->route('produits.index')
            ->with('success', 'Produit mis à jour.');
    }

    public function destroy(Produit $produit)
    {
        if ($produit->mouvementsStock()->exists()) {
            return redirect()->route('produits.index')
                ->with('error', 'Ce produit a un historique de mouvements de stock et ne peut pas être supprimé.');
        }

        if (LigneCommandeAchat::where('produit_id', $produit->id)->exists()) {
            return redirect()->route('produits.index')
                ->with('error', 'Ce produit est référencé dans une commande d\'achat et ne peut pas être supprimé.');
        }

        if (LigneFacture::where('produit_id', $produit->id)->exists()) {
            return redirect()->route('produits.index')
                ->with('error', 'Ce produit est référencé dans une facture et ne peut pas être supprimé.');
        }

        $produitName = $produit->nom;

        $produit->delete();

        // Enregistrer l'activité
        ActivityLogger::deleted(
            $produit,
            "Suppression du produit {$produitName}"
        );

        return redirect()->route('produits.index')->with('success','Produit supprimé avec succès.');
    }

    public function reapprovisionnement(Request $request, Produit $produit)
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'quantite' => 'required|integer|min:1',
                'raison' => 'nullable|string|max:255',
                'prix_achat_unitaire' => 'required|integer|min:0',
            ]);

            $nouveauCump = DB::transaction(function () use ($request, $produit) {
                $produitLocked = Produit::where('id', $produit->id)->lockForUpdate()->first();

                $stockAvant = $produitLocked->stock;
                $ancienCump = $produitLocked->prix_achat ?? 0;
                $quantite = (int) $request->quantite;
                $prixUnitaire = (int) $request->prix_achat_unitaire;

                // Création du mouvement d'entrée (incrémente automatiquement produit.stock)
                MouvementStock::create([
                    'produit_id' => $produitLocked->id,
                    'type' => 'entree',
                    'quantite' => $quantite,
                    'raison' => $request->raison ?? 'Réapprovisionnement',
                    'prix_unitaire' => $prixUnitaire,
                ]);

                // Coût Unitaire Moyen Pondéré (CUMP) : intègre le nouveau lot au stock existant
                $diviseur = $stockAvant + $quantite;
                $nouveauCump = $diviseur > 0
                    ? (int) round((($stockAvant * $ancienCump) + ($quantite * $prixUnitaire)) / $diviseur)
                    : $prixUnitaire;

                $produitLocked->update(['prix_achat' => $nouveauCump]);

                return $nouveauCump;
            });

            // Enregistrer l'activité
            ActivityLogger::stockUpdate(
                $produit,
                "Réapprovisionnement du produit {$produit->nom} - Quantité: {$request->quantite} - Coût unitaire: {$request->prix_achat_unitaire} - Nouveau CUMP: {$nouveauCump}",
                $request->quantite
            );

            return redirect()->route('produits.index')
                             ->with('success', 'Stock réapprovisionné avec succès.');
        }

        // GET → afficher le formulaire
        return view('produits.reapprovisionnement', compact('produit'));
    }

    /**
     * Ajustement de stock (pour pertes, dommages, inventaire, etc.)
     */
    public function ajustement(Request $request, Produit $produit)
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'nouvelle_quantite' => 'required|integer|min:0',
                'motif' => 'required|string|max:255',
            ]);

            $stockActuel = $produit->stock_actuel;
            $nouvelleQuantite = $request->nouvelle_quantite;
            $diff = $nouvelleQuantite - $stockActuel;

            // Si pas de différence, on ne fait rien
            if ($diff !== 0) {
                // Créer le mouvement d'ajustement
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'type' => $diff >= 0 ? 'entree' : 'sortie',
                    'quantite' => abs($diff),
                    'raison' => 'Ajustement: ' . $request->motif,
                ]);

                // Enregistrer l'activité
                ActivityLogger::stockUpdate(
                    $produit,
                    "Ajustement stock du produit {$produit->nom} - {$request->motif} - Différence: " . ($diff > 0 ? "+" : "") . $diff,
                    abs($diff)
                );
            }

            return redirect()->route('produits.index')
                             ->with('success', 'Stock ajusté avec succès.');
        }

        // GET → afficher le formulaire
        return view('produits.ajustement', compact('produit'));
    }

    public function mouvements(Produit $produit)
    {
        $mouvements = $produit->mouvementsStock()->orderBy('created_at','desc')->get();
        return view('produits.mouvements', compact('produit','mouvements'));
    }
}
