@extends('layouts.app')

@section('title', 'Tableau de bord')

@php
    $devise = $app_settings->devise ?? 'FCFA';
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    // Format compact pour les grands montants : 1,2 M / 845 k
    $compact = function ($n) use ($fmt) {
        $n = (float) $n;
        if (abs($n) >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1, ',', ' '), '0'), ',') . ' M';
        if (abs($n) >= 100000) return $fmt($n / 1000) . ' k';
        return $fmt($n);
    };
    $moisLabel = ucfirst($month->translatedFormat('F Y'));
    $moisPrecLabel = $prevStart->translatedFormat('F');
    $moisSuivant = $month->copy()->addMonthNoOverflow();
    $topMax = max(1, (int) ($topProduitsSortie->max('total') ?? 1));
@endphp

@push('head')
<style>
    .dash-header { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
    .dash-greeting { font-size: 1.5rem; font-weight: 700; margin: 0; }
    .dash-date { color: var(--secondary-color); font-size: .875rem; margin-top: .15rem; }

    .month-nav { display: inline-flex; align-items: center; background: #fff; border: 1px solid #dee2e6; border-radius: 50rem; padding: 2px; }
    .month-nav a, .month-nav span.disabled { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--dark-text); text-decoration: none; }
    .month-nav a:hover { background: rgba(13,110,253,.08); color: var(--primary-color); }
    .month-nav span.disabled { color: #ced4da; }
    .month-nav .month-label { font-weight: 600; font-size: .875rem; padding: 0 .5rem; min-width: 128px; text-align: center; }

    /* Tuiles KPI */
    .kpi { background: #fff; border-radius: .875rem; padding: 1.1rem 1.25rem; box-shadow: var(--card-shadow); height: 100%; display: flex; flex-direction: column; gap: .35rem; position: relative; transition: box-shadow .2s ease, transform .2s ease; }
    a.kpi { color: inherit; text-decoration: none; }
    a.kpi:hover { box-shadow: var(--card-shadow-hover); transform: translateY(-2px); }
    .kpi-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .kpi-label { font-size: .8125rem; font-weight: 500; color: var(--secondary-color); }
    .kpi-icon { width: 36px; height: 36px; border-radius: .625rem; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.15; letter-spacing: -.01em; white-space: nowrap; }
    .kpi-value small { font-size: .8rem; font-weight: 500; color: var(--secondary-color); margin-left: .2rem; }
    .kpi-foot { font-size: .78rem; color: var(--secondary-color); display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; margin-top: auto; }
    .delta { display: inline-flex; align-items: center; gap: .15rem; font-weight: 600; padding: .05rem .4rem; border-radius: 50rem; }
    .delta.up { color: #146c43; background: #d1e7dd; }
    .delta.down { color: #b02a37; background: #f8d7da; }
    .delta.flat { color: #495057; background: #e9ecef; }

    /* Cartes de section */
    .dash-card { background: #fff; border-radius: .875rem; box-shadow: var(--card-shadow); height: 100%; display: flex; flex-direction: column; }
    .dash-card-header { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; padding: 1rem 1.25rem .5rem; }
    .dash-card-title { font-size: .95rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: .5rem; }
    .dash-card-title i { color: var(--secondary-color); }
    .dash-card-body { padding: .5rem 1.25rem 1.25rem; flex: 1; }
    .link-more { font-size: .8125rem; font-weight: 500; text-decoration: none; white-space: nowrap; }
    .link-more:hover { text-decoration: underline; }

    /* Graphique */
    .chart-legend { display: flex; gap: 1rem; font-size: .8125rem; color: var(--secondary-color); }
    .chart-legend .key { display: inline-block; width: 14px; height: 2px; border-radius: 2px; vertical-align: middle; margin-right: .35rem; }
    .chart-wrap { position: relative; height: 290px; }
    .chart-table summary { font-size: .8125rem; color: var(--secondary-color); cursor: pointer; margin-top: .75rem; }
    .chart-table table { font-size: .8125rem; }

    /* Chiffres clés latéraux */
    .mini-figures { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .mini-figure { background: var(--light-bg); border-radius: .625rem; padding: .75rem .875rem; }
    .mini-figure .lbl { font-size: .75rem; color: var(--secondary-color); }
    .mini-figure .val { font-weight: 700; font-size: 1.05rem; white-space: nowrap; }

    /* Listes */
    .list-row { display: flex; align-items: center; gap: .75rem; padding: .65rem 0; border-bottom: 1px solid rgba(0,0,0,.05); color: inherit; text-decoration: none; }
    .list-row:last-child { border-bottom: 0; }
    a.list-row:hover .list-main { color: var(--primary-color); }
    .list-main { flex: 1; min-width: 0; font-weight: 500; font-size: .875rem; }
    .list-main .sub { display: block; font-weight: 400; font-size: .75rem; color: var(--secondary-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .list-end { text-align: right; font-size: .875rem; font-weight: 600; white-space: nowrap; }
    .list-end .sub { display: block; font-weight: 400; font-size: .75rem; color: var(--secondary-color); }
    .avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(13,110,253,.1); color: var(--primary-color); display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: .8rem; flex-shrink: 0; }

    /* Jauge de stock (même teinte, piste plus claire) */
    .meter { height: 6px; border-radius: 3px; background: #f8d7da; overflow: hidden; margin-top: .35rem; }
    .meter > span { display: block; height: 100%; background: #dc3545; border-radius: 3px; }
    .meter.warn { background: #fff3cd; } .meter.warn > span { background: #e0a800; }

    /* Barres top produits (une seule teinte : magnitude) */
    .bar-track { height: 8px; background: #eef4fd; border-radius: 4px; margin-top: .35rem; }
    .bar-fill { height: 100%; background: #2a78d6; border-radius: 0 4px 4px 0; }

    .stock-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; margin-bottom: 1rem; }
    .stock-strip > div { background: var(--light-bg); border-radius: .625rem; padding: .6rem .75rem; }
    .stock-strip .lbl { font-size: .72rem; color: var(--secondary-color); }
    .stock-strip .val { font-weight: 700; font-size: 1rem; white-space: nowrap; }
    @media (max-width: 575.98px) { .stock-strip { grid-template-columns: repeat(2, 1fr); } .kpi-value { font-size: 1.3rem; } }

    .dash-empty { text-align: center; color: var(--secondary-color); padding: 1.5rem .5rem; font-size: .875rem; }
    .dash-empty i { font-size: 2rem; display: block; margin-bottom: .35rem; opacity: .5; }
</style>
@endpush

@section('content')

{{-- En-tête : salutation, navigation par mois, actions rapides --}}
<div class="dash-header">
    <div>
        <h1 class="dash-greeting">Bonjour, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <div class="dash-date">{{ ucfirst(now()->translatedFormat('l d F Y')) }}</div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="month-nav" aria-label="Choisir le mois">
            <a href="{{ route('dashboard', ['month' => $prevStart->format('Y-m')]) }}" title="Mois précédent"><i class="bi bi-chevron-left"></i></a>
            <span class="month-label">{{ $moisLabel }}</span>
            @if($isCurrentMonth)
                <span class="disabled"><i class="bi bi-chevron-right"></i></span>
            @else
                <a href="{{ route('dashboard', ['month' => $moisSuivant->format('Y-m')]) }}" title="Mois suivant"><i class="bi bi-chevron-right"></i></a>
            @endif
        </div>
        @unless($isCurrentMonth)
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-link text-decoration-none">Mois en cours</a>
        @endunless
        <a href="{{ route('factures.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-receipt me-1"></i>Nouveau reçu
        </a>
        <a href="{{ route('devis.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-plus me-1"></i>Nouveau devis
        </a>
    </div>
</div>

{{-- Indicateurs clés --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi">
            <div class="kpi-head">
                <span class="kpi-label">Chiffre d'affaires</span>
                <span class="kpi-icon bg-primary-subtle text-primary"><i class="bi bi-graph-up-arrow"></i></span>
            </div>
            <div class="kpi-value" title="{{ $fmt($caMois) }} {{ $devise }}">{{ $compact($caMois) }}<small>{{ $devise }}</small></div>
            <div class="kpi-foot">
                @if(!is_null($variationCa))
                    <span class="delta {{ $variationCa > 0 ? 'up' : ($variationCa < 0 ? 'down' : 'flat') }}">
                        <i class="bi bi-arrow-{{ $variationCa > 0 ? 'up' : ($variationCa < 0 ? 'down' : 'right') }}-short"></i>{{ $variationCa > 0 ? '+' : '' }}{{ str_replace('.', ',', $variationCa) }} %
                    </span>
                    <span>vs {{ $moisPrecLabel }}</span>
                @else
                    <span>{{ $nbRecusMois }} reçu(s) ce mois</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi">
            <div class="kpi-head">
                <span class="kpi-label">Encaissé</span>
                <span class="kpi-icon bg-success-subtle text-success"><i class="bi bi-cash-coin"></i></span>
            </div>
            <div class="kpi-value" title="{{ $fmt($encaisseMois) }} {{ $devise }}">{{ $compact($encaisseMois) }}<small>{{ $devise }}</small></div>
            <div class="kpi-foot"><span>Paiements reçus en {{ $month->translatedFormat('F') }}</span></div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ route('factures.index', ['tab' => 'attente']) }}" class="kpi" title="Voir les factures en attente">
            <div class="kpi-head">
                <span class="kpi-label">Reste à encaisser</span>
                <span class="kpi-icon {{ $resteAEncaisser > 0 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' }}">
                    <i class="bi bi-{{ $resteAEncaisser > 0 ? 'hourglass-split' : 'check2-circle' }}"></i>
                </span>
            </div>
            <div class="kpi-value" title="{{ $fmt($resteAEncaisser) }} {{ $devise }}">{{ $compact($resteAEncaisser) }}<small>{{ $devise }}</small></div>
            <div class="kpi-foot">
                <span>{{ $nbImpayes }} facture(s) non soldée(s)</span>
                <i class="bi bi-chevron-right ms-auto"></i>
            </div>
        </a>
    </div>

    <div class="col-6 col-xl-3">
        <a href="{{ route('factures.index', ['tab' => 'devis']) }}" class="kpi" title="Voir les devis">
            <div class="kpi-head">
                <span class="kpi-label">Devis émis</span>
                <span class="kpi-icon bg-info-subtle text-info-emphasis"><i class="bi bi-file-earmark-text"></i></span>
            </div>
            <div class="kpi-value">{{ $nbDevisMois }}<small>devis</small></div>
            <div class="kpi-foot">
                <span>{{ $fmt($montantDevisMois) }} {{ $devise }} proposés</span>
                <i class="bi bi-chevron-right ms-auto"></i>
            </div>
        </a>
    </div>
</div>

{{-- Courbe + colonne latérale --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="dash-card">
            <div class="dash-card-header">
                <h2 class="dash-card-title"><i class="bi bi-activity"></i>Activité de {{ $month->translatedFormat('F') }}</h2>
                <div class="chart-legend" aria-hidden="true">
                    <span><span class="key" style="background:#2a78d6"></span>Reçus</span>
                    <span><span class="key" style="background:#eb6834"></span>Devis</span>
                </div>
            </div>
            <div class="dash-card-body">
                <div class="chart-wrap">
                    <canvas id="caChart" role="img" aria-label="Montant des reçus et des devis par jour en {{ $moisLabel }}"></canvas>
                </div>
                <details class="chart-table">
                    <summary>Voir les données sous forme de tableau</summary>
                    <div class="table-responsive mt-2" style="max-height: 240px;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Jour</th><th class="text-end">Reçus</th><th class="text-end">Devis</th></tr></thead>
                            <tbody>
                                @foreach($chartLabels as $i => $jour)
                                    @if($chartRecu[$i] > 0 || $chartProforma[$i] > 0)
                                        <tr>
                                            <td>{{ $jour }}</td>
                                            <td class="text-end">{{ $fmt($chartRecu[$i]) }}</td>
                                            <td class="text-end">{{ $fmt($chartProforma[$i]) }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <h2 class="dash-card-title"><i class="bi bi-bell"></i>À relancer</h2>
                @if($nbImpayes > 0)
                    <a href="{{ route('factures.index', ['tab' => 'attente']) }}" class="link-more">Tout voir ({{ $nbImpayes }})</a>
                @endif
            </div>
            <div class="dash-card-body">
                <div class="mini-figures mb-3">
                    <div class="mini-figure">
                        <div class="lbl">Ventes du jour</div>
                        <div class="val">{{ $compact($caAujourdhui) }} <span class="small fw-normal text-muted">{{ $devise }}</span></div>
                    </div>
                    <div class="mini-figure">
                        <div class="lbl">Cumul {{ $month->year }}</div>
                        <div class="val">{{ $compact($caAnnee) }} <span class="small fw-normal text-muted">{{ $devise }}</span></div>
                    </div>
                </div>

                @forelse($facturesARelancer as $f)
                    @php
                        $reste = max(0, $f->total - $f->montant_paye - ($f->avance ?? 0));
                        $jours = (int) $f->created_at->copy()->startOfDay()->diffInDays(now()->startOfDay());
                    @endphp
                    <a href="{{ route('factures.show', $f->id) }}" class="list-row">
                        <span class="avatar">{{ mb_strtoupper(mb_substr($f->client->nom ?? '?', 0, 1)) }}{{ mb_strtoupper(mb_substr($f->client->prenom ?? '', 0, 1)) }}</span>
                        <span class="list-main">
                            {{ $f->client->nomComplet ?? 'Client supprimé' }}
                            <span class="sub">{{ $f->numero_facture }} · {{ $jours === 0 ? "aujourd'hui" : "il y a $jours j" }}</span>
                        </span>
                        <span class="list-end">
                            {{ $fmt($reste) }}
                            <span class="sub">{{ $f->status === 'partiellement_payee' ? 'Partiel' : 'Non payée' }}</span>
                        </span>
                    </a>
                @empty
                    <div class="dash-empty">
                        <i class="bi bi-check2-circle"></i>
                        Toutes les factures sont soldées.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Stock --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="dash-card">
            <div class="dash-card-header">
                <h2 class="dash-card-title">
                    <i class="bi bi-box-seam"></i>Stock
                    @if($produitsCritiquesCount > 0)
                        <span class="badge bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ $produitsCritiquesCount }} en alerte</span>
                    @endif
                </h2>
                <a href="{{ route('produits.index') }}" class="link-more">Tous les produits</a>
            </div>
            <div class="dash-card-body">
                <div class="stock-strip">
                    <div><div class="lbl">Produits</div><div class="val">{{ $produitsCount }}</div></div>
                    <div><div class="lbl">Valeur du stock</div><div class="val">{{ $compact($valeurStock) }}</div></div>
                    <div><div class="lbl"><i class="bi bi-arrow-down-left text-success"></i> Entrées</div><div class="val">{{ $fmt($entreesStockMonth) }}</div></div>
                    <div><div class="lbl"><i class="bi bi-arrow-up-right text-danger"></i> Sorties</div><div class="val">{{ $fmt($sortiesStockMonth) }}</div></div>
                </div>

                @forelse($produitsCritiques as $produit)
                    @php
                        $ratio = $produit->seuil_alerte > 0 ? min(100, max(0, $produit->stock / $produit->seuil_alerte * 100)) : 0;
                        $rupture = $produit->stock <= 0;
                    @endphp
                    <div class="list-row">
                        <span class="list-main">
                            <a href="{{ route('produits.show', $produit->id) }}" class="text-reset text-decoration-none">{{ $produit->nom }}</a>
                            <span class="sub">
                                @if($rupture)
                                    <i class="bi bi-x-octagon text-danger"></i> Rupture de stock
                                @else
                                    <i class="bi bi-exclamation-triangle text-warning"></i> {{ $produit->stock }} restant(s) · seuil {{ $produit->seuil_alerte }}
                                @endif
                            </span>
                            <div class="meter {{ $rupture ? '' : 'warn' }}" role="progressbar" aria-valuenow="{{ $produit->stock }}" aria-valuemin="0" aria-valuemax="{{ $produit->seuil_alerte }}">
                                <span style="width: {{ $rupture ? 100 : max(4, $ratio) }}%"></span>
                            </div>
                        </span>
                        <a href="{{ route('produits.reapprovisionnement', $produit->id) }}" class="btn btn-sm btn-outline-primary" title="Réapprovisionner">
                            <i class="bi bi-plus-lg"></i><span class="d-none d-sm-inline ms-1">Réappro.</span>
                        </a>
                    </div>
                @empty
                    <div class="dash-empty">
                        <i class="bi bi-check2-circle"></i>
                        Aucun produit sous le seuil d'alerte.
                    </div>
                @endforelse

                @if($produitsCritiques->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-2 small">
                        <span class="text-muted">Page {{ $produitsCritiques->currentPage() }} / {{ $produitsCritiques->lastPage() }}</span>
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-outline-secondary {{ $produitsCritiques->onFirstPage() ? 'disabled' : '' }}" href="{{ $produitsCritiques->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                            <a class="btn btn-outline-secondary {{ $produitsCritiques->hasMorePages() ? '' : 'disabled' }}" href="{{ $produitsCritiques->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="dash-card">
            <div class="dash-card-header">
                <h2 class="dash-card-title"><i class="bi bi-trophy"></i>Meilleures ventes</h2>
                <span class="small text-muted">Quantités sorties · {{ $month->translatedFormat('F') }}</span>
            </div>
            <div class="dash-card-body">
                @forelse($topProduitsSortie as $i => $top)
                    <div class="list-row d-block">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="list-main text-truncate">
                                <span class="text-muted me-1">{{ $i + 1 }}.</span>{{ $top->produit->nom ?? 'Produit supprimé' }}
                            </span>
                            <span class="list-end">{{ $fmt($top->total) }}</span>
                        </div>
                        <div class="bar-track"><div class="bar-fill" style="width: {{ max(3, $top->total / $topMax * 100) }}%"></div></div>
                    </div>
                @empty
                    <div class="dash-empty">
                        <i class="bi bi-bar-chart"></i>
                        Aucune sortie de stock ce mois-ci.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Dernières factures --}}
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h2 class="dash-card-title"><i class="bi bi-clock-history"></i>Dernières factures</h2>
        <a href="{{ route('factures.index') }}" class="link-more">Toutes les factures</a>
    </div>
    <div class="dash-card-body pt-0">
        @if($recentFactures->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th class="d-none d-md-table-cell">Statut</th>
                            <th class="d-none d-sm-table-cell">Date</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentFactures as $facture)
                            <tr style="cursor:pointer" onclick="window.location='{{ route('factures.show', $facture->id) }}'">
                                <td>
                                    <a href="{{ route('factures.show', $facture->id) }}" class="fw-semibold text-reset text-decoration-none">
                                        {{ $facture->client->nomComplet ?? 'Client supprimé' }}
                                    </a>
                                    <span class="d-block small text-muted">{{ $facture->numero_facture }}</span>
                                    <span class="d-md-none badge {{ $facture->etat_badge_class }} mt-1">{{ $facture->etat_label }}</span>
                                </td>
                                <td class="d-none d-md-table-cell"><span class="badge {{ $facture->etat_badge_class }}">{{ $facture->etat_label }}</span></td>
                                <td class="text-muted d-none d-sm-table-cell">{{ $facture->created_at->format('d/m/Y') }}</td>
                                <td class="text-end fw-semibold text-nowrap">{{ $fmt($facture->total) }} <span class="small text-muted fw-normal">{{ $devise }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recentFactures->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3 small">
                    <span class="text-muted">Page {{ $recentFactures->currentPage() }} / {{ $recentFactures->lastPage() }}</span>
                    <div class="btn-group btn-group-sm">
                        <a class="btn btn-outline-secondary {{ $recentFactures->onFirstPage() ? 'disabled' : '' }}" href="{{ $recentFactures->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        <a class="btn btn-outline-secondary {{ $recentFactures->hasMorePages() ? '' : 'disabled' }}" href="{{ $recentFactures->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
            @endif
        @else
            <div class="dash-empty">
                <i class="bi bi-file-earmark-text"></i>
                Aucune facture pour le moment.
                <div class="mt-2"><a href="{{ route('factures.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Créer un reçu</a></div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('caChart');
    if (!el || typeof Chart === 'undefined') return;

    const devise = @json($devise);
    const plein = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 });
    const compact = v => Math.abs(v) >= 1e6 ? (v / 1e6).toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' M'
                      : Math.abs(v) >= 1e4 ? Math.round(v / 1e3).toLocaleString('fr-FR') + ' k'
                      : plein.format(v);

    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Reçus',
                    data: @json($chartRecu),
                    borderColor: '#2a78d6',
                    backgroundColor: 'rgba(42, 120, 214, 0.10)',
                    fill: true,
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBorderWidth: 2,
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBackgroundColor: '#2a78d6',
                },
                {
                    label: 'Devis',
                    data: @json($chartProforma),
                    borderColor: '#eb6834',
                    backgroundColor: 'transparent',
                    fill: false,
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBorderWidth: 2,
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBackgroundColor: '#eb6834',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#212529',
                    padding: 10,
                    cornerRadius: 8,
                    boxPadding: 4,
                    usePointStyle: true,
                    callbacks: {
                        label: ctx => ' ' + ctx.dataset.label + ' : ' + plein.format(ctx.parsed.y) + ' ' + devise,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { color: '#dee2e6' },
                    ticks: { color: '#6c757d', maxRotation: 0, autoSkip: true, maxTicksLimit: 10 },
                },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: '#eef0f2', lineWidth: 1 },
                    ticks: { color: '#6c757d', maxTicksLimit: 5, callback: v => compact(v) },
                },
            },
        },
    });
});
</script>
@endpush
