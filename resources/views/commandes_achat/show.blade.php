@extends('layouts.app')

@section('title', 'Commande d\'achat')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Commande d'achat</h1>
        <p class="page-subtitle">{{ $commande->numero }}</p>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('commandes_achat.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
        @if($commande->isEditable())
            <a href="{{ route('commandes_achat.edit', $commande->id) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil me-1"></i> Modifier
            </a>
        @endif
    </div>
</div>

{{-- Order Info --}}
<div class="modern-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Informations</h5>
                <div class="mb-2">
                    <span class="text-muted">Numéro:</span>
                    <span class="fw-semibold ms-2">{{ $commande->numero }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Fournisseur:</span>
                    <span class="fw-semibold ms-2">
                        <a href="{{ route('fournisseurs.show', $fournisseur->id) }}">{{ $fournisseur->nom }}</a>
                    </span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Date commande:</span>
                    <span class="fw-semibold ms-2">{{ $commande->date_commande->format('d/m/Y') }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Date réception prévue:</span>
                    <span class="fw-semibold ms-2">{{ $commande->date_reception_prevue?->format('d/m/Y') ?? '-' }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Statut</h5>
                <div class="mb-2">
                    <span class="text-muted">Statut:</span>
                    <span class="fw-semibold ms-2">
                        @if($commande->statut === 'brouillon')
                            <span class="badge bg-secondary">Brouillon</span>
                        @elseif($commande->statut === 'confirmee')
                            <span class="badge bg-info">Confirmée</span>
                        @elseif($commande->statut === 'reçue_partiellement')
                            <span class="badge bg-warning">Reçue partiellement</span>
                        @elseif($commande->statut === 'reçue')
                            <span class="badge bg-success">Reçue</span>
                        @elseif($commande->statut === 'annulee')
                            <span class="badge bg-danger">Annulée</span>
                        @endif
                    </span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Montant total:</span>
                    <span class="fw-semibold ms-2">{{ number_format($commande->montant_total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</span>
                </div>
                <div class="mt-3 pt-3 border-top">
                    @if($commande->canConfirm())
                        <form action="{{ route('commandes_achat.confirm', $commande->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg me-1"></i> Confirmer commande
                            </button>
                        </form>
                    @endif
                    @if($commande->canCancel())
                        <form action="{{ route('commandes_achat.cancel', $commande->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">
                                <i class="bi bi-trash me-1"></i> Annuler
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Lines --}}
<div class="modern-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list me-2"></i>Lignes de commande</span>
        @if($commande->isEditable())
            <a href="{{ route('commandes_achat.edit', $commande->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i> Ajouter ligne
            </a>
        @endif
    </div>
    <div class="card-body p-0">
        @if($lignes->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-end">Prix unitaire</th>
                            <th class="text-center">Commandé</th>
                            <th class="text-center">Reçu</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lignes as $ligne)
                            <tr>
                                <td class="fw-semibold">{{ $ligne->produit->nom }}</td>
                                <td class="text-end">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                <td class="text-center">{{ $ligne->quantite }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $ligne->isFullyReceived() ? 'bg-success' : ($ligne->quantite_reçue > 0 ? 'bg-warning' : 'bg-secondary') }}">
                                        {{ $ligne->quantite_reçue }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($ligne->montant, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
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

{{-- Receptions --}}
<div class="modern-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2"></i>Réceptions</span>
        @if(in_array($commande->statut, ['confirmee', 'reçue_partiellement']))
            <a href="{{ route('receptions_achat.create', $commande->id) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-plus-lg me-1"></i> Créer réception
            </a>
        @endif
    </div>
    <div class="card-body p-0">
        @if($receptions->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Date</th>
                            <th class="text-center">Quantité reçue</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receptions as $reception)
                            <tr>
                                <td class="fw-semibold">{{ $reception->numero }}</td>
                                <td>{{ $reception->date_reception->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $reception->quantite_totale_reçue }}</td>
                                <td class="text-end">
                                    <a href="{{ route('receptions_achat.show', $reception->id) }}" class="btn btn-sm btn-outline-info">
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
                <p class="text-muted">Aucune réception pour cette commande.</p>
            </div>
        @endif
    </div>
</div>

@if($commande->notes)
    <div class="modern-card mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-2"></i>Notes</h5>
            <p class="text-muted">{{ $commande->notes }}</p>
        </div>
    </div>
@endif
@endsection
