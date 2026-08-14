@extends('layouts.app')

@section('title', 'État clients')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">État clients</h1>
        <p class="page-subtitle d-none d-sm-block">Solde dû et historique d'achats</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Retour</span>
        </a>
        <a href="{{ route('rapports.clients.pdf') }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i><span class="d-none d-sm-inline">Télécharger PDF</span>
        </a>
    </div>
</div>

<div class="modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th class="text-center d-none d-md-table-cell">Nb factures</th>
                        <th class="text-end d-none d-md-table-cell">Total facturé</th>
                        <th class="text-end">Total payé</th>
                        <th class="text-end">Solde dû</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        @php
                            $totalFacture = $client->total_facture ?? 0;
                            $totalPaye = $client->total_paye ?? 0;
                            $solde = max(0, $totalFacture - $totalPaye);
                        @endphp
                        <tr>
                            <td class="fw-semibold">
                                {{ $client->nom }} {{ $client->prenom }}
                                <span class="d-md-none d-block small text-muted fw-normal">{{ $client->factures_count }} facture(s)</span>
                            </td>
                            <td class="text-center d-none d-md-table-cell">{{ $client->factures_count }}</td>
                            <td class="text-end d-none d-md-table-cell">{{ number_format($totalFacture, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                            <td class="text-end">{{ number_format($totalPaye, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                            <td class="text-end fw-bold {{ $solde > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($solde, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state py-3">
                                    <i class="bi bi-people"></i>
                                    <h5>Aucun client</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($clients->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                @foreach ($clients->links()->elements as $element)
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $clients->currentPage())
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
