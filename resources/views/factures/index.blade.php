@extends('layouts.app')

@section('title', 'Factures')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Factures</h1>
        <p class="page-subtitle d-none d-sm-block">Gestion des recus et devis</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('factures.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i><span class="d-none d-md-inline">Nouveau </span>Recu
        </a>
        <a href="{{ route('factures.create', ['type' => 'pro-forma']) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-plus me-1"></i><span class="d-none d-md-inline">Nouveau </span>Devis
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="row g-2 mb-3">
    <div class="col-12 col-md-3">
        <select id="filter_type" class="form-select form-select-sm">
            <option value="">Tous les types</option>
            <option value="pro-forma">Pro-forma</option>
            <option value="recu">Recu</option>
        </select>
    </div>

    <div class="col-12 col-md-6 position-relative">
        <i class="bi bi-search position-absolute"
           style="top: 50%; left: 10px; transform: translateY(-50%); color:#777;">
        </i>
        <input id="search_input"
               type="text"
               class="form-control form-control-sm ps-5"
               placeholder="Rechercher par numero ou client..."
               value="{{ request('search') }}">
    </div>
</div>

{{-- Table responsive --}}
<div class="table-responsive">
    <table class="table table-bordered table-hover table-sm align-middle shadow-sm mb-0">
        <thead class="table-light">
            <tr>
                <th class="small">Numero</th>
                <th class="small">Client</th>
                <th class="small">Type</th>
                <th class="small">Statut</th>
                <th class="small">Échéance</th>
                <th class="small">Total</th>
                <th class="text-end small">Actions</th>
            </tr>
        </thead>

        <tbody id="factures_table">
            @forelse($factures as $facture)
                <tr data-type="{{ $facture->type_document }}">
                    <td class="fw-bold">{{ $facture->numero_facture }}</td>
                    <td>{{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '—' }}</td>
                    <td>
                        @if($facture->type_document === 'pro-forma')
                            <span class="badge bg-warning text-dark">PF</span>
                        @else
                            <span class="badge bg-success">R</span>
                        @endif
                    </td>
                    <td>
                        @if($facture->status === 'payee')
                            <span class="badge bg-success">Payée</span>
                        @elseif($facture->status === 'partiellement_payee')
                            <span class="badge bg-info text-dark">Partielle</span>
                        @elseif($facture->status === 'recu')
                            <span class="badge bg-success">Reçu</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $facture->status)) }}</span>
                        @endif
                    </td>
                    <td>{{ $facture->date_echeance?->format('d/m/Y') ?? '—' }}</td>
                    <td class="fw-semibold">{{ number_format($facture->total, 0, ',', ' ') }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('factures.show', $facture->id) }}"
                               class="btn btn-sm btn-success"
                               title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('pdf.generate', $facture->id) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>

                            @if(auth()->user()->isAdmin() || $facture->user_id === auth()->id())
                                <form action="{{ route('factures.duplicate', $facture->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Dupliquer">
                                        <i class="bi bi-files"></i>
                                    </button>
                                </form>
                            @endif

                            @if($facture->type_document === 'pro-forma')
                                @if(auth()->user()->isAdmin() || $facture->user_id === auth()->id())
                                    <a href="{{ route('factures.edit', $facture->id) }}"
                                       class="btn btn-sm btn-secondary"
                                       title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                <form action="{{ route('factures.valider', $facture->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Valider ce Pro-forma en Recu ?');">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-sm btn-primary" title="Valider">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>

                                @if(!$facture->isDefinitive())
                                    @if(auth()->user()->isAdmin() || $facture->user_id === auth()->id())
                                        <form action="{{ route('factures.destroy', $facture->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Supprimer cette facture ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-3 text-muted small">
                        Aucune facture trouvee.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
    @if($factures->hasPages())
    <div class="card-footer bg-white">
        {{ $factures->links() }}
    </div>
    @endif

@endsection

@push('scripts')
<script>
const searchInput = document.getElementById('search_input');
const filterType = document.getElementById('filter_type');

function filterRows() {
    const search = searchInput.value.toLowerCase().trim();
    const type = filterType.value;

    document.querySelectorAll('#factures_table tr').forEach(row => {
        const numero = row.querySelector('td:nth-child(1)').innerText.toLowerCase();
        const client = row.querySelector('td:nth-child(2)').innerText.toLowerCase();
        const rowType = row.dataset.type;

        const matchesSearch = numero.includes(search) || client.includes(search);
        const matchesType = type === '' || type === rowType;

        row.style.display = (matchesSearch && matchesType) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterRows);
filterType.addEventListener('change', filterRows);
</script>
@endpush

