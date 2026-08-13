@extends('layouts.app')

@section('title', 'Parametres')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Parametres</h1>
        <p class="page-subtitle">Configuration de l'application</p>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ !request('tab') || request('tab') == 'company' ? 'active' : '' }}" 
                id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab">
            <i class="bi bi-building me-1"></i> Societe
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ request('tab') == 'users' ? 'active' : '' }}" 
                id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
            <i class="bi bi-people me-1"></i> Utilisateurs
        </button>
    </li>
</ul>

{{-- Tab Content --}}
<div class="tab-content" id="settingsTabsContent">
    
    {{-- Company Tab --}}
    <div class="tab-pane fade {{ !request('tab') || request('tab') == 'company' ? 'show active' : '' }}" 
         id="company" role="tabpanel">
        <div class="row g-4">
            {{-- Company Info Card --}}
            <div class="col-12 col-lg-8">
                <div class="modern-card">
                    <div class="card-header bg-primary-subtle">
                        <i class="bi bi-building me-2"></i>Informations de l'entreprise
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="company_name" class="form-label">
                                        <i class="bi bi-bank me-1 text-primary"></i>Nom de la societe
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <input type="text" name="company_name" id="company_name" class="form-control"
                                            value="{{ old('company_name', $settings?->company_name) }}" required>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-1 text-primary"></i>Email
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" id="email" class="form-control"
                                            value="{{ old('email', $settings?->email) }}">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="phone" class="form-label">
                                        <i class="bi bi-telephone me-1 text-primary"></i>Telephone
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" id="phone" class="form-control"
                                            value="{{ old('phone', $settings?->phone) }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">
                                        <i class="bi bi-geo-alt me-1 text-primary"></i>Adresse
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-house"></i></span>
                                        <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $settings?->address) }}</textarea>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="ifu" class="form-label">
                                        <i class="bi bi-hash me-1 text-warning"></i>Numero IFU
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-warning-subtle"><i class="bi bi-hash text-warning"></i></span>
                                        <input type="text" name="ifu" id="ifu" class="form-control"
                                            value="{{ old('ifu', $settings?->ifu) }}">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="rccm" class="form-label">
                                        <i class="bi bi-journal-text me-1 text-warning"></i>Numero RCCM
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-warning-subtle"><i class="bi bi-journal-text text-warning"></i></span>
                                        <input type="text" name="rccm" id="rccm" class="form-control"
                                            value="{{ old('rccm', $settings?->rccm) }}">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="country" class="form-label">
                                        <i class="bi bi-globe me-1 text-info"></i>Pays
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info-subtle"><i class="bi bi-globe text-info"></i></span>
                                        <input type="text" name="country" id="country" class="form-control"
                                            value="{{ old('country', $settings?->country) }}">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="national_motto" class="form-label">
                                        <i class="bi bi-flag me-1 text-info"></i>Devise/Nationalite
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info-subtle"><i class="bi bi-flag text-info"></i></span>
                                        <input type="text" name="national_motto" id="national_motto" class="form-control"
                                            value="{{ old('national_motto', $settings?->national_motto) }}">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="tva_default" class="form-label">
                                        <i class="bi bi-percent me-1 text-info"></i>Taux de TVA par defaut (%)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info-subtle"><i class="bi bi-percent text-info"></i></span>
                                        <input type="number" step="0.01" name="tva_default" id="tva_default" class="form-control @error('tva_default') is-invalid @enderror"
                                            value="{{ old('tva_default', $settings?->tva_default ?? 0) }}">
                                        @error('tva_default')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-lg me-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Logo & Actions Card --}}
            <div class="col-12 col-lg-4">
                <div class="modern-card mb-4">
                    <div class="card-header bg-success-subtle">
                        <i class="bi bi-image me-2"></i>Logo de l'entreprise
                    </div>
                    <div class="card-body text-center">
                        @if($settings?->logo)
                            <div class="mb-3 p-3 bg-light rounded">
                                <img src="{{ asset('logos/'.$settings->logo) }}" alt="Logo" 
                                     class="img-fluid" style="max-height: 120px;">
                            </div>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-check-circle text-success me-1"></i>Logo actuel
                            </p>
                        @else
                            <div class="mb-3 p-4 bg-light rounded">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">Aucun logo uploaded</p>
                            </div>
                        @endif
                        
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="company_name" value="{{ $settings?->company_name ?? '' }}">
                            <div class="mb-3">
                                <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                                <div class="form-text">PNG, JPG, GIF (max 2MB)</div>
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-upload me-1"></i>Changer le logo
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="modern-card border-danger">
                    <div class="card-header bg-danger-subtle text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>Zone danger
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Cette action supprimera definitivement tous les parametres de l'entreprise.
                        </p>
                        <form action="{{ route('settings.reset') }}" method="POST"
                            onsubmit="return confirm('Tout sera supprime. Etes-vous sur de vouloir continuer ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reinitialiser les parametres
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Tab --}}
    <div class="tab-pane fade {{ request('tab') == 'users' ? 'show active' : '' }}" 
         id="users" role="tabpanel">
        <div class="row g-4">
            {{-- Users List --}}
            <div class="col-12 col-lg-8">
                <div class="modern-card">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>Liste des utilisateurs
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td class="fw-semibold">{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->role === 'admin')
                                                    <span class="badge bg-primary">Admin</span>
                                                @else
                                                    <span class="badge bg-secondary">Employe</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->is_active)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="action-buttons justify-content-end">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editUserModal{{ $user->id }}"
                                                            title="Modifier">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    @if($user->id !== auth()->id())
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#resetPasswordModal{{ $user->id }}"
                                                                title="Reinitialiser le mot de passe">
                                                            <i class="bi bi-key"></i>
                                                        </button>
                                                        <a href="{{ route('settings.users.toggle', $user->id) }}" 
                                                           class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}"
                                                           title="{{ $user->is_active ? 'Desactiver' : 'Activer' }}">
                                                            <i class="bi bi-{{ $user->is_active ? 'person-dash' : 'person-check' }}"></i>
                                                        </a>
                                                        <form action="{{ route('settings.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="return confirm('Supprimer cet utilisateur ?')"
                                                                    title="Supprimer">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Edit User Modal --}}
                                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('settings.users.update', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nom</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Nouveau mot de passe (laisser vide pour garder l'actuel)</label>
                                                                <input type="password" name="password" class="form-control">
                                                                <small class="form-text text-muted">8 caractères min., avec majuscule, minuscule et chiffre.</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Role</label>
                                                                <select name="role" class="form-select" required>
                                                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                                    <option value="employe" {{ $user->role === 'employe' ? 'selected' : '' }}>Employe</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Reset Password Modal --}}
                                        <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reinitialiser le mot de passe de {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('settings.users.reset-password', $user->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-info d-flex align-items-center">
                                                                <i class="bi bi-info-circle me-2"></i>
                                                                Entrez le nouveau mot de passe pour {{ $user->name }}.
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Nouveau mot de passe</label>
                                                                <input type="password" name="new_password" class="form-control" required minlength="8">
                                                                <small class="form-text text-muted">8 caractères min., avec majuscule, minuscule et chiffre.</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Confirmer le mot de passe</label>
                                                                <input type="password" name="new_password_confirmation" class="form-control" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-primary">Reinitialiser</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state">
                                                    <i class="bi bi-people"></i>
                                                    <h5>Aucun utilisateur</h5>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add User Form --}}
            <div class="col-12 col-lg-4">
                <div class="modern-card">
                    <div class="card-header">
                        <i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.users.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                                <small class="form-text text-muted">8 caractères min., avec majuscule, minuscule et chiffre.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmer le mot de passe</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="employe">Employe</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus-lg me-1"></i> Ajouter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
