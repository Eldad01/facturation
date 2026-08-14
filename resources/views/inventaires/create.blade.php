@extends('layouts.app')

@section('title', 'Nouvel inventaire')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Nouvel inventaire</h1>
        <p class="page-subtitle">Ouvrir une session de comptage physique</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="modern-card">
            <div class="card-body text-center">
                <i class="bi bi-clipboard-check text-primary" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-4">
                    Cette action va figer le <strong>stock théorique</strong> actuel de
                    <strong>{{ $produitsCount }}</strong> produit(s) et ouvrir une session de comptage.
                    Vous pourrez ensuite saisir les quantités constatées physiquement,
                    en une ou plusieurs fois.
                </p>
                <form action="{{ route('inventaires.store') }}" method="POST">
                    @csrf
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('inventaires.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-fill me-1"></i> Ouvrir la session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
