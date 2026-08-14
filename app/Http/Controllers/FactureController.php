<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\Client;
use App\Models\Produit;
use App\Models\MouvementStock;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FactureController extends Controller
{
    /**
     * Page unique "Factures" à onglets : Devis / En attente / Reçus (payés)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $etat = $request->input('etat'); // 'non_payee' | 'partielle' | null (tous), pour l'onglet "En attente"
        $tab = $request->input('tab', 'devis');

        $searchScope = function ($query) use ($search) {
            $query->when($search, function ($q, $search) {
                $q->whereHas('client', fn($qc) =>
                    $qc->where('nom', 'like', "%$search%")
                )->orWhere('numero_facture', 'like', "%$search%");
            });
        };

        $devis = Facture::with('client', 'user')
            ->where('type_document', 'pro-forma')
            ->tap($searchScope)
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'devis_page')
            ->withQueryString();

        $enAttente = Facture::with('client', 'user')
            ->where('type_document', 'recu')
            ->where('status', '!=', 'payee')
            ->when($etat === 'non_payee', fn($q) => $q->where('status', '!=', 'partiellement_payee'))
            ->when($etat === 'partielle', fn($q) => $q->where('status', 'partiellement_payee'))
            ->tap($searchScope)
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'attente_page')
            ->withQueryString();

        $recus = Facture::with('client', 'user')
            ->where('type_document', 'recu')
            ->where('status', 'payee')
            ->tap($searchScope)
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'recu_page')
            ->withQueryString();

        return view('factures.index', compact('devis', 'enAttente', 'recus', 'tab'));
    }

    public function createProforma()
    {
        return view('devis.create', [
            'clients' => Client::all(),
            'produits' => Produit::all(),
        ]);
    }

    public function createRecu()
    {
        return view('factures.create', [
            'clients' => Client::all(),
            'produits' => Produit::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type_document' => 'required|in:pro-forma,recu',
            'status' => 'nullable|in:pro-forma,recu',
            'date_echeance' => 'nullable|date|after_or_equal:today',
            'tva' => 'nullable|numeric|min:0|max:100',
            'remise' => 'nullable|numeric|min:0',
            'lignes' => 'required|array|min:1',
            'lignes.*.produit_id' => 'required|exists:produits,id',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.remise' => 'nullable|numeric|min:0',
        ]);

        $produitIds = collect($request->lignes)->pluck('produit_id');

        if ($produitIds->count() !== $produitIds->unique()->count()) {
            return redirect()->back()
                ->withErrors([
                    'lignes' => 'Un même produit ne peut pas apparaître sur plusieurs lignes.'
                ])
                ->withInput();
        }

        $client = Client::find($request->client_id);

        try {
            /* TRANSACTION PROPRE - verrou stock + prix catalogue pour eviter survente et manipulation de prix */
            $facture = DB::transaction(function () use ($request, $produitIds) {
                $produits = Produit::whereIn('id', $produitIds)->lockForUpdate()->get()->keyBy('id');

                if ($request->type_document === 'recu') {
                    foreach ($request->lignes as $index => $ligne) {
                        $produit = $produits[$ligne['produit_id']];

                        if ((int) $ligne['quantite'] > $produit->stock) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                "lignes.$index.quantite" =>
                                    "(reste: {$produit->stock})"
                            ]);
                        }
                    }
                }

                $status = $request->input('status') ?? ($request->type_document === 'recu' ? 'recu' : 'pro-forma');

                $facture = Facture::create([
                    'client_id' => $request->client_id,
                    'user_id' => auth()->id(),
                    'type_document' => $request->type_document,
                    'status' => $status,
                    'date_echeance' => $request->date_echeance,
                    'numero_facture' => Facture::generateNumeroFor($request->type_document),
                    'total' => 0,
                    'montant_paye' => 0,
                    'modifiable' => true,
                    'remise' => $request->remise ?? 0,
                    'tva' => $request->tva ?? 0,
                ]);

                $subtotal = 0;

                foreach ($request->lignes as $ligne) {
                    $produit = $produits[$ligne['produit_id']];
                    $quantite = (int) $ligne['quantite'];
                    // Prix toujours issu du catalogue produit, jamais de la saisie client (anti-manipulation)
                    $prixUnitaire = (int) $produit->prix_vente;
                    $remiseLigne = isset($ligne['remise']) ? (float) $ligne['remise'] : 0.0;

                    $totalLigne = max(0, ($quantite * $prixUnitaire) - $remiseLigne);

                    $subtotal += $totalLigne;

                    LigneFacture::create([
                        'facture_id' => $facture->id,
                        'produit_id' => $produit->id,
                        'quantite' => $quantite,
                        'prix_unitaire' => $prixUnitaire,
                        'total_ligne' => (int) $totalLigne,
                        'remise' => $remiseLigne,
                    ]);

                    if ($request->type_document === 'recu') {
                        MouvementStock::create([
                            'produit_id' => $produit->id,
                            'type' => 'sortie',
                            'quantite' => $quantite,
                            'raison' => "Vente facture {$facture->numero_facture}",
                        ]);

                        ActivityLogger::stockUpdate(
                            $produit,
                            "Vente de {$produit->nom} - Facture {$facture->numero_facture}",
                            $quantite
                        );
                    }
                }

                $invoiceRemise = (float) ($request->remise ?? 0);
                $tva = (float) ($request->tva ?? 0);

                $totalHt = max(0, $subtotal - $invoiceRemise);
                $totalTtc = round($totalHt * (1 + $tva / 100));

                $facture->update(['total' => $totalTtc]);

                return $facture;
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('factures.create')
                ->withErrors($e->errors())
                ->withInput();
        }

        // Enregistrer l'activité
        ActivityLogger::created(
            $facture,
            "Création {$facture->type_document} {$facture->numero_facture} pour {$client->nomComplet} - Total: {$facture->total}",
            $facture->type_document === 'recu' ? (float) $facture->total : null
        );

        if ($facture->type_document === 'recu') {
            return redirect()
                ->route('factures.show', $facture->id)
                ->with('success', 'Facture créée avec succès. Vous pouvez enregistrer un paiement ci-dessous.');
        }

        return redirect()
            ->route('factures.index', ['tab' => 'devis'])
            ->with('success', 'Devis créé avec succès.');
    }


    public function valider(Facture $facture)
    {
        if (!auth()->user()->isAdmin() && $facture->user_id !== auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas valider une facture créée par un autre utilisateur.');
        }

        if ($facture->type_document !== 'pro-forma') {
            return redirect()
                ->route('factures.show', $facture->id)
                ->with('error', 'Seul un pro-forma peut être validé.');
        }

        $oldNumero = $facture->numero_facture;
        $client = $facture->client;
        $total = $facture->total;

        try {
            /* TRANSACTION avec verrou stock pour eviter la survente concurrente */
            DB::transaction(function () use ($facture) {
                $lignes = $facture->lignes()->lockForUpdate()->get();
                $produits = Produit::whereIn('id', $lignes->pluck('produit_id')->unique())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($lignes as $ligne) {
                    $produit = $produits[$ligne->produit_id];

                    if ($ligne->quantite > $produit->stock) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "lignes.{$ligne->id}.quantite" =>
                                "Stock insuffisant pour {$produit->nom} (reste: {$produit->stock})"
                        ]);
                    }
                }

                foreach ($lignes as $ligne) {
                    $produit = $produits[$ligne->produit_id];

                    MouvementStock::create([
                        'produit_id' => $ligne->produit_id,
                        'type' => 'sortie',
                        'quantite' => $ligne->quantite,
                        'raison' => "Validation facture {$facture->numero_facture}",
                    ]);

                    ActivityLogger::stockUpdate(
                        $produit,
                        "Validation - vente de {$produit->nom} - Facture {$facture->numero_facture}",
                        $ligne->quantite
                    );
                }

                $facture->update([
                    'type_document' => 'recu',
                    'status' => 'recu',
                    'numero_facture' => Facture::generateNumeroFor('recu'),
                    'modifiable' => false,
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('factures.edit', $facture->id)
                ->withErrors($e->errors());
        }

        // Enregistrer l'activité
        ActivityLogger::validated(
            $facture,
            "Validation pro-forma {$oldNumero} → reçu {$facture->numero_facture} pour {$client->nomComplet}",
            (float) $total
        );

        return redirect()
            ->route('factures.show', $facture->id)
            ->with('success', 'Pro-forma validé en reçu. Vous pouvez enregistrer un paiement ci-dessous.');
    }


    public function show(Facture $facture)
    {
        $facture->load('client', 'lignes.produit', 'user', 'paiements.lignes.ligneFacture.produit');
        return view('factures.show', compact('facture'));
    }

    /**
     * Retourne un message d'erreur si $facture ne peut pas être modifiée par l'utilisateur courant, sinon null.
     */
    private function assertCanModify(Facture $facture): ?string
    {
        if (auth()->user()->isAdmin()) {
            if ($facture->isDefinitive() || $facture->status === 'annule') {
                return $facture->status === 'annule' ? 'Une facture annulée ne peut pas être modifiée.' : 'Un reçu ne peut pas être modifié.';
            }
            return null;
        }

        // Employe : uniquement ses propres devis (non validés), créés le jour même
        if ($facture->user_id !== auth()->id()) {
            return 'Vous ne pouvez pas modifier une facture créée par un autre utilisateur.';
        }

        if ($facture->isDefinitive()) {
            return 'Un reçu ne peut pas être modifié.';
        }

        $today = Carbon::today();
        $factureDate = Carbon::parse($facture->created_at)->startOfDay();

        if ($factureDate->lt($today)) {
            return 'Vous ne pouvez pas modifier une facture après le jour de création.';
        }

        return null;
    }

    public function edit(Facture $facture)
    {
        if ($message = $this->assertCanModify($facture)) {
            return back()->with('error', $message);
        }

        return view('factures.edit', [
            'facture' => $facture->load('lignes.produit'),
            'clients' => Client::all(),
            'produits' => Produit::all(),
        ]);
    }

    public function update(Request $request, Facture $facture)
    {
        if ($message = $this->assertCanModify($facture)) {
            return back()->with('error', $message);
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'nullable|in:pro-forma,recu',
            'tva' => 'nullable|numeric|min:0|max:100',
            'remise' => 'nullable|numeric|min:0',
            'lignes' => 'required|array|min:1',
            'lignes.*.produit_id' => 'required|exists:produits,id',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.remise' => 'nullable|numeric|min:0',
        ]);

        $oldData = [
            'client_id' => $facture->client_id,
            'total' => $facture->total,
            'lignes_count' => $facture->lignes->count(),
        ];

        $client = Client::find($request->client_id);
        $updatedTotal = 0;

        DB::transaction(function () use ($request, $facture, &$updatedTotal) {
            $facture->lignes()->delete();

            $subtotal = 0;
            $produitIds = collect($request->lignes)->pluck('produit_id');
            $produits = Produit::whereIn('id', $produitIds)->get()->keyBy('id');

            foreach ($request->lignes as $ligne) {
                $produit = $produits[$ligne['produit_id']];
                $quantite = (int) $ligne['quantite'];
                // Prix toujours issu du catalogue produit, jamais de la saisie client (anti-manipulation)
                $prixUnitaire = (int) $produit->prix_vente;
                $remiseLigne = isset($ligne['remise']) ? (float) $ligne['remise'] : 0.0;

                $totalLigne = max(0, ($quantite * $prixUnitaire) - $remiseLigne);

                $subtotal += $totalLigne;

                LigneFacture::create([
                    'facture_id' => $facture->id,
                    'produit_id' => $produit->id,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'total_ligne' => (int) $totalLigne,
                    'remise' => $remiseLigne,
                ]);
            }

            $invoiceRemise = (float) ($request->remise ?? 0);
            $tva = (float) ($request->tva ?? 0);

            $totalHt = max(0, $subtotal - $invoiceRemise);
            $totalTtc = round($totalHt * (1 + $tva / 100));
            $updatedTotal = $totalTtc;

            $facture->update([
                'client_id' => $request->client_id,
                'total' => $totalTtc,
                'date_echeance' => $request->date_echeance,
                'remise' => $invoiceRemise,
                'tva' => $tva,
                'status' => $request->input('status', $facture->status),
            ]);
        });

        // Enregistrer l'activité
        ActivityLogger::updated(
            $facture,
            "Mise à jour facture {$facture->numero_facture} pour {$client->nomComplet}",
            (float) ($updatedTotal - $oldData['total']),
            $oldData
        );

        return redirect()->route('factures.show', $facture->id)->with('success', 'Facture mise à jour.');
    }

    public function annuler(Facture $facture)
    {
        // Only admin or owner can cancel
        if (!auth()->user()->isAdmin() && $facture->user_id !== auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas annuler cette facture.');
        }

        if ($facture->status === 'annule') {
            return back()->with('info', 'La facture est déjà annulée.');
        }

        if ($facture->montant_paye > 0) {
            return back()->with('error', 'Cette facture a des paiements enregistrés (' .
                number_format($facture->montant_paye, 0, ',', ' ') .
                '). Supprimez ou remboursez les paiements avant de pouvoir l\'annuler.');
        }

        DB::transaction(function () use ($facture) {
            // If it was a 'recu', attempt to reverse stock mouvements
            if ($facture->type_document === 'recu') {
                foreach ($facture->lignes as $ligne) {
                    MouvementStock::create([
                        'produit_id' => $ligne->produit_id,
                        'type' => 'entree',
                        'quantite' => $ligne->quantite,
                        'raison' => "Annulation facture {$facture->numero_facture}",
                    ]);

                    ActivityLogger::stockUpdate(
                        $ligne->produit,
                        "Annulation - retour en stock de {$ligne->produit->nom} - Facture {$facture->numero_facture}",
                        $ligne->quantite
                    );
                }
            }

            $facture->update(['status' => 'annule', 'modifiable' => false]);

            ActivityLogger::deleted($facture, "Annulation facture {$facture->numero_facture}");
        });

        return redirect()->route('factures.show', $facture->id)->with('success', 'Facture annulée.');
    }

    public function destroy(Facture $facture)
    {
        // Only admin can delete any facture
        if (!auth()->user()->isAdmin()) {
            // Employe can only delete their own factures
            if ($facture->user_id !== auth()->id()) {
                return back()->with('error', 'Vous ne pouvez pas supprimer une facture créée par un autre utilisateur.');
            }

            // Employe can only delete pro-forma (not validated)
            if ($facture->isDefinitive()) {
                return back()->with('error', 'Vous ne pouvez pas supprimer un reçu.');
            }
        } else {
            if ($facture->isDefinitive()) {
                return back()->with('error', 'Un reçu ne peut pas être supprimé.');
            }
        }

        if ($facture->montant_paye > 0) {
            return back()->with('error', 'Cette facture a des paiements enregistrés (' .
                number_format($facture->montant_paye, 0, ',', ' ') .
                '). Supprimez ou remboursez les paiements avant de pouvoir la supprimer.');
        }

        $factureData = [
            'numero' => $facture->numero_facture,
            'client' => $facture->client->nomComplet ?? 'Inconnu',
            'total' => $facture->total,
        ];

        $facture->delete();

        // Enregistrer l'activité
        ActivityLogger::deleted(
            $facture,
            "Suppression {$factureData['numero']} pour {$factureData['client']} - Total: {$factureData['total']}"
        );

        return redirect()->route('factures.index', ['tab' => 'devis'])->with('success', 'Devis supprimé.');
    }
}
