@extends('layouts.app')

@section('title', 'Réception d\'achat')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Réception d'achat</h1>
        <p class="page-subtitle">{{ $reception->numero }}</p>
    </div>
    <a href="{{ route('commandes_achat.show', $reception->commande_achat_id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

{{-- Reception Info --}}
<div class="modern-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Informations</h5>
                <div class="mb-2">
                    <span class="text-muted">Numéro réception:</span>
                    <span class="fw-semibold ms-2">{{ $reception->numero }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Commande:</span>
                    <span class="fw-semibold ms-2">
                        <a href="{{ route('commandes_achat.show', $commande->id) }}">{{ $commande->numero }}</a>
                    </span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Fournisseur:</span>
                    <span class="fw-semibold ms-2">{{ $commande->fournisseur->nom }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-calendar me-2"></i>Dates</h5>
                <div class="mb-2">
                    <span class="text-muted">Date de réception:</span>
                    <span class="fw-semibold ms-2">{{ $reception->date_reception->format('d/m/Y') }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Créée le:</span>
                    <span class="fw-semibold ms-2">{{ $reception->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Quantité reçue:</span>
                    <span class="fw-semibold ms-2 badge bg-info">{{ $reception->quantite_totale_reçue }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reception Lines --}}
<div class="modern-card mb-4">
    <div class="card-header">
        <span><i class="bi bi-list me-2"></i>Lignes reçues</span>
    </div>
    <div class="card-body p-0">
        @if($lignes->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-end">Prix unitaire</th>
                            <th class="text-center">Quantité reçue</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lignes as $ligne)
                            <tr>
                                <td class="fw-semibold">{{ $ligne->ligneCommande->produit->nom }}</td>
                                <td class="text-end">{{ number_format($ligne->ligneCommande->prix_unitaire, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                <td class="text-center badge bg-success">{{ $ligne->quantite_reçue }}</td>
                                <td class="text-end fw-semibold">{{ number_format($ligne->quantite_reçue * $ligne->ligneCommande->prix_unitaire, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state py-3">
                <p class="text-muted">Aucune ligne reçue.</p>
            </div>
        @endif
    </div>
</div>

{{-- Status --}}
<div class="modern-card">
    <div class="card-header">
        <span><i class="bi bi-info-circle me-2"></i>Commande</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
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
                    <span class="text-muted">Montant total commande:</span>
                    <span class="fw-semibold ms-2">{{ number_format($commande->montant_total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-2">
                    <span class="text-muted">Réceptions:</span>
                    <span class="fw-semibold ms-2 badge bg-primary">{{ $commande->receptions()->count() }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Quantité totale reçue:</span>
                    <span class="fw-semibold ms-2">{{ $commande->quantite_reçue }} / {{ $commande->quantite_total }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if($reception->notes)
    <div class="modern-card mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-2"></i>Notes</h5>
            <p class="text-muted">{{ $reception->notes }}</p>
        </div>
    </div>
@endif
@endsection
