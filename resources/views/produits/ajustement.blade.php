@extends('layouts.app')

@section('title', 'Ajustement de stock')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Ajustement de stock</h1>
        <p class="page-subtitle">{{ $produit->nom }}</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="modern-card">
            <div class="card-body">
                <form action="{{ route('produits.ajustement', $produit->id) }}" method="POST">
                    @csrf

                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>
                            <strong>Stock actuel :</strong> {{ $produit->stock_actuel }} unites
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nouvelle_quantite" class="form-label">Nouvelle quantite <span class="text-danger">*</span></label>
                        <input type="number"
                               name="nouvelle_quantite"
                               id="nouvelle_quantite"
                               class="form-control"
                               min="0"
                               value="{{ $produit->stock_actuel }}"
                               required>
                        <small class="text-muted">Saisissez la quantite finale souhaitee apres ajustement</small>
                    </div>

                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif de l'ajustement <span class="text-danger">*</span></label>
                        <select name="motif" id="motif" class="form-select" required>
                            <option value="">Selectionner un motif</option>
                            <option value="Perte">Perte</option>
                            <option value="Dommage">Dommage</option>
                            <option value="Inventaire">Inventaire</option>
                            <option value="Erreur de saisie">Erreur de saisie</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>

                    <div class="mb-4" id="autreMotifDiv" style="display: none;">
                        <label for="motif_autre" class="form-label">Preciser le motif</label>
                        <input type="text"
                               name="motif_autre"
                               id="motif_autre"
                               class="form-control"
                               placeholder="Decrivez le motif...">
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i> Ajuster le stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const motifSelect = document.getElementById('motif');
    const autreDiv = document.getElementById('autreMotifDiv');

    motifSelect.addEventListener('change', function() {
        if (this.value === 'Autre') {
            autreDiv.style.display = 'block';
            autreDiv.querySelector('input').required = true;
        } else {
            autreDiv.style.display = 'none';
            autreDiv.querySelector('input').required = false;
        }
    });
});
</script>
@endpush
