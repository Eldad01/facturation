@extends('layouts.app')

@section('title', "Chiffre d'affaires & bénéfices")

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Chiffre d'affaires &amp; bénéfices</h1>
        <p class="page-subtitle d-none d-sm-block">Rentabilité sur une période</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Retour</span>
        </a>
        <a href="{{ route('rapports.ca_benefices.pdf', request()->query()) }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i><span class="d-none d-sm-inline">Télécharger PDF</span>
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="modern-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Période</label>
                <select name="date_preset" class="form-select" onchange="this.form.submit()">
                    <option value="">Personnalisée</option>
                    <option value="today" {{ request('date_preset') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                    <option value="week" {{ request('date_preset') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                    <option value="month" {{ request('date_preset') == 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="year" {{ request('date_preset') == 'year' ? 'selected' : '' }}>Cette année</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date début</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Date fin</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Filtrer
                </button>
            </div>
        </div>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-value">{{ $nbFactures }}</div>
            <div class="stat-label">Factures ({{ $label }})</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card success">
            <div class="stat-value">{{ number_format($chiffreAffaires, 0, ',', ' ') }}</div>
            <div class="stat-label">Chiffre d'affaires ({{ $app_settings->devise ?? 'FCFA' }})</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card {{ $benefice >= 0 ? 'success' : 'danger' }}">
            <div class="stat-value">{{ number_format($benefice, 0, ',', ' ') }}</div>
            <div class="stat-label">Bénéfice estimé ({{ $app_settings->devise ?? 'FCFA' }})</div>
        </div>
    </div>
</div>

<div class="alert alert-warning">
    <i class="bi bi-info-circle me-1"></i>
    Le bénéfice est estimé à partir du <strong>coût moyen d'achat actuel (CUMP)</strong> de chaque produit,
    et non du coût réel au moment de chaque vente (l'application ne conserve pas d'historique du coût par vente).
    Ce chiffre est donc une estimation.
</div>
@endsection
