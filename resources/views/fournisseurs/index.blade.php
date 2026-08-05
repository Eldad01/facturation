@extends('layouts.app')

@section('title', 'Fournisseurs')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Fournisseurs</h1>
        <p class="page-subtitle d-none d-sm-block">Gestion des fournisseurs</p>
    </div>
    @auth
        @if(auth()->user()->isAdmin())
            <a href="{{ route('fournisseurs.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">Ajouter</span>
            </a>
        @endif
    @endauth
</div>

{{-- Suppliers Table --}}
<div class="modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th class="small">Nom</th>
                        <th class="text-muted small d-none d-md-table-cell">Email</th>
                        <th class="text-muted small d-none d-lg-table-cell">Téléphone</th>
                        <th class="text-center small">Produits</th>
                        <th class="text-center small">Commandes</th>
                        <th class="text-end small">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fournisseurs as $fournisseur)
                        <tr>
                            <td class="fw-semibold">{{ $fournisseur->nom }}</td>
                            <td class="text-muted d-none d-md-table-cell">{{ $fournisseur->email ?? '-' }}</td>
                            <td class="text-muted d-none d-lg-table-cell">{{ $fournisseur->telephone ?? '-' }}</td>
                            <td class="text-center">{{ $fournisseur->produits->count() }}</td>
                            <td class="text-center">{{ $fournisseur->commandes->count() }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('fournisseurs.show', $fournisseur->id) }}"
                                       class="btn btn-sm btn-outline-info"
                                       title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('fournisseurs.edit', $fournisseur->id) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state py-3">
                                    <i class="bi bi-shop"></i>
                                    <h5>Aucun fournisseur</h5>
                                    <p class="text-muted small">Aucun fournisseur trouvé.</p>
                                    @auth
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('fournisseurs.create') }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus-lg me-1"></i>Ajouter
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($fournisseurs->hasPages())
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                @foreach ($fournisseurs->links()->elements as $element)
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $fournisseurs->currentPage())
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
