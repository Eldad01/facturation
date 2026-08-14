@extends('layouts.app')

@section('title', 'État produits')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">État produits</h1>
        <p class="page-subtitle d-none d-sm-block">Stock, valorisation et top ventes</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Retour</span>
        </a>
        <a href="{{ route('rapports.produits.pdf', request()->query()) }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i><span class="d-none d-sm-inline">Télécharger PDF</span>
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="modern-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Période</label>
                <select name="date_preset" class="form-select" onchange="this.form.submit()">
                    <option value="">Personnalisée</option>
                    <option value="today" {{ request('date_preset') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                    <option value="week" {{ request('date_preset') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                    <option value="month" {{ request('date_preset') == 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="year" {{ request('date_preset') == 'year' ? 'selected' : '' }}>Cette année</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date début</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date fin</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Filtrer
                </button>
            </div>
        </div>
    </div>
</form>

<div class="modern-card mb-4">
    <div class="card-body">
        <span class="text-muted">Valorisation totale du stock (au coût moyen d'achat) :</span>
        <span class="fw-bold text-primary">{{ number_format($valorisationTotale, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</span>
    </div>
</div>

<div class="modern-card mb-4">
    <div class="card-header"><i class="bi bi-trophy me-2"></i>Top produits vendus — {{ $label }}</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-end">Quantité vendue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProduits as $tp)
                        <tr>
                            <td class="fw-semibold">{{ $tp->produit->nom ?? '—' }}</td>
                            <td class="text-end">{{ $tp->total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Aucune vente sur cette période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modern-card">
    <div class="card-header"><i class="bi bi-box-seam me-2"></i>Stock &amp; valorisation</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end d-none d-md-table-cell">Coût moyen (CUMP)</th>
                        <th class="text-end">Valorisation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produits as $produit)
                        <tr>
                            <td class="fw-semibold">{{ $produit->nom }}</td>
                            <td class="text-end">{{ $produit->stock }}</td>
                            <td class="text-end d-none d-md-table-cell">{{ number_format($produit->prix_achat, 0, ',', ' ') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($produit->stock * $produit->prix_achat, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucun produit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($produits->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                @foreach ($produits->links()->elements as $element)
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $produits->currentPage())
                                <li class="page-item active"><span class="page-link" style="background-color: #0d6efd; border-color: #0d6efd;">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}" style="color: #0d6efd;">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection
