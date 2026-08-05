@extends('layouts.app')

@section('title', 'Nouveau client')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Nouveau client</h1>
        <p class="page-subtitle">Ajouter un nouveau client</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="modern-card">
            <div class="card-body">
                <form action="{{ route('clients.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" 
                               name="nom" 
                               id="nom"
                               value="{{ old('nom') }}" 
                               class="form-control @error('nom') is-invalid @enderror" 
                               required
                               pattern="[A-Za-zÀ-ÿ' \-]+"
                               title="Seules les lettres, espaces, apostrophes et traits d'union sont autorises">
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prenom(s)</label>
                        <input type="text" 
                               name="prenom" 
                               id="prenom"
                               value="{{ old('prenom') }}" 
                               class="form-control @error('prenom') is-invalid @enderror" 
                               required
                               pattern="[A-Za-zÀ-ÿ' \-]+"
                               title="Seules les lettres, espaces, apostrophes et traits d'union sont autorises">
                        @error('prenom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Telephone</label>
                        <input type="text" 
                               name="telephone" 
                               id="telephone"
                               value="{{ old('telephone') }}" 
                               class="form-control @error('telephone') is-invalid @enderror" 
                               required
                               pattern="^\+?\d+$"
                               title="Seuls les chiffres et le signe + sont autorises">
                        @error('telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-muted">(optionnel)</span></label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="adresse" class="form-label">Adresse <span class="text-muted">(optionnel)</span></label>
                        <input type="text" 
                               name="adresse" 
                               id="adresse"
                               value="{{ old('adresse') }}" 
                               class="form-control @error('adresse') is-invalid @enderror">
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
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
