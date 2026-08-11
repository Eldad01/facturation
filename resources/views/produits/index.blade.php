@extends('layouts.app')

@section('title', 'Produits')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Produits</h1>
        <p class="page-subtitle d-none d-sm-block">Gestion des produits et du stock</p>
    </div>
    @auth
        @if(auth()->user()->isAdmin())
            <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">Ajouter</span>
            </a>
        @endif
    @endauth
</div>

{{-- Products Table --}}
<div class="modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th class="small">Nom</th>
                        <th class="text-muted small d-none d-md-table-cell">SKU</th>
                        <th class="text-end small">Prix vente</th>
                        <th class="text-end small d-none d-md-table-cell">Prix achat</th>
                        <th class="text-center small">Stock</th>
                        <th class="text-center small d-none d-lg-table-cell">Seuil</th>
                        <th class="text-end small">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produits as $produit)
                        <tr class="{{ $produit->enAlerte() ? 'table-warning' : '' }}">
                            <td class="fw-semibold">{{ $produit->nom }}</td>
                            <td class="text-muted d-none d-md-table-cell">{{ $produit->sku ?? '-' }}</td>
                            <td class="text-end">{{ number_format($produit->prix_vente, 0, ',', ' ') }}</td>
                            <td class="text-end text-muted d-none d-md-table-cell">{{ number_format($produit->prix_achat, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                <span class="fw-semibold {{ $produit->enAlerte() ? 'text-danger' : '' }}">
                                    {{ $produit->stock_actuel }}
                                </span>
                                @if($produit->enAlerte())
                                    <i class="bi bi-exclamation-triangle text-danger" title="Stock en alerte"></i>
                                @endif
                            </td>
                            <td class="text-center text-muted d-none d-lg-table-cell">{{ $produit->seuil_alerte }}</td>
                            <td class="text-end">
                                <div class="action-buttons justify-content-end">
                                    <a href="{{ route('produits.show', $produit->id) }}"
                                       class="btn btn-sm btn-outline-info"
                                       title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('produits.mouvements', $produit->id) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Mouvements">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('produits.edit', $produit->id) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('produits.reapprovisionnement', $produit->id) }}"
                                               class="btn btn-sm btn-outline-success"
                                               title="Reapprovisionner">
                                                <i class="bi bi-plus-circle"></i>
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state py-3">
                                    <i class="bi bi-box-seam"></i>
                                    <h5>Aucun produit</h5>
                                    <p class="text-muted small">Aucun produit trouve.</p>
                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus-lg me-1"></i>Ajouter
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
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

