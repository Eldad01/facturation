@extends('layouts.app')

@section('title', 'Clients')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Clients</h1>
        <p class="page-subtitle d-none d-sm-block">Gestion des clients</p>
    </div>
    <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">Ajouter</span>
    </a>
</div>

{{-- Search Form --}}
<div class="modern-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('clients.index') }}" class="row g-3">
            <div class="col-12 col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Rechercher par nom, prenom ou telephone..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i> Rechercher
                    </button>
                    @if(request('search'))
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Clients Table --}}
<div class="modern-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th class="d-none d-sm-table-cell">Telephone</th>
                        <th class="d-none d-lg-table-cell">Email</th>
                        <th class="d-none d-md-table-cell">Adresse</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td class="fw-semibold">
                                {{ $client->nom }} {{ $client->prenom }}
                                <a href="tel:{{ $client->telephone }}" class="text-decoration-none d-sm-none d-block small text-muted">
                                    <i class="bi bi-telephone me-1"></i>{{ $client->telephone }}
                                </a>
                            </td>
                            <td class="d-none d-sm-table-cell">
                                <a href="tel:{{ $client->telephone }}" class="text-decoration-none">
                                    <i class="bi bi-telephone me-1"></i>{{ $client->telephone }}
                                </a>
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $client->email ?? '-' }}</td>
                            <td class="d-none d-md-table-cell">{{ $client->adresse ?? '-' }}</td>
                            <td class="text-end">
                                <div class="action-buttons justify-content-end">
                                    <a href="{{ route('clients.show', $client->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('clients.edit', $client->id) }}" 
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <form action="{{ route('clients.destroy', $client->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Voulez-vous vraiment supprimer ce client ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <h5>Aucun client trouve</h5>
                                    <p class="text-muted">Recherchez par nom, prenom ou numero de telephone.</p>
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
