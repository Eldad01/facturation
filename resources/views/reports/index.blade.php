@extends('layouts.app')

@section('title', 'Rapports')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Rapports</h1>
        <p class="page-subtitle d-none d-sm-block">États et rapports imprimables</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-md-6 col-lg-3">
        <a href="{{ route('rapports.clients') }}" class="text-decoration-none">
            <div class="modern-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                    <h5 class="mt-3 mb-1 text-dark">État clients</h5>
                    <p class="text-muted small mb-0">Solde dû et historique</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <a href="{{ route('rapports.factures') }}" class="text-decoration-none">
            <div class="modern-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-file-text text-success" style="font-size: 2.5rem;"></i>
                    <h5 class="mt-3 mb-1 text-dark">État factures</h5>
                    <p class="text-muted small mb-0">Factures sur une période</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <a href="{{ route('rapports.produits') }}" class="text-decoration-none">
            <div class="modern-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-box-seam text-warning" style="font-size: 2.5rem;"></i>
                    <h5 class="mt-3 mb-1 text-dark">État produits</h5>
                    <p class="text-muted small mb-0">Stock, valorisation, top ventes</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <a href="{{ route('rapports.ca_benefices') }}" class="text-decoration-none">
            <div class="modern-card h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-graph-up-arrow text-info" style="font-size: 2.5rem;"></i>
                    <h5 class="mt-3 mb-1 text-dark">CA &amp; bénéfices</h5>
                    <p class="text-muted small mb-0">Chiffre d'affaires et marge</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
