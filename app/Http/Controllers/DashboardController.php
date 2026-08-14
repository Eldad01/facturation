<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facture;
use App\Models\Client;
use App\Models\Produit;
use App\Models\MouvementStock;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** =========================
         *  FILTRES
         *  ========================= */
        $month = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)
            : now();

        $year = $request->year ?? now()->year;
        $day  = $request->day ? Carbon::parse($request->day) : now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();

        $startOfDay = $day->copy()->startOfDay();
        $endOfDay   = $day->copy()->endOfDay();

        /** =========================
         *  FACTURATION
         *  ========================= */

        // Comptages
        $proformasMonthCount = Facture::where('type_document', 'pro-forma')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $recuMonthCount = Facture::where('type_document', 'recu')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $recuMonthSum = Facture::where('type_document', 'recu')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $recuDaySum = Facture::where('type_document', 'recu')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('total');

        $proformasMonthSum = Facture::where('type_document', 'pro-forma')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $recuYearSum = Facture::where('type_document', 'recu')
            ->whereYear('created_at', $year)
            ->sum('total');

        // Clients
        $clientsCount = Client::count();

        /** =========================
         *  COURBE DU CHIFFRE D’AFFAIRES
         *  ========================= */
        $chartLabels = [];
        $chartRecu = [];
        $chartProforma = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $chartLabels[] = $date->format('d M');

            $chartRecu[] = Facture::where('type_document', 'recu')
                ->whereDate('created_at', $date)
                ->sum('total');

            $chartProforma[] = Facture::where('type_document', 'pro-forma')
                ->whereDate('created_at', $date)
                ->sum('total');
        }

        /** =========================
         *  STOCK
         *  ========================= */
        $produitsCount = Produit::count();
        $stockTotal = Produit::sum('stock');
        $produitsCritiquesCount = Produit::whereColumn('stock', '<=', 'seuil_alerte')->count();

        $entreesStockMonth = MouvementStock::where('type', 'entree')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('quantite');

        $sortiesStockMonth = MouvementStock::where('type', 'sortie')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('quantite');

        $produitsCritiques = Produit::whereColumn('stock', '<=', 'seuil_alerte')
            ->orderBy('stock', 'asc')
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
         *  DERNIERS ÉLÉMENTS
         *  ========================= */
        $recentFactures = Facture::with('client')
            ->orderByDesc('created_at')
            ->paginate(5, ['*'], 'factures_page')
            ->withQueryString();

        $recentMouvements = MouvementStock::with('produit')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /** =========================
         *  ENVOI À LA VUE
         *  ========================= */
        return view('dashboard.index', compact(
            'month',
            'year',
            'day',

            // Facturation
            'proformasMonthCount',
            'recuMonthCount',
            'recuMonthSum',
            'recuDaySum',
            'proformasMonthSum',
            'recuYearSum',

            // Courbe
            'chartLabels',
            'chartRecu',
            'chartProforma',

            // Clients
            'clientsCount',

            // Stock
            'produitsCount',
            'stockTotal',
            'produitsCritiquesCount',
            'entreesStockMonth',
            'sortiesStockMonth',

            // Listes
            'produitsCritiques',
            'topProduitsSortie',
            'recentFactures',
            'recentMouvements'
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
