@extends('layouts.app')

@section('title', 'Nouvelle commande d\'achat')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Nouvelle commande d'achat</h1>
        <p class="page-subtitle">Créer une nouvelle commande</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="modern-card">
            <div class="card-body">
                <form action="{{ route('commandes_achat.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="fournisseur_id" class="form-label">Fournisseur</label>
                        <select name="fournisseur_id" id="fournisseur_id" class="form-select @error('fournisseur_id') is-invalid @enderror" required>
                            <option value="">-- Sélectionner un fournisseur --</option>
                            @foreach($fournisseurs as $f)
                                <option value="{{ $f->id }}" {{ old('fournisseur_id') == $f->id ? 'selected' : '' }}>
                                    {{ $f->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('fournisseur_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="date_commande" class="form-label">Date de commande</label>
                        <input type="date"
                               name="date_commande"
                               id="date_commande"
                               class="form-control @error('date_commande') is-invalid @enderror"
                               value="{{ old('date_commande', date('Y-m-d')) }}"
                               required>
                        @error('date_commande')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="date_reception_prevue" class="form-label">Date de réception prévue</label>
                        <input type="date"
                               name="date_reception_prevue"
                               id="date_reception_prevue"
                               class="form-control @error('date_reception_prevue') is-invalid @enderror"
                               value="{{ old('date_reception_prevue') }}">
                        @error('date_reception_prevue')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <a href="{{ route('commandes_achat.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
