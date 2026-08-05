@extends('layouts.app')

@section('title', 'Commandes d\'achat')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Commandes d'achat</h1>
        <p class="page-subtitle d-none d-sm-block">Gestion des commandes fournisseurs</p>
    </div>
    <a href="{{ route('commandes_achat.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">Nouvelle commande</span>
    </a>
</div>

{{-- Purchase Orders Table --}}
<div class="modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th class="small">Numéro</th>
                        <th class="small">Fournisseur</th>
                        <th class="text-end small">Montant</th>
                        <th class="text-center small">Statut</th>
                        <th class="text-end small d-none d-md-table-cell">Date</th>
                        <th class="text-end small">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commandes as $commande)
                        <tr>
                            <td class="fw-semibold">{{ $commande->numero }}</td>
                            <td>{{ $commande->fournisseur->nom }}</td>
                            <td class="text-end">{{ number_format($commande->montant_total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                            <td class="text-center">
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
                            </td>
                            <td class="text-end text-muted d-none d-md-table-cell">{{ $commande->date_commande->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('commandes_achat.show', $commande->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state py-3">
                                    <i class="bi bi-receipt"></i>
                                    <h5>Aucune commande</h5>
                                    <p class="text-muted small">Aucune commande d'achat trouvée.</p>
                                    <a href="{{ route('commandes_achat.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i>Nouvelle commande
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($commandes->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                @foreach ($commandes->links()->elements as $element)
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $commandes->currentPage())
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
