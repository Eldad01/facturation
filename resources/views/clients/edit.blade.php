@extends('layouts.app')

@section('title', 'Modifier le client')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Modifier le client</h1>
        <p class="page-subtitle">Mettre a jour les informations du client</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="modern-card">
            <div class="card-body">
                <form action="{{ route('clients.update', $client->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label d-block">Type de client</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type_client" id="type_particulier" value="particulier"
                                   {{ old('type_client', $client->type_client) == 'particulier' ? 'checked' : '' }}
                                   onchange="document.getElementById('entreprise-fields').classList.add('d-none')">
                            <label class="btn btn-outline-primary" for="type_particulier">
                                <i class="bi bi-person me-1"></i>Particulier
                            </label>

                            <input type="radio" class="btn-check" name="type_client" id="type_entreprise" value="entreprise"
                                   {{ old('type_client', $client->type_client) == 'entreprise' ? 'checked' : '' }}
                                   onchange="document.getElementById('entreprise-fields').classList.remove('d-none')">
                            <label class="btn btn-outline-primary" for="type_entreprise">
                                <i class="bi bi-building me-1"></i>Entreprise
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" 
                               name="nom" 
                               id="nom"
                               value="{{ old('nom', $client->nom) }}" 
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
                               value="{{ old('prenom', $client->prenom) }}" 
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
                               value="{{ old('telephone', $client->telephone) }}" 
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
                               value="{{ old('email', $client->email) }}"
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
                               value="{{ old('adresse', $client->adresse) }}" 
                               class="form-control @error('adresse') is-invalid @enderror">
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="entreprise-fields" class="{{ old('type_client', $client->type_client) == 'entreprise' ? '' : 'd-none' }}">
                        <hr>
                        <h6 class="text-muted mb-3"><i class="bi bi-building me-1"></i>Informations entreprise <span class="text-muted fw-normal">(optionnel)</span></h6>

                        <div class="mb-3">
                            <label for="ifu" class="form-label">IFU</label>
                            <input type="text" name="ifu" id="ifu" value="{{ old('ifu', $client->ifu) }}"
                                   class="form-control @error('ifu') is-invalid @enderror">
                            @error('ifu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="rccm" class="form-label">RCCM</label>
                            <input type="text" name="rccm" id="rccm" value="{{ old('rccm', $client->rccm) }}"
                                   class="form-control @error('rccm') is-invalid @enderror">
                            @error('rccm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="boite_postale" class="form-label">Boîte postale</label>
                            <input type="text" name="boite_postale" id="boite_postale" value="{{ old('boite_postale', $client->boite_postale) }}"
                                   class="form-control @error('boite_postale') is-invalid @enderror">
                            @error('boite_postale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="regime_imposition" class="form-label">Régime d'imposition</label>
                            <input type="text" name="regime_imposition" id="regime_imposition" value="{{ old('regime_imposition', $client->regime_imposition) }}"
                                   class="form-control @error('regime_imposition') is-invalid @enderror">
                            @error('regime_imposition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="contact_nom" class="form-label">Nom du contact référent</label>
                            <input type="text" name="contact_nom" id="contact_nom" value="{{ old('contact_nom', $client->contact_nom) }}"
                                   class="form-control @error('contact_nom') is-invalid @enderror">
                            @error('contact_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Mettre a jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
