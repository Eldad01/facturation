@extends('layouts.app')

@section('title', 'Nouveau devis')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Nouveau devis</h1>
        <p class="page-subtitle">Créer une pro-forma</p>
    </div>
</div>

{{-- Error Alert --}}
@if($errors->has('stock'))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>
            <ul class="mb-0">
                @foreach($errors->get('stock') as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="modern-card">
            <div class="card-body">
                @include('factures._form', ['typeDocument' => 'pro-forma', 'cancelRoute' => route('factures.index', ['tab' => 'devis'])])
            </div>
        </div>
    </div>
</div>
@endsection
