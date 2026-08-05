@extends('layouts.app')

@section('title', 'Créer réception d\'achat')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Créer réception d'achat</h1>
        <p class="page-subtitle">{{ $commande->numero }}</p>
    </div>
    <a href="{{ route('commandes_achat.show', $commande->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="modern-card">
            <div class="card-body">
                <form action="{{ route('receptions_achat.store', $commande->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="date_reception" class="form-label">Date de réception</label>
                        <input type="date"
                               name="date_reception"
                               id="date_reception"
                               class="form-control @error('date_reception') is-invalid @enderror"
                               value="{{ old('date_reception', date('Y-m-d')) }}"
                               required>
                        @error('date_reception')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Lines Reception --}}
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Produits à recevoir</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-end">Commandé</th>
                                        <th class="text-end">Reçu</th>
                                        <th class="text-end">À recevoir</th>
                                        <th class="text-end">Quantité reçue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lignes as $index => $ligne)
                                        <tr>
                                            <td class="fw-semibold">{{ $ligne->produit->nom }}</td>
                                            <td class="text-end">{{ $ligne->quantite }}</td>
                                            <td class="text-end">{{ $ligne->quantite_reçue }}</td>
                                            <td class="text-end fw-semibold">{{ $ligne->quantite - $ligne->quantite_reçue }}</td>
                                            <td class="text-end">
                                                <input type="hidden" name="lignes[{{ $index }}][ligne_id]" value="{{ $ligne->id }}">
                                                <input type="number"
                                                       name="lignes[{{ $index }}][quantite_reçue]"
                                                       class="form-control @error("lignes.{$index}.quantite_reçue") is-invalid @enderror"
                                                       min="1"
                                                       max="{{ $ligne->quantite - $ligne->quantite_reçue }}"
                                                       value="{{ old("lignes.{$index}.quantite_reçue", $ligne->quantite - $ligne->quantite_reçue) }}"
                                                       required>
                                                @error("lignes.{$index}.quantite_reçue")
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="3"
                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('commandes_achat.show', $commande->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Créer réception
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
