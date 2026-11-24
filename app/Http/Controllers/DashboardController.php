<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facture;
use App\Models\Client;
use App\Models\Produit;
use App\Models\MouvementStock;
use App\Models\Paiement;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** =========================
         *  PÉRIODE (mois sélectionné)
         *  ========================= */
        $month = now()->startOfMonth();
        if ($request->filled('month') && preg_match('/^\d{4}-\d{2}$/', $request->month)) {
            $month = Carbon::createFromFormat('!Y-m', $request->month)->startOfMonth();
        }
        if ($month->gt(now())) {
            $month = now()->startOfMonth();
        }

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();
        $prevStart    = $month->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd      = $prevStart->copy()->endOfMonth();
        $isCurrentMonth = $month->isSameMonth(now());

        // Reçus valides (une facture annulée ne compte pas dans le chiffre d'affaires)
        $recus = fn () => Facture::where('type_document', 'recu')->where('status', '!=', 'annule');
        $devis = fn () => Facture::where('type_document', 'pro-forma')->where('status', '!=', 'annule');

        /** =========================
         *  INDICATEURS CLÉS
         *  ========================= */
        $caMois      = (float) $recus()->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total');
        $caMoisPrec  = (float) $recus()->whereBetween('created_at', [$prevStart, $prevEnd])->sum('total');
        $nbRecusMois = $recus()->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $nbRecusMoisPrec = $recus()->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $encaisseMois = (float) Paiement::whereHas('facture', fn ($q) => $q->where('status', '!=', 'annule'))
            ->whereBetween('date_paiement', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('montant');

        $nbDevisMois  = $devis()->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $montantDevisMois = (float) $devis()->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total');

        $caAujourdhui = (float) $recus()->whereDate('created_at', now()->toDateString())->sum('total');
        $caAnnee      = (float) $recus()->whereYear('created_at', $month->year)->sum('total');

        // Reste à encaisser (toutes périodes confondues) : reçus non soldés
        $impayesQuery = fn () => $recus()
            ->where('status', '!=', 'payee')
            ->whereRaw('total - montant_paye - COALESCE(avance, 0) > 0');
        $resteAEncaisser = (float) $impayesQuery()
            ->selectRaw('COALESCE(SUM(GREATEST(total - montant_paye - COALESCE(avance, 0), 0)), 0) as reste')
            ->value('reste');
        $nbImpayes = $impayesQuery()->count();

        $facturesARelancer = $impayesQuery()
            ->with('client')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $variation = fn ($actuel, $precedent) => $precedent > 0
            ? round((($actuel - $precedent) / $precedent) * 100, 1)
            : null;
        $variationCa = $variation($caMois, $caMoisPrec);
        $variationRecus = $variation($nbRecusMois, $nbRecusMoisPrec);

        /** =========================
         *  COURBE QUOTIDIENNE (2 requêtes groupées)
         *  ========================= */
        $parJour = function ($query) use ($startOfMonth, $endOfMonth) {
            return $query->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('DATE(created_at) as jour, SUM(total) as montant')
                ->groupByRaw('DATE(created_at)')
                ->pluck('montant', 'jour')
                ->mapWithKeys(fn ($v, $k) => [Carbon::parse($k)->toDateString() => (float) $v]);
        };
        $recusParJour = $parJour($recus());
        $devisParJour = $parJour($devis());

        $chartLabels = [];
        $chartRecu = [];
        $chartProforma = [];
        $dernierJour = $isCurrentMonth ? now()->copy() : $endOfMonth->copy();
        for ($date = $startOfMonth->copy(); $date->lte($dernierJour); $date->addDay()) {
            $key = $date->toDateString();
            $chartLabels[] = $date->format('d/m');
            $chartRecu[] = $recusParJour[$key] ?? 0;
            $chartProforma[] = $devisParJour[$key] ?? 0;
        }

        /** =========================
         *  STOCK
         *  ========================= */
        $produitsCount = Produit::count();
        $produitsCritiquesCount = Produit::whereColumn('stock', '<=', 'seuil_alerte')->count();
        $valeurStock = (float) Produit::selectRaw('COALESCE(SUM(stock * prix_achat), 0) as v')->value('v');

        $entreesStockMonth = (int) MouvementStock::where('type', 'entree')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('quantite');

        $sortiesStockMonth = (int) MouvementStock::where('type', 'sortie')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('quantite');

        $produitsCritiques = Produit::whereColumn('stock', '<=', 'seuil_alerte')
            ->orderBy('stock')
            ->paginate(5, ['*'], 'stock_page')
            ->withQueryString();

        $topProduitsSortie = MouvementStock::selectRaw('produit_id, SUM(quantite) as total')
            ->where('type', 'sortie')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('produit_id')
            ->orderByDesc('total')
            ->with('produit')
            ->limit(5)
            ->get();

        /** =========================
         *  ACTIVITÉ RÉCENTE
         *  ========================= */
        $clientsCount = Client::count();

        $recentFactures = Facture::with('client')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(6, ['*'], 'factures_page')
            ->withQueryString();

        return view('dashboard.index', compact(
            'month', 'prevStart', 'isCurrentMonth',
            'caMois', 'caMoisPrec', 'variationCa', 'nbRecusMois', 'variationRecus',
            'encaisseMois', 'nbDevisMois', 'montantDevisMois', 'caAujourdhui', 'caAnnee',
            'resteAEncaisser', 'nbImpayes', 'facturesARelancer',
            'chartLabels', 'chartRecu', 'chartProforma',
            'produitsCount', 'produitsCritiquesCount', 'valeurStock',
            'entreesStockMonth', 'sortiesStockMonth', 'produitsCritiques', 'topProduitsSortie',
            'clientsCount', 'recentFactures'
        ));
    }

    /**
     * Dashboard Employé
     */
    public function employe()
    {
        $month = now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Stats for current month
        $recuMonthCount = \App\Models\Facture::where('type_document', 'recu')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $recuMonthSum = \App\Models\Facture::where('type_document', 'recu')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $produitsCount = \App\Models\Produit::count();
        $clientsCount  = \App\Models\Client::count();
        $facturesCount = \App\Models\Facture::count();
        
        // Mes factures récentes (celles créées par l'employé connecté)
        $recentFactures = \App\Models\Facture::with('client')
                            ->where('user_id', auth()->id())
                            ->orderByDesc('created_at')
                            ->paginate(5, ['*'], 'factures_page')
                            ->withQueryString();

        // Clients récents
        $recentClients = \App\Models\Client::orderByDesc('created_at')->limit(5)->get();

        // Produits en alerte (stock <= seuil_alerte)
        $produitsEnAlerte = \App\Models\Produit::whereColumn('stock', '<=', 'seuil_alerte')
            ->orderBy('stock', 'asc')
            ->paginate(5, ['*'], 'stock_page')
            ->withQueryString();

        return view('dashboard.employe', compact(
            'produitsCount',
            'clientsCount',
            'facturesCount',
            'recentFactures',
            'recentClients',
            'recuMonthCount',
            'recuMonthSum',
            'produitsEnAlerte'
        ));
    }
}
