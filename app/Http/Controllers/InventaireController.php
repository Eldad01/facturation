<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\Produit;
use App\Models\MouvementStock;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

class InventaireController extends Controller
{
    public function index()
    {
        $inventaires = Inventaire::with('userCreation', 'userValidation')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('inventaires.index', compact('inventaires'));
    }

    public function create()
    {
        $produitsCount = Produit::count();
        return view('inventaires.create', compact('produitsCount'));
    }

    public function store(Request $request)
    {
        $inventaire = DB::transaction(function () {
            $inventaire = Inventaire::create([
                'reference' => 'INV-' . date('YmdHis'),
                'statut' => 'brouillon',
                'user_id_creation' => auth()->id(),
            ]);

            $produits = Produit::orderBy('nom')->get();
            foreach ($produits as $produit) {
                InventaireLigne::create([
                    'inventaire_id' => $inventaire->id,
                    'produit_id' => $produit->id,
                    'stock_theorique' => $produit->stock,
                    'stock_reel' => null,
                ]);
            }

            ActivityLogger::created(
                $inventaire,
                "Ouverture de la session d'inventaire {$inventaire->reference} ({$produits->count()} produits)"
            );

            return $inventaire;
        });

        return redirect()->route('inventaires.show', $inventaire->id)
            ->with('success', 'Session d\'inventaire ouverte. Vous pouvez saisir les comptages.');
    }

    public function show(Inventaire $inventaire)
    {
        $inventaire->load('lignes.produit', 'userCreation', 'userValidation');
        return view('inventaires.show', compact('inventaire'));
    }

    public function update(Request $request, Inventaire $inventaire)
    {
        if ($inventaire->isValidee()) {
            return redirect()->route('inventaires.show', $inventaire->id)
                ->with('error', 'Cet inventaire est déjà validé, les comptages ne peuvent plus être modifiés.');
        }

        $request->validate([
            'lignes' => 'required|array',
            'lignes.*.ligne_id' => 'required|integer|exists:inventaire_lignes,id',
            'lignes.*.stock_reel' => 'nullable|integer|min:0',
        ]);

        foreach ($request->lignes as $ligneData) {
            if (!array_key_exists('stock_reel', $ligneData) || $ligneData['stock_reel'] === null || $ligneData['stock_reel'] === '') {
                continue;
            }

            $ligne = $inventaire->lignes()->where('id', $ligneData['ligne_id'])->first();
            if ($ligne) {
                $ligne->update(['stock_reel' => (int) $ligneData['stock_reel']]);
            }
        }

        ActivityLogger::updated(
            $inventaire,
            "Saisie des comptages - session {$inventaire->reference}"
        );

        return redirect()->route('inventaires.show', $inventaire->id)
            ->with('success', 'Comptages enregistrés.');
    }

    public function valider(Inventaire $inventaire)
    {
        if ($inventaire->isValidee()) {
            return redirect()->route('inventaires.show', $inventaire->id)
                ->with('error', 'Cet inventaire est déjà validé.');
        }

        DB::transaction(function () use ($inventaire) {
            $lignes = $inventaire->lignes()->whereNotNull('stock_reel')->get();

            foreach ($lignes as $ligne) {
                $produit = Produit::where('id', $ligne->produit_id)->lockForUpdate()->first();
                if (!$produit) {
                    continue;
                }

                $diff = $ligne->stock_reel - $produit->stock;

                if ($diff !== 0) {
                    MouvementStock::create([
                        'produit_id' => $produit->id,
                        'type' => $diff > 0 ? 'entree' : 'sortie',
                        'quantite' => abs($diff),
                        'raison' => "Inventaire {$inventaire->reference}",
                    ]);

                    ActivityLogger::stockUpdate(
                        $produit,
                        "Ajustement inventaire {$inventaire->reference} - écart: " . ($diff > 0 ? '+' : '') . $diff,
                        abs($diff)
                    );
                }

                $produit->update([
                    'dernier_stock_reel' => $ligne->stock_reel,
                    'date_dernier_inventaire' => now(),
                ]);
            }

            $inventaire->update([
                'statut' => 'validee',
                'user_id_validation' => auth()->id(),
                'date_validation' => now(),
            ]);

            ActivityLogger::validated(
                $inventaire,
                "Validation de l'inventaire {$inventaire->reference}"
            );
        });

        return redirect()->route('inventaires.show', $inventaire->id)
            ->with('success', 'Inventaire validé, le stock a été ajusté.');
    }
}
