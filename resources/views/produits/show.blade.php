@extends('layouts.app')

@section('title', 'Details du produit')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Details du produit</h1>
        <p class="page-subtitle">{{ $produit->nom }}</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('produits.edit', $produit->id) }}" class="btn btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Modifier
                </a>
            @endif
        @endauth
    </div>
</div>

{{-- Product Info Card --}}
<div class="modern-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Informations</h5>
                <div class="mb-2">
                    <span class="text-muted">Nom:</span>
                    <span class="fw-semibold ms-2">{{ $produit->nom }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">SKU:</span>
                    <span class="fw-semibold ms-2">{{ $produit->sku ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Catégorie:</span>
                    <span class="fw-semibold ms-2">{{ $produit->categorie ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Famille:</span>
                    <span class="fw-semibold ms-2">{{ $produit->famille ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Unité:</span>
                    <span class="fw-semibold ms-2">{{ $produit->unite ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Prix d'achat:</span>
                    <span class="fw-semibold ms-2">{{ number_format($produit->prix_achat, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Prix de vente:</span>
                    <span class="fw-semibold ms-2">{{ number_format($produit->prix_vente, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up me-2"></i>Stock</h5>
                <div class="mb-2">
                    <span class="text-muted">Stock actuel:</span>
                    <span class="fw-semibold ms-2 {{ $produit->enAlerte() ? 'text-danger' : '' }}">
                        {{ $produit->stock_actuel }}
                        @if($produit->enAlerte())
                            <i class="bi bi-exclamation-triangle text-danger ms-1"></i>
                        @endif
                    </span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Seuil d'alerte:</span>
                    <span class="fw-semibold ms-2">{{ $produit->seuil_alerte }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Date d'ajout:</span>
                    <span class="fw-semibold ms-2">{{ $produit->created_at->format('d/m/Y') }}</span>
                </div>
                @if($produit->photo)
                    <div class="mt-3">
                        <img src="{{ asset('storage/' . $produit->photo) }}" alt="Photo produit" class="img-fluid rounded" style="max-height: 220px; object-fit: contain;">
                    </div>
                @endif
            </div>
        </div>

        @auth
            @if(auth()->user()->isAdmin())
                <div class="mt-3 pt-3 border-top d-flex gap-2">
                    <a href="{{ route('produits.reapprovisionnement', $produit->id) }}" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle me-1"></i> Reapprovisionner
                    </a>
                    <a href="{{ route('produits.ajustement', $produit->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-sliders me-1"></i> Ajuster stock
                    </a>
                </div>
            @endif
        @endauth
    </div>
</div>

<div class="modern-card mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3"><i class="bi bi-card-text me-2"></i>Description</h5>
        <p class="text-muted">{{ $produit->description ?? 'Aucune description disponible.' }}</p>
    </div>
</div>

{{-- Stock Movements --}}
<div class="modern-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Historique des mouvements de stock</span>
        <a href="{{ route('produits.mouvements', $produit->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> Voir tout
        </a>
    </div>
    <div class="card-body p-0">
        @if($produit->mouvementsStock->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-end">Quantite</th>
                            <th>Raison</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produit->mouvementsStock()->orderBy('created_at', 'desc')->limit(10)->get() as $mouvement)
                            <tr>
                                <td class="text-muted">{{ $mouvement->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($mouvement->type === 'entree')
                                        <span class="badge bg-success-subtle text-success">Entree</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Sortie</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ $mouvement->quantite }}</td>
                                <td>{{ $mouvement->raison ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                <h5>Aucun mouvement</h5>
                <p class="text-muted">Aucun mouvement de stock pour ce produit.</p>
            </div>
        @endif
    </div>
</div>
@endsection
