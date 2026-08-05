@extends('layouts.app')

@section('title', 'Modifier le produit')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Modifier le produit</h1>
        <p class="page-subtitle">{{ $produit->nom }}</p>
    </div>
    <a href="{{ route('produits.show', $produit->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="modern-card">
            <div class="card-body">
                <form action="{{ route('produits.update', $produit->id) }}" method="POST" enctype="multipart/form-data" id="produitForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du produit</label>
                        <input type="text"
                               name="nom"
                               id="nom"
                               class="form-control @error('nom') is-invalid @enderror"
                               value="{{ old('nom', $produit->nom) }}"
                               required>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text"
                                       name="sku"
                                       id="sku"
                                       class="form-control @error('sku') is-invalid @enderror"
                                       value="{{ old('sku', $produit->sku) }}">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categorie" class="form-label">Catégorie</label>
                                <input type="text"
                                       name="categorie"
                                       id="categorie"
                                       class="form-control @error('categorie') is-invalid @enderror"
                                       value="{{ old('categorie', $produit->categorie) }}">
                                @error('categorie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="famille" class="form-label">Famille</label>
                                <input type="text"
                                       name="famille"
                                       id="famille"
                                       class="form-control @error('famille') is-invalid @enderror"
                                       value="{{ old('famille', $produit->famille) }}">
                                @error('famille')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unite" class="form-label">Unité</label>
                                <input type="text"
                                       name="unite"
                                       id="unite"
                                       class="form-control @error('unite') is-invalid @enderror"
                                       value="{{ old('unite', $produit->unite) }}">
                                @error('unite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $produit->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo du produit</label>
                        <input type="file"
                               name="photo"
                               id="photo"
                               accept="image/*"
                               class="form-control @error('photo') is-invalid @enderror">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($produit->photo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $produit->photo) }}" alt="Photo produit" class="img-thumbnail" style="max-width: 120px;">
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="prix_achat" class="form-label">Prix d'achat ({{ $app_settings->devise ?? 'FCFA' }})</label>
                        <input type="number"
                               name="prix_achat"
                               id="prix_achat"
                               class="form-control @error('prix_achat') is-invalid @enderror"
                               step="0.01"
                               min="0.01"
                               value="{{ old('prix_achat', $produit->prix_achat) }}"
                               required>
                        @error('prix_achat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="prix_vente" class="form-label">Prix de vente ({{ $app_settings->devise ?? 'FCFA' }})</label>
                        <input type="number"
                               name="prix_vente"
                               id="prix_vente"
                               class="form-control @error('prix_vente') is-invalid @enderror"
                               step="0.01"
                               min="0.01"
                               value="{{ old('prix_vente', $produit->prix_vente) }}"
                               required>
                        @error('prix_vente')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Quantite en stock</label>
                        <input type="number"
                               name="stock"
                               id="stock"
                               class="form-control @error('stock') is-invalid @enderror"
                               min="0"
                               value="{{ old('stock', $produit->stock_actuel) }}">
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="seuil_alerte" class="form-label">Seuil d'alerte</label>
                        <input type="number"
                               name="seuil_alerte"
                               id="seuil_alerte"
                               class="form-control @error('seuil_alerte') is-invalid @enderror"
                               min="0"
                               value="{{ old('seuil_alerte', $produit->seuil_alerte) }}"
                               required>
                        @error('seuil_alerte')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('produits.show', $produit->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Etes-vous sur de vouloir enregistrer ce produit ?</p>
                <div class="alert alert-warning d-flex align-items-center">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <small>Attention : Un produit peut être conservé et son stock ajusté plus tard.</small>
                </div>
                <div class="card mt-3 p-3 bg-light">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted d-block">Nom</small>
                            <strong id="confirmNom">-</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Stock</small>
                            <strong id="confirmStock">-</strong> unites
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Prix d'achat</small>
                            <strong id="confirmPrixAchat">-</strong> {{ $app_settings->devise ?? 'FCFA' }}
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Prix de vente</small>
                            <strong id="confirmPrixVente">-</strong> {{ $app_settings->devise ?? 'FCFA' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="produitForm" class="btn btn-primary">Confirmer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('confirmModal').addEventListener('show.bs.modal', function (event) {
    const nom = document.querySelector('input[name="nom"]').value;
    const prixAchat = document.querySelector('input[name="prix_achat"]').value;
    const prixVente = document.querySelector('input[name="prix_vente"]').value;
    const stock = document.querySelector('input[name="stock"]').value;
    
    document.getElementById('confirmNom').textContent = nom || '-';
    document.getElementById('confirmPrixAchat').textContent = prixAchat ? parseFloat(prixAchat).toLocaleString() : '0';
    document.getElementById('confirmPrixVente').textContent = prixVente ? parseFloat(prixVente).toLocaleString() : '0';
    document.getElementById('confirmStock').textContent = stock || '0';
});
</script>
@endpush
