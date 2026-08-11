@extends('layouts.app')

@section('title', 'Détails fournisseur')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Détails fournisseur</h1>
        <p class="page-subtitle">{{ $fournisseur->nom }}</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('fournisseurs.edit', $fournisseur->id) }}" class="btn btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Modifier
                </a>
            @endif
        @endauth
    </div>
</div>

{{-- Supplier Info Card --}}
<div class="modern-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-shop me-2"></i>Informations</h5>
                <div class="mb-2">
                    <span class="text-muted">Nom:</span>
                    <span class="fw-semibold ms-2">{{ $fournisseur->nom }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Email:</span>
                    <span class="fw-semibold ms-2">{{ $fournisseur->email ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Téléphone:</span>
                    <span class="fw-semibold ms-2">{{ $fournisseur->telephone ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Adresse:</span>
                    <span class="fw-semibold ms-2">{{ $fournisseur->adresse ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Ville:</span>
                    <span class="fw-semibold ms-2">{{ $fournisseur->ville ?? '-' }} {{ $fournisseur->code_postal ?? '' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Pays:</span>
                    <span class="fw-semibold ms-2">{{ $fournisseur->pays ?? '-' }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up me-2"></i>Statistiques</h5>
                <div class="mb-2">
                    <span class="text-muted">Produits fournis:</span>
                    <span class="fw-semibold ms-2 badge bg-primary">{{ $fournisseur->produits->count() }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Commandes:</span>
                    <span class="fw-semibold ms-2 badge bg-success">{{ $fournisseur->commandes->count() }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Statut:</span>
                    <span class="fw-semibold ms-2">
                        @if($fournisseur->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </span>
                </div>
                @if($fournisseur->notes)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block">Notes:</small>
                        <p class="small text-muted">{{ $fournisseur->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Products --}}
<div class="modern-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-boxes me-2"></i>Produits fournis</span>
        @auth
            @if(auth()->user()->isAdmin())
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#linkProductModal">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter produit
                </button>
            @endif
        @endauth
    </div>
    <div class="card-body p-0">
        @if($produits->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-end">Prix achat</th>
                            <th class="text-end">Délai (j.)</th>
                            <th class="text-center">Min.</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produits as $produit)
                            <tr>
                                <td class="fw-semibold">{{ $produit->nom }}</td>
                                <td class="text-end">{{ number_format($produit->pivot->prix_achat, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                <td class="text-end">{{ $produit->pivot->delai_livraison }}</td>
                                <td class="text-center">{{ $produit->pivot->quantite_minimum }}</td>
                                <td class="text-end">
                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <form action="{{ route('fournisseurs.unlink-product', [$fournisseur->id, $produit->id]) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Êtes-vous sûr?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state py-3">
                <p class="text-muted">Aucun produit fourni par ce fournisseur.</p>
            </div>
        @endif
    </div>
</div>

{{-- Recent Orders --}}
<div class="modern-card">
    <div class="card-header">
        <span><i class="bi bi-receipt me-2"></i>Commandes récentes</span>
    </div>
    <div class="card-body p-0">
        @if($commandes->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Montant</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commandes as $cmd)
                            <tr>
                                <td class="fw-semibold">{{ $cmd->numero }}</td>
                                <td>{{ $cmd->date_commande->format('d/m/Y') }}</td>
                                <td>
                                    @if($cmd->statut === 'brouillon')
                                        <span class="badge bg-secondary">Brouillon</span>
                                    @elseif($cmd->statut === 'confirmee')
                                        <span class="badge bg-info">Confirmée</span>
                                    @elseif($cmd->statut === 'reçue_partiellement')
                                        <span class="badge bg-warning">Reçue partiellement</span>
                                    @elseif($cmd->statut === 'reçue')
                                        <span class="badge bg-success">Reçue</span>
                                    @elseif($cmd->statut === 'annulee')
                                        <span class="badge bg-danger">Annulée</span>
                                    @endif
                                </td>
                                <td>{{ number_format($cmd->montant_total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('commandes_achat.show', $cmd->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state py-3">
                <p class="text-muted">Aucune commande pour ce fournisseur.</p>
            </div>
        @endif
    </div>
</div>

{{-- Link Product Modal --}}
@auth
    @if(auth()->user()->isAdmin())
        <div class="modal fade" id="linkProductModal" tabindex="-1" aria-labelledby="linkProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="linkProductModalLabel">Ajouter un produit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('fournisseurs.link-product', $fournisseur->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="produit_id" class="form-label">Produit</label>
                                <select name="produit_id" id="produit_id" class="form-select @error('produit_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner un produit --</option>
                                    @foreach(\App\Models\Produit::orderBy('nom')->get() as $p)
                                        @if(!$fournisseur->produits->contains($p->id))
                                            <option value="{{ $p->id }}">{{ $p->nom }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('produit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="prix_achat" class="form-label">Prix d'achat ({{ $app_settings->devise ?? 'FCFA' }})</label>
                                <input type="number" name="prix_achat" id="prix_achat" class="form-control @error('prix_achat') is-invalid @enderror" required min="0">
                                @error('prix_achat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="delai_livraison" class="form-label">Délai de livraison (jours)</label>
                                <input type="number" name="delai_livraison" id="delai_livraison" class="form-control @error('delai_livraison') is-invalid @enderror" value="5" required min="1">
                                @error('delai_livraison')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="quantite_minimum" class="form-label">Quantité minimale</label>
                                <input type="number" name="quantite_minimum" id="quantite_minimum" class="form-control @error('quantite_minimum') is-invalid @enderror" value="1" required min="1">
                                @error('quantite_minimum')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="reference_fournisseur" class="form-label">Référence fournisseur (optionnel)</label>
                                <input type="text" name="reference_fournisseur" id="reference_fournisseur" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endauth
@endsection
