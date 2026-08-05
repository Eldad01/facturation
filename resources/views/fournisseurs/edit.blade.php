@extends('layouts.app')

@section('title', 'Modifier fournisseur')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Modifier fournisseur</h1>
        <p class="page-subtitle">{{ $fournisseur->nom }}</p>
    </div>
    <a href="{{ route('fournisseurs.show', $fournisseur->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="modern-card">
            <div class="card-body">
                <form action="{{ route('fournisseurs.update', $fournisseur->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du fournisseur</label>
                        <input type="text"
                               name="nom"
                               id="nom"
                               class="form-control @error('nom') is-invalid @enderror"
                               value="{{ old('nom', $fournisseur->nom) }}"
                               required>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $fournisseur->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="text"
                               name="telephone"
                               id="telephone"
                               class="form-control @error('telephone') is-invalid @enderror"
                               value="{{ old('telephone', $fournisseur->telephone) }}">
                        @error('telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <input type="text"
                               name="adresse"
                               id="adresse"
                               class="form-control @error('adresse') is-invalid @enderror"
                               value="{{ old('adresse', $fournisseur->adresse) }}">
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text"
                                       name="ville"
                                       id="ville"
                                       class="form-control @error('ville') is-invalid @enderror"
                                       value="{{ old('ville', $fournisseur->ville) }}">
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text"
                                       name="code_postal"
                                       id="code_postal"
                                       class="form-control @error('code_postal') is-invalid @enderror"
                                       value="{{ old('code_postal', $fournisseur->code_postal) }}">
                                @error('code_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pays" class="form-label">Pays</label>
                        <input type="text"
                               name="pays"
                               id="pays"
                               class="form-control @error('pays') is-invalid @enderror"
                               value="{{ old('pays', $fournisseur->pays) }}">
                        @error('pays')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="3"
                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $fournisseur->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('fournisseurs.show', $fournisseur->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
