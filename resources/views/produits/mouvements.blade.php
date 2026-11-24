@extends('layouts.app')

@section('title', 'Mouvements de stock')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Mouvements de stock</h1>
        <p class="page-subtitle">{{ $produit->nom }}</p>
    </div>
    <div class="d-flex gap-2">
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('produits.reapprovisionnement', $produit->id) }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Reapprovisionner
                </a>
            @endif
        @endauth
    </div>
</div>

{{-- Stock Info --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card {{ $produit->enAlerte() ? 'warning' : 'success' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $produit->stock }}</div>
                    <div class="stat-label">Stock actuel</div>
                </div>
                <div class="stat-icon {{ $produit->enAlerte() ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }}">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $produit->seuil_alerte }}</div>
                    <div class="stat-label">Seuil d'alerte</div>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Movements Table --}}
<div class="modern-card">
    <div class="card-header">
        <i class="bi bi-clock-history me-2"></i>Historique des mouvements
    </div>
    <div class="card-body p-0">
        @if($mouvements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-end">Quantite</th>
                            <th>Reference</th>
                            <th>Raison</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mouvements as $m)
                            <tr>
                                <td>
                                    <div>{{ $m->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $m->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($m->type === 'entree')
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="bi bi-arrow-down me-1"></i>Entree
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="bi bi-arrow-up me-1"></i>Sortie
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ $m->quantite }}</td>
                                <td>{{ $m->reference ?? '-' }}</td>
                                <td>{{ $m->raison ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                <h5>Aucun mouvement</h5>
                <p class="text-muted">Aucun mouvement de stock enregistre pour ce produit.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('produits.reapprovisionnement', $produit->id) }}" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Premier reapprovisionnement
                        </a>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</div>
@endsection
