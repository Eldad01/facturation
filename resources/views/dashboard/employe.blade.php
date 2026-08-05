@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Tableau de bord</h1>
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
        <div class="stat-card {{ $produitsEnAlerte->count() > 0 ? 'warning' : 'success' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $produitsEnAlerte->count() }}</div>
                    <div class="stat-label">Produits en alerte</div>
                </div>
                <div class="stat-icon {{ $produitsEnAlerte->count() > 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }}">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $facturesCount }}</div>
                    <div class="stat-label">Total factures</div>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-file-text"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $clientsCount }}</div>
                    <div class="stat-label">Clients</div>
                </div>
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="row g-3 mb-4">
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
</div>

{{-- Products Alert --}}
@if($produitsEnAlerte->count() > 0)
<div class="modern-card mb-4 border-danger">
    <div class="card-header bg-danger-subtle text-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle"></i>
        <span class="me-auto">Produits en alerte de stock</span>
        <span class="badge bg-danger">{{ $produitsEnAlerte->count() }}</span>
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
                    @foreach($produitsEnAlerte as $produit)
                        <tr>
                            <td class="fw-semibold">{{ $produit->nom }}</td>
                            <td class="text-end text-danger fw-bold">{{ $produit->stock }}</td>
                            <td class="text-end d-none d-md-table-cell">{{ $produit->seuil_alerte }}</td>
                            <td class="text-end">
                                <a href="{{ route('produits.show', $produit->id) }}" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Recent Clients --}}
<div class="modern-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>Clients recents</span>
        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> Voir tout
        </a>
    </div>
    <div class="card-body p-0">
        @if($recentClients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prenom</th>
                            <th>Telephone</th>
                            <th>Adresse</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentClients as $client)
                            <tr>
                                <td class="fw-semibold">{{ $client->nom }}</td>
                                <td>{{ $client->prenom }}</td>
                                <td><a href="tel:{{ $client->telephone }}" class="text-decoration-none">{{ $client->telephone }}</a></td>
                                <td>{{ $client->adresse ?? '-' }}</td>
                                <td class="text-muted">{{ $client->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h5>Aucun client</h5>
                <p class="text-muted">Aucun client trouve.</p>
                <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter un client
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Recent Invoices --}}
<div class="modern-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-text me-2"></i>Factures recentes</span>
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
</div>
@endsection
