<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Services\ActivityLogger;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::orderBy('nom')->paginate(15);
        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function create()
    {
        return view('fournisseurs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:fournisseurs,nom',
            'email' => 'nullable|email|unique:fournisseurs,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:20',
            'pays' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $fournisseur = Fournisseur::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'code_postal' => $request->code_postal,
            'pays' => $request->pays,
            'notes' => $request->notes,
        ]);

        ActivityLogger::created($fournisseur, "Création du fournisseur {$fournisseur->nom}");

        return redirect()->route('fournisseurs.show', $fournisseur->id)
            ->with('success', 'Fournisseur créé avec succès.');
    }

    public function show(Fournisseur $fournisseur)
    {
        $produits = $fournisseur->produits()->paginate(10);
        $commandes = $fournisseur->commandes()->latest()->paginate(10);
        return view('fournisseurs.show', compact('fournisseur', 'produits', 'commandes'));
    }

    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:fournisseurs,nom,' . $fournisseur->id,
            'email' => 'nullable|email|unique:fournisseurs,email,' . $fournisseur->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:20',
            'pays' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldData = [
            'nom' => $fournisseur->nom,
            'email' => $fournisseur->email,
            'telephone' => $fournisseur->telephone,
        ];

        $fournisseur->update([
            'nom' => $request->nom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'code_postal' => $request->code_postal,
            'pays' => $request->pays,
            'notes' => $request->notes,
        ]);

        ActivityLogger::updated($fournisseur, "Modification du fournisseur {$fournisseur->nom}", null, $oldData);

        return redirect()->route('fournisseurs.show', $fournisseur->id)
            ->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        if ($fournisseur->commandes()->exists()) {
            return redirect()->route('fournisseurs.index')
                ->with('error', 'Ce fournisseur a des commandes d\'achat associées et ne peut pas être supprimé.');
        }

        $nom = $fournisseur->nom;
        $fournisseur->delete();

        ActivityLogger::deleted($fournisseur, "Suppression du fournisseur {$nom}");

        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur supprimé.');
    }

    public function linkProduct(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'prix_achat' => 'required|integer|min:0',
            'delai_livraison' => 'required|integer|min:1',
            'reference_fournisseur' => 'nullable|string|max:100',
            'quantite_minimum' => 'required|integer|min:1',
        ]);

        $fournisseur->produits()->attach($request->produit_id, [
            'prix_achat' => $request->prix_achat,
            'delai_livraison' => $request->delai_livraison,
            'reference_fournisseur' => $request->reference_fournisseur,
            'quantite_minimum' => $request->quantite_minimum,
        ]);

        return redirect()->route('fournisseurs.show', $fournisseur->id)
            ->with('success', 'Produit lié au fournisseur.');
    }

    public function unlinkProduct(Fournisseur $fournisseur, Produit $produit)
    {
        $fournisseur->produits()->detach($produit->id);

        return redirect()->route('fournisseurs.show', $fournisseur->id)
            ->with('success', 'Produit retiré du fournisseur.');
    }
}
