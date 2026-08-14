@extends('layouts.app')

@section('title', 'Inventaires')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Inventaires</h1>
        <p class="page-subtitle d-none d-sm-block">Comptages physiques du stock</p>
    </div>
    <a href="{{ route('inventaires.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">Nouvel inventaire</span>
    </a>
</div>

{{-- Inventaires Table --}}
<div class="modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th class="d-none d-sm-table-cell">Créé par</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventaires as $inventaire)
                        <tr>
                            <td class="fw-semibold">
                                {{ $inventaire->reference }}
                                <span class="d-sm-none d-block small text-muted">{{ $inventaire->userCreation->name ?? '-' }}</span>
                            </td>
                            <td class="d-none d-sm-table-cell">{{ $inventaire->userCreation->name ?? '-' }}</td>
                            <td class="d-none d-md-table-cell text-muted">{{ $inventaire->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($inventaire->isValidee())
                                    <span class="badge bg-success-subtle text-success">Validé</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Brouillon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="action-buttons justify-content-end">
                                    <a href="{{ route('inventaires.show', $inventaire->id) }}"
                                       class="btn btn-sm btn-outline-info"
                                       title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state py-3">
                                    <i class="bi bi-clipboard-check"></i>
                                    <h5>Aucun inventaire</h5>
                                    <p class="text-muted small">Aucune session d'inventaire n'a encore été ouverte.</p>
                                    <a href="{{ route('inventaires.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i>Nouvel inventaire
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($inventaires->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                @foreach ($inventaires->links()->elements as $element)
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $inventaires->currentPage())
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
