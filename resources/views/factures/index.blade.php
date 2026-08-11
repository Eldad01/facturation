@extends('layouts.app')

@section('title', 'Factures')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Factures</h1>
        <p class="page-subtitle d-none d-sm-block">Devis, factures en attente et reçus payés</p>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="facturesTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'devis' ? 'active' : '' }}"
                id="devis-tab" data-bs-toggle="tab" data-bs-target="#devis" type="button" role="tab">
            <i class="bi bi-file-earmark-text me-1"></i> Devis
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'attente' ? 'active' : '' }}"
                id="attente-tab" data-bs-toggle="tab" data-bs-target="#attente" type="button" role="tab">
            <i class="bi bi-hourglass-split me-1"></i> En attente
            @if($enAttente->total() > 0)
                <span class="badge bg-danger ms-1">{{ $enAttente->total() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'recus' ? 'active' : '' }}"
                id="recus-tab" data-bs-toggle="tab" data-bs-target="#recus" type="button" role="tab">
            <i class="bi bi-check2-circle me-1"></i> Reçus
        </button>
    </li>
</ul>

<div class="tab-content" id="facturesTabsContent">

    {{-- ===================== Onglet Devis ===================== --}}
    <div class="tab-pane fade {{ $tab == 'devis' ? 'show active' : '' }}" id="devis" role="tabpanel">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('devis.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-plus me-1"></i> Nouveau devis
            </a>
        </div>

        <div class="modern-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('factures.index') }}" class="row g-2">
                    <input type="hidden" name="tab" value="devis">
                    <div class="col-12 col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Rechercher par numero ou client..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search me-1"></i> Rechercher
                            </button>
                            @if(request('search'))
                                <a href="{{ route('factures.index', ['tab' => 'devis']) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="small">Numero</th>
                                <th class="small">Client</th>
                                <th class="small">Échéance</th>
                                <th class="text-end small">Total</th>
                                <th class="text-end small">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($devis as $facture)
                                <tr>
                                    <td class="fw-semibold">{{ $facture->numero_facture }}</td>
                                    <td>{{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '—' }}</td>
                                    <td>{{ $facture->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($facture->total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                    <td class="text-end">
                                        <div class="action-buttons justify-content-end">
                                            <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('pdf.generate', $facture->id) }}" class="btn btn-sm btn-outline-secondary" title="PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            @if(auth()->user()->isAdmin() || $facture->user_id === auth()->id())
                                                <a href="{{ route('factures.edit', $facture->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('factures.valider', $facture->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Valider ce devis en facture ?');">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Valider">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('factures.destroy', $facture->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Supprimer ce devis ?');">
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
                                        <div class="empty-state py-3">
                                            <i class="bi bi-file-earmark-text"></i>
                                            <h5>Aucun devis</h5>
                                            <p class="text-muted small">Aucun devis trouvé.</p>
                                            <a href="{{ route('devis.create') }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus-lg me-1"></i>Nouveau devis
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($devis->hasPages())
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                        @foreach ($devis->appends(['tab' => 'devis'])->links()->elements as $element)
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $devis->currentPage())
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
    </div>

    {{-- ===================== Onglet En attente ===================== --}}
    <div class="tab-pane fade {{ $tab == 'attente' ? 'show active' : '' }}" id="attente" role="tabpanel">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('factures.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Nouvelle facture
            </a>
        </div>

        <div class="modern-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('factures.index') }}" class="row g-2">
                    <input type="hidden" name="tab" value="attente">
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Rechercher par numero ou client..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <select name="etat" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous les états</option>
                            <option value="non_payee" {{ request('etat') === 'non_payee' ? 'selected' : '' }}>Non payées</option>
                            <option value="partielle" {{ request('etat') === 'partielle' ? 'selected' : '' }}>Partiellement payées</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search me-1"></i> Rechercher
                            </button>
                            @if(request('search') || request('etat'))
                                <a href="{{ route('factures.index', ['tab' => 'attente']) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="small">Numero</th>
                                <th class="small">Client</th>
                                <th class="small">Statut</th>
                                <th class="small">Échéance</th>
                                <th class="text-end small">Total</th>
                                <th class="text-end small">Reste à payer</th>
                                <th class="text-end small">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enAttente as $facture)
                                @php $enRetard = $facture->isOverdue(); @endphp
                                <tr class="{{ $enRetard ? 'table-danger' : '' }}">
                                    <td class="fw-semibold">{{ $facture->numero_facture }}</td>
                                    <td>{{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '—' }}</td>
                                    <td>
                                        @if($facture->status === 'partiellement_payee')
                                            <span class="badge bg-warning text-dark">Partiellement payée</span>
                                        @elseif($facture->status === 'annule')
                                            <span class="badge bg-secondary">Annulée</span>
                                        @else
                                            <span class="badge bg-secondary">Non payée</span>
                                        @endif
                                        @if($enRetard)
                                            <span class="badge bg-danger">En retard</span>
                                        @endif
                                    </td>
                                    <td>{{ $facture->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($facture->total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($facture->balance, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                    <td class="text-end">
                                        <div class="action-buttons justify-content-end">
                                            <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-sm btn-outline-success" title="Voir / Encaisser">
                                                <i class="bi bi-cash-coin"></i>
                                            </a>
                                            <a href="{{ route('pdf.generate', $facture->id) }}" class="btn btn-sm btn-outline-secondary" title="PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state py-3">
                                            <i class="bi bi-hourglass-split"></i>
                                            <h5>Rien en attente</h5>
                                            <p class="text-muted small">Aucune facture en attente de paiement.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($enAttente->hasPages())
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                        @foreach ($enAttente->appends(['tab' => 'attente'])->links()->elements as $element)
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $enAttente->currentPage())
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
    </div>

    {{-- ===================== Onglet Reçus ===================== --}}
    <div class="tab-pane fade {{ $tab == 'recus' ? 'show active' : '' }}" id="recus" role="tabpanel">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('factures.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Nouvelle facture
            </a>
        </div>

        <div class="modern-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('factures.index') }}" class="row g-2">
                    <input type="hidden" name="tab" value="recus">
                    <div class="col-12 col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Rechercher par numero ou client..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search me-1"></i> Rechercher
                            </button>
                            @if(request('search'))
                                <a href="{{ route('factures.index', ['tab' => 'recus']) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modern-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="small">Numero</th>
                                <th class="small">Client</th>
                                <th class="small">Statut</th>
                                <th class="small">Date</th>
                                <th class="text-end small">Total</th>
                                <th class="text-end small">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recus as $facture)
                                <tr>
                                    <td class="fw-semibold">{{ $facture->numero_facture }}</td>
                                    <td>{{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '—' }}</td>
                                    <td><span class="badge bg-success">Payée</span></td>
                                    <td>{{ $facture->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($facture->total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                    <td class="text-end">
                                        <div class="action-buttons justify-content-end">
                                            <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('pdf.generate', $facture->id) }}" class="btn btn-sm btn-outline-secondary" title="PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state py-3">
                                            <i class="bi bi-file-text"></i>
                                            <h5>Aucune facture payée</h5>
                                            <p class="text-muted small">Les factures intégralement réglées apparaîtront ici.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recus->hasPages())
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-center mb-0" style="flex-wrap: wrap;">
                        @foreach ($recus->appends(['tab' => 'recus'])->links()->elements as $element)
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $recus->currentPage())
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
    </div>

</div>
@endsection
