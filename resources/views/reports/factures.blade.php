@extends('layouts.app')

@section('title', 'État factures')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">État factures</h1>
        <p class="page-subtitle d-none d-sm-block">Reçus filtrés par période</p>
    </div>
    <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Retour</span>
    </a>
</div>

{{-- Filters --}}
<form method="GET" class="modern-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Période</label>
                <select name="date_preset" class="form-select" onchange="this.form.submit()">
                    <option value="">Personnalisée</option>
                    <option value="today" {{ request('date_preset') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                    <option value="week" {{ request('date_preset') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                    <option value="month" {{ request('date_preset') == 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="year" {{ request('date_preset') == 'year' ? 'selected' : '' }}>Cette année</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date début</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date fin</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('rapports.factures.pdf', request()->query()) }}" target="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modern-card mb-4">
    <div class="card-body">
        <span class="text-muted">Période :</span> <span class="fw-semibold">{{ $label }}</span>
        &nbsp;&middot;&nbsp;
        <span class="text-muted">Nombre de factures :</span> <span class="fw-semibold">{{ $factures->count() }}</span>
        &nbsp;&middot;&nbsp;
        <span class="text-muted">Total encaissé :</span> <span class="fw-bold text-primary">{{ number_format($total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</span>
    </div>
</div>

<div class="modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="text-end">Total</th>
                        <th class="d-none d-md-table-cell">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                        <tr>
                            <td class="fw-semibold">
                                {{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '' }}
                                <span class="d-block small text-muted fw-normal">{{ $facture->numero_facture }}</span>
                            </td>
                            <td class="d-none d-md-table-cell text-muted">{{ $facture->created_at->format('d/m/Y') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($facture->total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                            <td class="d-none d-md-table-cell">
                                @if($facture->isPaid())
                                    <span class="badge bg-success-subtle text-success">Payée</span>
                                @elseif($facture->isPartiallyPaid())
                                    <span class="badge bg-warning-subtle text-warning">Partielle</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Non payée</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state py-3">
                                    <i class="bi bi-file-text"></i>
                                    <h5>Aucune facture</h5>
                                    <p class="text-muted small">Aucune facture sur cette période.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
