@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Tableau de bord</h1>
        <p class="page-subtitle d-none d-sm-block">{{ now()->translatedFormat('l d F Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('factures.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i><span class="d-none d-md-inline">Nouveau recu</span>
        </a>
        <a href="{{ route('devis.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-plus me-1"></i><span class="d-none d-md-inline">Nouveau devis</span>
        </a>
    </div>
</div>

{{-- Admin Statistics Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $proformasMonthCount }}</div>
                    <div class="stat-label">Devis ce mois</div>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $recuMonthCount }}</div>
                    <div class="stat-label">Recus ce mois</div>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ number_format($recuMonthSum, 0, ',', ' ') }}</div>
                    <div class="stat-label">Encaissement ({{ $app_settings->devise ?? 'FCFA' }})</div>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-currency-exchange"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card {{ $produitsCritiquesCount > 0 ? 'danger' : 'success' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $produitsCritiquesCount }}</div>
                    <div class="stat-label">Produits en alerte</div>
                </div>
                <div class="stat-icon {{ $produitsCritiquesCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Products Alert --}}
@if($produitsCritiquesCount > 0)
<div class="modern-card mb-4 border-danger">
    <div class="card-header bg-danger-subtle text-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle"></i>
        <span class="me-auto">Produits en alerte de stock</span>
        <span class="badge bg-danger">{{ $produitsCritiquesCount }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end d-none d-md-table-cell">Seuil</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produitsCritiques as $produit)
                        <tr>
                            <td class="fw-semibold">{{ $produit->nom }}</td>
                            <td class="text-end text-danger fw-bold">{{ $produit->stock }}</td>
                            <td class="text-end d-none d-md-table-cell">{{ $produit->seuil_alerte }}</td>
                            <td class="text-end">
                                <a href="{{ route('produits.reapprovisionnement', $produit->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-plus-circle"></i><span class="d-none d-md-inline ms-1">Reapprovisionner</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($produitsCritiques->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                @foreach ($produitsCritiques->links()->elements as $element)
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $produitsCritiques->currentPage())
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
@endif

{{-- Revenue Chart --}}
<div class="modern-card mb-4">
    <div class="card-header">
        <i class="bi bi-graph-up me-2"></i>Evolution du chiffre d'affaires
    </div>
    <div class="card-body">
        <div style="height: 300px;">
            <canvas id="caChart"></canvas>
        </div>
    </div>
</div>

{{-- Recent Invoices --}}
<div class="modern-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-text me-2"></i>Factures recentes</span>
        <a href="{{ route('factures.index', ['tab' => 'attente']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> Voir tout
        </a>
    </div>
    <div class="card-body p-0">
        @if($recentFactures->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th class="text-end">Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentFactures as $facture)
                            <tr>
                                <td class="fw-semibold">{{ $facture->numero_facture }}</td>
                                <td>{{ $facture->client->nom ?? '-' }} {{ $facture->client->prenom ?? '' }}</td>
                                <td>
                                    @if($facture->type_document === 'recu')
                                        <span class="badge bg-success-subtle text-success">Recu</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Pro-forma</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($facture->total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                <td class="text-muted">{{ $facture->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-file-text"></i>
                <h5>Aucune facture</h5>
                <p class="text-muted">Aucune facture trouvee.</p>
                <a href="{{ route('factures.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Creer un recu
                </a>
            </div>
        @endif
    </div>
    @if($recentFactures->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                @foreach ($recentFactures->links()->elements as $element)
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $recentFactures->currentPage())
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

{{-- Chart Script --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('caChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Recus',
                        data: {!! json_encode($chartRecu) !!},
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Devis',
                        data: {!! json_encode($chartProforma) !!},
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

