<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\Produit;
use App\Models\MouvementStock;
use Carbon\Carbon;
use PDF;

class ReportController extends Controller
{
    private function resolvePeriod(Request $request): array
    {
        $preset = $request->input('date_preset');
        $today = now();

        [$start, $end] = match ($preset) {
            'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'week' => [$today->copy()->startOfWeek(), $today->copy()->endOfDay()],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            default => [
                $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : $today->copy()->startOfMonth(),
                $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : $today->copy()->endOfMonth(),
            ],
        };

        $label = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');

        return [$start, $end, $label];
    }

    public function index()
    {
        return view('reports.index');
    }

    /* ================= ÉTAT CLIENTS ================= */

    private function clientsData()
    {
        return Client::withCount('factures')
            ->withSum(['factures as total_facture' => fn ($q) => $q->where('type_document', 'recu')], 'total')
            ->withSum(['factures as total_paye' => fn ($q) => $q->where('type_document', 'recu')], 'montant_paye')
            ->orderBy('nom')
            ->get();
    }

    public function clients(Request $request)
    {
        $clients = $this->clientsData();
        return view('reports.clients', compact('clients'));
    }

    public function clientsPdf(Request $request)
    {
        $clients = $this->clientsData();
        $pdf = PDF::loadView('reports.clients_pdf', compact('clients'));
        return $pdf->stream('etat_clients.pdf');
    }

    /* ================= ÉTAT FACTURES ================= */

    private function facturesData(Request $request)
    {
        [$start, $end, $label] = $this->resolvePeriod($request);

        $factures = Facture::with('client')
            ->where('type_document', 'recu')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();

        $total = $factures->sum('total');

        return [$factures, $total, $start, $end, $label];
    }

    public function factures(Request $request)
    {
        [$factures, $total, $start, $end, $label] = $this->facturesData($request);
        return view('reports.factures', compact('factures', 'total', 'start', 'end', 'label'));
    }

    public function facturesPdf(Request $request)
    {
        [$factures, $total, $start, $end, $label] = $this->facturesData($request);
        $period = $request->input('date_preset', 'custom');
        $pdf = PDF::loadView('reports.sales', compact('factures', 'total', 'period', 'start', 'end'));
        return $pdf->stream('rapport_factures.pdf');
    }

    /* ================= ÉTAT PRODUITS ================= */

    private function produitsData(Request $request)
    {
        [$start, $end, $label] = $this->resolvePeriod($request);

        $produits = Produit::orderBy('nom')->get();
        $valorisationTotale = $produits->sum(fn ($p) => $p->stock * $p->prix_achat);

        $topProduits = MouvementStock::selectRaw('produit_id, SUM(quantite) as total')
            ->where('type', 'sortie')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('produit_id')
            ->orderByDesc('total')
            ->with('produit')
            ->limit(10)
            ->get();

        return [$produits, $valorisationTotale, $topProduits, $start, $end, $label];
    }

    public function produits(Request $request)
    {
        [$produits, $valorisationTotale, $topProduits, $start, $end, $label] = $this->produitsData($request);
        return view('reports.produits', compact('produits', 'valorisationTotale', 'topProduits', 'start', 'end', 'label'));
    }

    public function produitsPdf(Request $request)
    {
        [$produits, $valorisationTotale, $topProduits, $start, $end, $label] = $this->produitsData($request);
        $pdf = PDF::loadView('reports.produits_pdf', compact('produits', 'valorisationTotale', 'topProduits', 'start', 'end', 'label'));
        return $pdf->stream('etat_produits.pdf');
    }

    /* ================= CHIFFRE D'AFFAIRES & BÉNÉFICES ================= */

    private function caEtBeneficesData(Request $request)
    {
        [$start, $end, $label] = $this->resolvePeriod($request);

        $factures = Facture::where('type_document', 'recu')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $chiffreAffaires = $factures->sum('total');

        $lignes = LigneFacture::whereIn('facture_id', $factures->pluck('id'))->with('produit')->get();

        $benefice = $lignes->sum(function ($ligne) {
            $coutUnitaire = $ligne->produit->prix_achat ?? 0;
            return ($ligne->prix_unitaire - $coutUnitaire) * $ligne->quantite;
        });

        return [$chiffreAffaires, $benefice, $factures->count(), $start, $end, $label];
    }

    public function caEtBenefices(Request $request)
    {
        [$chiffreAffaires, $benefice, $nbFactures, $start, $end, $label] = $this->caEtBeneficesData($request);
        return view('reports.ca_benefices', compact('chiffreAffaires', 'benefice', 'nbFactures', 'start', 'end', 'label'));
    }

    public function caEtBeneficesPdf(Request $request)
    {
        [$chiffreAffaires, $benefice, $nbFactures, $start, $end, $label] = $this->caEtBeneficesData($request);
        $pdf = PDF::loadView('reports.ca_benefices_pdf', compact('chiffreAffaires', 'benefice', 'nbFactures', 'start', 'end', 'label'));
        return $pdf->stream('rapport_ca_benefices.pdf');
    }
}
