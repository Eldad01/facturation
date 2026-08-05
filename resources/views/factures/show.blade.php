@extends('layouts.app')

@section('title', 'Details de la facture')

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Facture</h1>
        <p class="page-subtitle">{{ $facture->numero_facture }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('factures.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
        <a href="{{ route('pdf.generate', $facture->id) }}" class="btn btn-primary" target="_blank">
            <i class="bi bi-printer me-1"></i> Imprimer
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="modern-card">
            {{-- Header --}}
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">Facture</span>
                    <h4 class="mb-0 fw-bold">{{ $facture->numero_facture }}</h4>
                </div>
                <span class="badge {{ $facture->type_document=='pro-forma' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }} fs-6">
                    {{ ucfirst($facture->type_document) }}
                </span>
            </div>

            {{-- Body --}}
            <div class="card-body">
                {{-- General Info --}}
                <div class="row mb-4">
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted mb-2"><i class="bi bi-person me-1"></i>Client</h6>
                        <p class="fw-semibold mb-0">{{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '—' }}</p>
                        @if($facture->client?->telephone)
                            <p class="text-muted small mb-0">
                                <i class="bi bi-telephone me-1"></i>{{ $facture->client->telephone }}
                            </p>
                        @endif
                        @if($facture->client?->adresse)
                            <p class="text-muted small mb-0">
                                <i class="bi bi-geo-alt me-1"></i>{{ $facture->client->adresse }}
                            </p>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <div class="mb-2">
                            <span class="badge {{ $facture->status === 'payee' ? 'bg-success' : ($facture->status === 'partiellement_payee' ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $facture->status)) }}
                            </span>
                        </div>
                        <h6 class="text-muted mb-2"><i class="bi bi-calendar me-1"></i>Date</h6>
                        <p class="fw-semibold mb-0">{{ $facture->created_at->format('d/m/Y') }}</p>
                        <p class="text-muted small mb-0">{{ $facture->created_at->format('H:i') }}</p>
                        @if($facture->date_echeance)
                            <p class="text-muted small mb-0">
                                <i class="bi bi-hourglass-split me-1"></i>Echéance: {{ $facture->date_echeance->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                <hr class="my-4">

                {{-- Lines Table --}}
                <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>Details</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produit</th>
                                <th class="text-center" style="width:100px;">Quantite</th>
                                <th class="text-end" style="width:150px;">Prix unitaire</th>
                                <th class="text-end" style="width:150px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facture->lignes as $ligne)
                                <tr>
                                    <td>{{ $ligne->produit->nom ?? '—' }}</td>
                                    <td class="text-center">{{ $ligne->quantite }}</td>
                                    <td class="text-end">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($ligne->total_ligne, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Total --}}
                @php
                    $subtotal = $facture->lignes->sum('total_ligne');
                    $invoiceRemise = $facture->remise ?? 0;
                    $tvaRate = $facture->tva ?? 0;
                    $totalHt = max(0, $subtotal - $invoiceRemise);
                    $tvaAmount = round($totalHt * ($tvaRate/100));
                    $totalTtc = $facture->total;
                @endphp

                <div class="d-flex justify-content-end">
                    <div class="text-end" style="min-width: 220px;">
                        <div class="text-muted small">Total HT</div>
                        <div class="fw-semibold mb-2">{{ number_format($totalHt, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</div>

                        <div class="text-muted small">TVA ({{ $tvaRate }}%)</div>
                        <div class="fw-semibold mb-2">{{ number_format($tvaAmount, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</div>

                        <div class="text-muted mb-1">Total TTC</div>
                        <div class="h3 mb-0 fw-bold text-primary">{{ number_format($totalTtc, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted small">Montant payé</div>
                            <div class="fw-bold">{{ number_format($facture->montant_paye, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted small">Reste à payer</div>
                            <div class="fw-bold">{{ number_format($facture->balance, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted small">Statut</div>
                            <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $facture->status)) }}</div>
                        </div>
                    </div>
                </div>

                @if($facture->paiements->isNotEmpty())
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-currency-dollar me-2"></i>Historique des paiements</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Référence</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facture->paiements as $paiement)
                                        <tr>
                                            <td>{{ $paiement->date_paiement?->format('d/m/Y') ?? $paiement->created_at->format('d/m/Y') }}</td>
                                            <td class="text-end">{{ number_format($paiement->montant, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $paiement->mode)) }}</td>
                                            <td>{{ $paiement->reference ?? '—' }}</td>
                                            <td>{{ $paiement->note ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if(!$facture->isPaid())
                    <div class="modern-card mb-4">
                        <div class="card-body">
                            <h5 class="mb-4"><i class="bi bi-credit-card-2-front me-2"></i>Enregistrer un paiement</h5>
                            <form action="{{ route('factures.paiements.store', $facture->id) }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label for="montant" class="form-label">Montant</label>
                                        <input type="number" step="0.01" name="montant" id="montant"
                                               class="form-control @error('montant') is-invalid @enderror"
                                               value="{{ old('montant', $facture->balance) }}" required>
                                        @error('montant')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="mode" class="form-label">Mode de paiement</label>
                                        <select name="mode" id="mode" class="form-select @error('mode') is-invalid @enderror" required>
                                            <option value="especes">Espèces</option>
                                            <option value="carte">Carte</option>
                                            <option value="virement">Virement</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                        @error('mode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="date_paiement" class="form-label">Date de paiement</label>
                                        <input type="date" name="date_paiement" id="date_paiement"
                                               class="form-control @error('date_paiement') is-invalid @enderror"
                                               value="{{ old('date_paiement', now()->format('Y-m-d')) }}">
                                        @error('date_paiement')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="reference" class="form-label">Référence</label>
                                        <input type="text" name="reference" id="reference"
                                               class="form-control @error('reference') is-invalid @enderror"
                                               value="{{ old('reference') }}">
                                        @error('reference')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="note" class="form-label">Note</label>
                                        <input type="text" name="note" id="note"
                                               class="form-control @error('note') is-invalid @enderror"
                                               value="{{ old('note') }}">
                                        @error('note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check2-circle me-1"></i>Enregistrer le paiement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                @if(auth()->user()->isAdmin() || $facture->user_id === auth()->id())
                    <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
                        <a href="{{ route('factures.edit', $facture->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Modifier
                        </a>

                        <form action="{{ route('factures.duplicate', $facture->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-files me-1"></i> Dupliquer
                            </button>
                        </form>

                        @if($facture->type_document === 'pro-forma' && $facture->modifiable)
                            <form action="{{ route('factures.valider', $facture->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success" onclick="return confirm('Voulez-vous valider ce devis en recu ?');">
                                    <i class="bi bi-check2-circle me-1"></i> Valider
                                </button>
                            </form>
                        @endif

                        @if($facture->status !== 'annule')
                            <form action="{{ route('factures.annuler', $facture->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous annuler cette facture ?');">
                                    <i class="bi bi-x-circle me-1"></i> Annuler
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
