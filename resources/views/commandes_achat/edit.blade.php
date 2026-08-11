@extends('layouts.app')

@section('title', 'Modifier commande d\'achat')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Modifier commande d'achat</h1>
        <p class="page-subtitle">{{ $commande->numero }}</p>
    </div>
    <a href="{{ route('commandes_achat.show', $commande->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        {{-- Order Details --}}
        <div class="modern-card mb-4">
            <div class="card-header">
                <span><i class="bi bi-receipt me-2"></i>Détails de la commande</span>
            </div>
            <div class="card-body">
                <form action="{{ route('commandes_achat.update', $commande->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="date_reception_prevue" class="form-label">Date de réception prévue</label>
                        <input type="date"
                               name="date_reception_prevue"
                               id="date_reception_prevue"
                               class="form-control @error('date_reception_prevue') is-invalid @enderror"
                               value="{{ old('date_reception_prevue', $commande->date_reception_prevue?->format('Y-m-d')) }}">
                        @error('date_reception_prevue')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="3"
                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $commande->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('commandes_achat.show', $commande->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Order Lines --}}
        <div class="modern-card">
            <div class="card-header">
                <span><i class="bi bi-list me-2"></i>Lignes de commande</span>
            </div>
            <div class="card-body p-0">
                @if($lignes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-end">Prix unitaire</th>
                                    <th class="text-center">Quantité</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lignes as $ligne)
                                    <tr>
                                        <td class="fw-semibold">{{ $ligne->produit->nom }}</td>
                                        <td class="text-end">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                        <td class="text-center">{{ $ligne->quantite }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($ligne->montant, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('commandes_achat.remove_ligne', [$commande->id, $ligne->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state py-3">
                        <p class="text-muted">Aucune ligne de commande.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        {{-- Add Line --}}
        <div class="modern-card mb-4">
            <div class="card-header">
                <span><i class="bi bi-plus-circle me-2"></i>Ajouter une ligne</span>
            </div>
            <div class="card-body">
                <form action="{{ route('commandes_achat.add_ligne', $commande->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="produit_id" class="form-label">Produit</label>
                        <select name="produit_id" id="produit_id" class="form-select @error('produit_id') is-invalid @enderror" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}" {{ old('produit_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nom }} ({{ number_format($p->pivot->prix_achat, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }})
                                </option>
                            @endforeach
                        </select>
                        @error('produit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="quantite" class="form-label">Quantité</label>
                        <input type="number"
                               name="quantite"
                               id="quantite"
                               class="form-control @error('quantite') is-invalid @enderror"
                               min="1"
                               value="{{ old('quantite', 1) }}"
                               required>
                        @error('quantite')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="prix_unitaire" class="form-label">Prix unitaire ({{ $app_settings->devise ?? 'FCFA' }})</label>
                        <input type="number"
                               name="prix_unitaire"
                               id="prix_unitaire"
                               class="form-control @error('prix_unitaire') is-invalid @enderror"
                               min="0"
                               value="{{ old('prix_unitaire') }}"
                               required>
                        @error('prix_unitaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>

        {{-- Summary --}}
        <div class="modern-card">
            <div class="card-header">
                <span><i class="bi bi-calculator me-2"></i>Résumé</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted">Total:</span>
                    <span class="fw-semibold ms-2">{{ number_format($commande->montant_total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Fournisseur:</span>
                    <span class="fw-semibold ms-2">{{ $fournisseur->nom }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Lignes:</span>
                    <span class="fw-semibold ms-2">{{ $lignes->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
