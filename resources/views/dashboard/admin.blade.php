@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-subtitle">{{ now()->translatedFormat('l d F Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('factures.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nouveau recu
        </a>
        <a href="{{ route('factures.create', ['type' => 'pro-forma']) }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-plus me-1"></i> Nouveau devis
        </a>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $clientsCount }}</div>
                    <div class="stat-label">Clients</div>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $produitsCount }}</div>
                    <div class="stat-label">Produits</div>
                </div>
                <div class="stat-icon bg-secondary-subtle text-secondary">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ number_format($stockTotal, 0, ',', ' ') }}</div>
                    <div class="stat-label">Stock total</div>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-clipboard-data"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card {{ $produitsCritiques->count() > 0 ? 'danger' : 'success' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $produitsCritiques->count() }}</div>
                    <div class="stat-label">Produits critiques</div>
                </div>
                <div class="stat-icon {{ $produitsCritiques->count() > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sales Chart --}}
<div class="modern-card mb-4">
    <div class="card-header">
        <i class="bi bi-graph-up me-2"></i>Chiffre d'affaires du mois
    </div>
    <div class="card-body">
        <div style="height: 300px;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>

{{-- Recent Data --}}
<div class="row g-4 mb-4">
    {{-- Recent Invoices --}}
    <div class="col-12 col-lg-6">
        <div class="modern-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-text me-2"></i>Dernieres factures</span>
                <a href="{{ route('factures.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right"></i> Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentFactures->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Client</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFactures as $facture)
                                    <tr>
                                        <td>
                                            @if($facture->type_document === 'recu')
                                                <span class="badge bg-success-subtle text-success">Recu</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning">Pro-forma</span>
                                            @endif
                                        </td>
                                        <td>{{ $facture->client->nom ?? '-' }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($facture->total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-file-text"></i>
                        <h5>Aucune facture</h5>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Stock Movements --}}
    <div class="col-12 col-lg-6">
        <div class="modern-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Derniers mouvements de stock</span>
                <a href="{{ route('produits.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right"></i> Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentMouvements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Type</th>
                                    <th class="text-end">Quantite</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMouvements as $mouvement)
                                    <tr>
                                        <td>{{ $mouvement->produit->nom ?? '-' }}</td>
                                        <td>
                                            @if($mouvement->type === 'entree')
                                                <span class="badge bg-success-subtle text-success">Entree</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Sortie</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold">{{ $mouvement->quantite }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-clock-history"></i>
                        <h5>Aucun mouvement</h5>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Critical Products --}}
@if($produitsCritiques->count() > 0)
<div class="modern-card border-danger">
    <div class="card-header bg-danger-subtle text-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>Produits critiques
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-end">Stock actuel</th>
                        <th class="text-end">Seuil d'alerte</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produitsCritiques as $produit)
                        <tr>
                            <td class="fw-semibold">{{ $produit->nom }}</td>
                            <td class="text-end text-danger fw-bold">{{ $produit->stock }}</td>
                            <td class="text-end">{{ $produit->seuil_alerte }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

{{-- Chart Script --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Recu',
                        data: @json($chartRecu),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Pro-forma',
                        data: @json($chartProforma),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' {{ $app_settings->devise ?? 'FCFA' }}';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
