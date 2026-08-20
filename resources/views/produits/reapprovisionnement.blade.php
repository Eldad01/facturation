@extends('layouts.app')

@section('title', 'Reapprovisionnement')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Reapprovisionnement</h1>
        <p class="page-subtitle">{{ $produit->nom }}</p>
    </div>
    <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-6">
        <div class="modern-card">
            <div class="card-body">
                <div class="alert alert-light border d-flex justify-content-between mb-4">
                    <span class="text-muted">Stock actuel</span>
                    <span class="fw-bold">{{ $produit->stock }} {{ $produit->unite ?? 'unité(s)' }}</span>
                </div>
                <div class="alert alert-light border d-flex justify-content-between mb-4">
                    <span class="text-muted">Coût moyen pondéré actuel</span>
                    <span class="fw-bold">{{ number_format($produit->prix_achat, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }} / unité</span>
                </div>

                <form action="{{ route('produits.reapprovisionnement', $produit->id) }}" method="POST" id="reappro_form">
                    @csrf

                    <div class="mb-3">
                        <label for="quantite" class="form-label">Quantité à ajouter</label>
                        <input type="number"
                               name="quantite"
                               id="quantite"
                               class="form-control"
                               min="1"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="raison" class="form-label">Motif <span class="text-muted">(optionnel)</span></label>
                        <input type="text"
                               name="raison"
                               id="raison"
                               class="form-control"
                               placeholder="Achat fournisseur, correction stock, retour...">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="prix_achat_unitaire" class="form-label">Coût d'achat unitaire</label>
                            <input type="number"
                                   name="prix_achat_unitaire"
                                   id="prix_achat_unitaire"
                                   class="form-control"
                                   min="0"
                                   required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Coût d'achat total</label>
                            <input type="text"
                                   id="prix_achat_total_display"
                                   class="form-control"
                                   value="0"
                                   readonly>
                        </div>
                    </div>

                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                        <span><i class="bi bi-calculator me-1"></i>Nouveau coût moyen pondéré (CUMP)</span>
                        <span class="fw-bold" id="cump_preview">—</span>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter au stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const stockActuel = {{ (int) $produit->stock }};
    const cumpActuel = {{ (float) $produit->prix_achat }};

    const quantiteInput = document.getElementById('quantite');
    const prixUnitaireInput = document.getElementById('prix_achat_unitaire');
    const totalDisplay = document.getElementById('prix_achat_total_display');
    const cumpPreview = document.getElementById('cump_preview');

    function recalc() {
        const quantite = parseFloat(quantiteInput.value || 0);
        const prixUnitaire = parseFloat(prixUnitaireInput.value || 0);
        const total = quantite * prixUnitaire;

        totalDisplay.value = total.toLocaleString('fr-FR');

        const diviseur = stockActuel + quantite;
        if (diviseur > 0 && quantite > 0) {
            const nouveauCump = ((stockActuel * cumpActuel) + (quantite * prixUnitaire)) / diviseur;
            cumpPreview.textContent = Math.round(nouveauCump).toLocaleString('fr-FR') + ' {{ $app_settings->devise ?? "FCFA" }} / unité';
        } else {
            cumpPreview.textContent = '—';
        }
    }

    quantiteInput.addEventListener('input', recalc);
    prixUnitaireInput.addEventListener('input', recalc);
})();
</script>
@endpush
@endsection
