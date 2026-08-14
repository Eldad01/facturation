@extends('layouts.app')

@section('title', 'Details du client')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Details du client</h1>
        <p class="page-subtitle">
            {{ $client->nomComplet }}
            @if($client->isEntreprise())
                <span class="badge bg-primary-subtle text-primary ms-1"><i class="bi bi-building me-1"></i>Entreprise</span>
            @else
                <span class="badge bg-secondary-subtle text-secondary ms-1"><i class="bi bi-person me-1"></i>Particulier</span>
            @endif
        </p>
    </div>
    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

{{-- Client Info Card --}}
<div class="modern-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Informations</h5>
                <div class="mb-2">
                    <span class="text-muted">Nom:</span>
                    <span class="fw-semibold ms-2">{{ $client->nom }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Prenom(s):</span>
                    <span class="fw-semibold ms-2">{{ $client->prenom }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Telephone:</span>
                    <span class="fw-semibold ms-2">
                        <a href="tel:{{ $client->telephone }}" class="text-decoration-none">{{ $client->telephone }}</a>
                    </span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Email:</span>
                    <span class="fw-semibold ms-2">
                        @if($client->email)
                            <a href="mailto:{{ $client->email }}" class="text-decoration-none">{{ $client->email }}</a>
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Coordonnees</h5>
                <div class="mb-2">
                    <span class="text-muted">Adresse:</span>
                    <span class="fw-semibold ms-2">{{ $client->adresse ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Date d'ajout:</span>
                    <span class="fw-semibold ms-2">{{ $client->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        @if($client->isEntreprise())
            <div class="mt-3 pt-3 border-top">
                <h5 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Informations entreprise</h5>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="mb-2">
                            <span class="text-muted">IFU:</span>
                            <span class="fw-semibold ms-2">{{ $client->ifu ?? '-' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">RCCM:</span>
                            <span class="fw-semibold ms-2">{{ $client->rccm ?? '-' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Boîte postale:</span>
                            <span class="fw-semibold ms-2">{{ $client->boite_postale ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-2">
                            <span class="text-muted">Régime d'imposition:</span>
                            <span class="fw-semibold ms-2">{{ $client->regime_imposition ?? '-' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Contact référent:</span>
                            <span class="fw-semibold ms-2">{{ $client->contact_nom ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-3 pt-3 border-top d-flex gap-2">
            <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Modifier
            </a>
        </div>
    </div>
</div>

{{-- Invoices History --}}
<div class="modern-card">
    <div class="card-header">
        <i class="bi bi-file-text me-2"></i>Historique des documents
    </div>
    <div class="card-body p-0">
        @if($client->factures->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Type</th>
                            <th class="text-end">Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->factures as $facture)
                            <tr>
                                <td class="fw-semibold">{{ $facture->numero_facture }}</td>
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
                <h5>Aucun document</h5>
                <p class="text-muted">Aucun document trouve pour ce client.</p>
            </div>
        @endif
    </div>
</div>
@endsection
