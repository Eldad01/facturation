@extends('layouts.app')

@section('title', 'Inventaire ' . $inventaire->reference)

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Inventaire {{ $inventaire->reference }}</h1>
        <p class="page-subtitle">
            @if($inventaire->isValidee())
                <span class="badge bg-success-subtle text-success">Validé</span>
            @else
                <span class="badge bg-warning-subtle text-warning">Brouillon</span>
            @endif
        </p>
    </div>
    <a href="{{ route('inventaires.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

{{-- Info Card --}}
<div class="modern-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="mb-2">
                    <span class="text-muted">Ouvert par:</span>
                    <span class="fw-semibold ms-2">{{ $inventaire->userCreation->name ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Ouvert le:</span>
                    <span class="fw-semibold ms-2">{{ $inventaire->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6">
                @if($inventaire->isValidee())
                    <div class="mb-2">
                        <span class="text-muted">Validé par:</span>
                        <span class="fw-semibold ms-2">{{ $inventaire->userValidation->name ?? '-' }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Validé le:</span>
                        <span class="fw-semibold ms-2">{{ $inventaire->date_validation?->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$inventaire->isValidee())
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Saisissez la quantité réellement comptée pour chaque produit. Vous pouvez enregistrer
        partiellement et continuer plus tard. Seul un administrateur peut valider la session
        (l'écart sera alors appliqué au stock).
    </div>
@endif

{{-- Lignes --}}
<div class="modern-card mb-4">
    <div class="card-body p-0">
        <form action="{{ route('inventaires.update', $inventaire->id) }}" method="POST" id="inventaireForm">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produit</th>
                            <th class="text-center d-none d-sm-table-cell" style="width:120px;">Stock théo.</th>
                            <th class="text-center" style="width:140px;">Stock réel</th>
                            <th class="text-center" style="width:100px;">Écart</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventaire->lignes as $ligne)
                            <tr>
                                <td class="align-middle">
                                    {{ $ligne->produit->nom ?? '—' }}
                                    <span class="d-sm-none d-block small text-muted">Théo. : {{ $ligne->stock_theorique }}</span>
                                </td>
                                <td class="text-center align-middle d-none d-sm-table-cell">{{ $ligne->stock_theorique }}</td>
                                <td class="text-center">
                                    <input type="hidden" name="lignes[{{ $ligne->id }}][ligne_id]" value="{{ $ligne->id }}">
                                    <input type="number"
                                           name="lignes[{{ $ligne->id }}][stock_reel]"
                                           class="form-control form-control-sm text-center"
                                           min="0"
                                           value="{{ $ligne->stock_reel }}"
                                           {{ $inventaire->isValidee() ? 'disabled' : '' }}>
                                </td>
                                <td class="text-center align-middle">
                                    @if($ligne->stock_reel === null)
                                        <span class="text-muted">—</span>
                                    @elseif($ligne->ecart == 0)
                                        <span class="badge bg-success-subtle text-success">0</span>
                                    @elseif($ligne->ecart > 0)
                                        <span class="badge bg-primary-subtle text-primary">+{{ $ligne->ecart }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">{{ $ligne->ecart }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(!$inventaire->isValidee())
                <div class="p-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Enregistrer les comptages
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

@if(!$inventaire->isValidee() && auth()->user()->isAdmin())
    <div class="modern-card border-warning">
        <div class="card-body">
            <h5 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Valider l'inventaire</h5>
            <p class="text-muted small mb-3">
                Cette action est définitive : elle applique les écarts au stock (création de mouvements de stock
                correctifs) pour toutes les lignes comptées, et clôture la session.
            </p>
            <form action="{{ route('inventaires.valider', $inventaire->id) }}" method="POST"
                  onsubmit="return confirm('Valider cet inventaire ? Le stock sera ajusté en fonction des écarts constatés.');">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check2-circle me-1"></i> Valider et ajuster le stock
                </button>
            </form>
        </div>
    </div>
@endif
@endsection
