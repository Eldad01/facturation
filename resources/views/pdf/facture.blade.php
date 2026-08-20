<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $titre }} - {{ $facture->numero_facture }}</title>

    <style>
        @php
            $devise = $app_settings->devise ?? 'FCFA';
            $accent = '#0d6efd';
        @endphp

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 28px 34px;
            color: #212529;
        }

        .accent-bar {
            height: 5px;
            background: {{ $accent }};
            margin: -28px -34px 22px -34px;
        }

        /* ---------- En-tête (entreprise, centrée) ---------- */
        .doc-header {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .doc-header .logo {
            max-height: 75px;
            max-width: 160px;
            margin-bottom: 8px;
        }

        .doc-header .name {
            font-size: 24px;
            font-weight: bold;
            color: {{ $accent }};
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .doc-header .meta {
            font-size: 10px;
            color: #495057;
            line-height: 1.6;
        }

        /* ---------- Bandeau titre du document ---------- */
        .doc-title-bar {
            text-align: center;
            background: {{ $accent }};
            color: #fff;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 9px 10px;
            border-radius: 3px;
            margin: 14px 0 8px;
        }

        .doc-sub-meta {
            text-align: center;
            font-size: 10px;
            color: #495057;
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-block;
            margin-left: 8px;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #fff;
        }

        .status-payee { background: #198754; }
        .status-partielle { background: #fd7e14; }
        .status-impayee { background: #6c757d; }
        .status-retard { background: #dc3545; }
        .status-annule { background: #6c757d; }

        /* ---------- Bloc client ---------- */
        .client-box {
            width: 55%;
            border: 1px solid #dee2e6;
            border-left: 3px solid {{ $accent }};
            border-radius: 2px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        .client-box .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .client-box .name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .client-box .meta {
            font-size: 10px;
            color: #495057;
            line-height: 1.5;
        }

        /* ---------- Objet ---------- */
        .objet-line {
            font-size: 10.5px;
            color: #212529;
            margin: 0 0 16px;
        }

        .objet-line .label {
            font-weight: bold;
            color: #495057;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            margin-right: 6px;
        }

        /* ---------- Table des lignes ---------- */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        table.items th {
            background: {{ $accent }};
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid {{ $accent }};
        }

        table.items td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            font-size: 10.5px;
        }

        table.items tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }

        /* ---------- Totaux ---------- */
        .totals-wrap {
            width: 100%;
            display: table;
            margin-top: 6px;
        }

        .totals-left {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }

        .totals-right {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        table.totals {
            width: 100%;
            border-collapse: collapse;
        }

        table.totals td {
            padding: 5px 10px;
            font-size: 10.5px;
            border: none;
        }

        table.totals td.label {
            color: #495057;
        }

        table.totals td.value {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
        }

        table.totals tr.ttc td {
            border-top: 1.5px solid #212529;
            padding-top: 8px;
            font-size: 13px;
        }

        table.totals tr.balance td {
            background: #fff3cd;
            font-weight: bold;
            color: #997404;
        }

        table.totals tr.paid td {
            background: #d1e7dd;
            color: #0f5132;
            font-weight: bold;
        }

        /* ---------- Paiements ---------- */
        .payments-title {
            font-size: 11px;
            font-weight: bold;
            margin: 22px 0 6px;
            color: #212529;
        }

        table.payments {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.payments th {
            background: #f1f3f5;
            font-size: 9px;
            text-transform: uppercase;
            padding: 5px 8px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        table.payments td {
            padding: 5px 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
        }

        /* ---------- Signatures ---------- */
        .signatures {
            width: 100%;
            display: table;
            margin-top: 40px;
        }

        .signatures > div {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .signatures .sign-line {
            margin: 40px 30px 4px;
            border-top: 1px solid #adb5bd;
        }

        .signatures .sign-label {
            font-size: 9px;
            color: #6c757d;
        }

        /* ---------- Pied de page ---------- */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }

        .footer .thanks {
            font-size: 10.5px;
            font-style: italic;
            color: #495057;
            margin-bottom: 4px;
        }

        .footer .legal {
            font-size: 8.5px;
            color: #adb5bd;
        }
    </style>
</head>

<body>

<div class="accent-bar"></div>

{{-- ================= EN-TÊTE (entreprise, centrée) ================= --}}
<div class="doc-header">
    @if($settings?->logo)
        <img class="logo" src="{{ public_path('logos/'.$settings->logo) }}">
    @endif
    <div class="name">{{ $settings?->company_name ?? 'Votre entreprise' }}</div>
    <div class="meta">
        @if($settings?->address){{ $settings->address }}<br>@endif
        @if($settings?->boite_postale)BP : {{ $settings->boite_postale }}&nbsp;&nbsp;@endif
        @if($settings?->phone)Tél : {{ $settings->phone }}<br>@endif
        @if($settings?->email){{ $settings->email }}<br>@endif
        @if($settings?->ifu)IFU : {{ $settings->ifu }}&nbsp;&nbsp;@endif
        @if($settings?->rccm)RCCM : {{ $settings->rccm }}<br>@endif
        @if($settings?->regime_imposition)Régime : {{ $settings->regime_imposition }}<br>@endif
        @if($settings?->contact_nom)Contact : {{ $settings->contact_nom }}@if($settings?->contact_telephone) ({{ $settings->contact_telephone }})@endif @endif
    </div>
</div>

{{-- ================= TITRE DU DOCUMENT ================= --}}
<div class="doc-title-bar">{{ $titre }} N° {{ $facture->numero_facture }}</div>

<div class="doc-sub-meta">
    Date : {{ $facture->created_at->format('d/m/Y') }}
    @if($facture->type_document === 'recu' && $facture->date_echeance)
        &nbsp;&nbsp;|&nbsp;&nbsp;Échéance : {{ $facture->date_echeance->format('d/m/Y') }}
    @endif
    @if($facture->type_document === 'recu')
        @php
            $statusClass = match($facture->etat) {
                'annulee' => 'status-annule',
                'payee' => 'status-payee',
                'partiellement_payee' => 'status-partielle',
                default => 'status-impayee',
            };
        @endphp
        <span class="status-badge {{ $statusClass }}">{{ $facture->etat_label }}</span>
        @if($facture->isOverdue())
            <span class="status-badge status-retard">En retard</span>
        @endif
    @endif
</div>

{{-- ================= CLIENT ================= --}}
<div class="client-box">
    <div class="label">Doit :</div>
    <div class="name">{{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '' }}</div>
    <div class="meta">
        @if($facture->client?->telephone){{ $facture->client->telephone }}<br>@endif
        @if($facture->client?->email){{ $facture->client->email }}<br>@endif
        @if($facture->client?->adresse){{ $facture->client->adresse }}<br>@endif
        @if($facture->client?->isEntreprise())
            @if($facture->client->boite_postale)BP : {{ $facture->client->boite_postale }}<br>@endif
            @if($facture->client->ifu)IFU : {{ $facture->client->ifu }}&nbsp;&nbsp;@endif
            @if($facture->client->rccm)RCCM : {{ $facture->client->rccm }}<br>@endif
            @if($facture->client->regime_imposition)Régime : {{ $facture->client->regime_imposition }}<br>@endif
            @if($facture->client->contact_nom)Contact : {{ $facture->client->contact_nom }}@endif
        @endif
    </div>
</div>

{{-- ================= OBJET ================= --}}
@if($facture->objet)
    <div class="objet-line"><span class="label">Objet :</span>{{ $facture->objet }}</div>
@endif

{{-- ================= LIGNES ================= --}}
@php
    $hasRemiseLigne = $facture->lignes->sum('remise') > 0;
@endphp

<table class="items">
    <thead>
        <tr>
            <th style="width:5%;">#</th>
            <th>Désignation</th>
            <th class="text-center" style="width:10%;">Qté</th>
            <th class="text-right" style="width:16%;">PU ({{ $devise }})</th>
            @if($hasRemiseLigne)
                <th class="text-right" style="width:13%;">Remise</th>
            @endif
            <th class="text-right" style="width:16%;">Total ({{ $devise }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($facture->lignes as $index => $ligne)
            <tr>
                <td class="text-center text-muted">{{ $index + 1 }}</td>
                <td>{{ $ligne->produit->nom ?? 'Produit supprimé' }}</td>
                <td class="text-center">{{ $ligne->quantite }}</td>
                <td class="text-right">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                @if($hasRemiseLigne)
                    <td class="text-right">{{ $ligne->remise > 0 ? number_format($ligne->remise, 0, ',', ' ') : '—' }}</td>
                @endif
                <td class="text-right">{{ number_format($ligne->total_ligne, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= TOTAUX ================= --}}
@php
    $subtotal = $facture->lignes->sum('total_ligne');
    $invoiceRemise = $facture->remise ?? 0;
    $tvaRate = $facture->tva ?? 0;
    $totalHt = max(0, $subtotal - $invoiceRemise);
    $tvaAmount = round($totalHt * ($tvaRate / 100));
    $totalTtc = $facture->total;
@endphp

<div class="totals-wrap">
    <div class="totals-left">
        @if($facture->type_document === 'recu' && $settings?->banque_numero_compte)
            <div class="meta" style="font-size: 10px;">
                <strong>Coordonnées bancaires</strong><br>
                @if($settings->banque_nom){{ $settings->banque_nom }}<br>@endif
                N° compte : {{ $settings->banque_numero_compte }}
                @if($settings->banque_autres_comptes)
                    <br>{{ $settings->banque_autres_comptes }}
                @endif
            </div>
        @endif
    </div>
    <div class="totals-right">
        <table class="totals">
            <tr>
                <td class="label">Sous-total HT</td>
                <td class="value">{{ number_format($subtotal, 0, ',', ' ') }} {{ $devise }}</td>
            </tr>
            @if($invoiceRemise > 0)
                <tr>
                    <td class="label">Remise</td>
                    <td class="value">- {{ number_format($invoiceRemise, 0, ',', ' ') }} {{ $devise }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">TVA ({{ $tvaRate }}%)</td>
                <td class="value">{{ number_format($tvaAmount, 0, ',', ' ') }} {{ $devise }}</td>
            </tr>
            <tr class="ttc">
                <td class="label">Total TTC</td>
                <td class="value">{{ number_format($totalTtc, 0, ',', ' ') }} {{ $devise }}</td>
            </tr>
            @if($facture->type_document === 'recu')
                <tr>
                    <td class="label">Montant payé</td>
                    <td class="value">{{ number_format($facture->montant_paye, 0, ',', ' ') }} {{ $devise }}</td>
                </tr>
                @if($facture->balance > 0)
                    <tr class="balance">
                        <td class="label">Solde restant</td>
                        <td class="value">{{ number_format($facture->balance, 0, ',', ' ') }} {{ $devise }}</td>
                    </tr>
                @else
                    <tr class="paid">
                        <td class="label" colspan="2" style="text-align:center;">Facture soldée</td>
                    </tr>
                @endif
            @endif
        </table>
    </div>
</div>

{{-- ================= HISTORIQUE PAIEMENTS ================= --}}
@if($facture->paiements->isNotEmpty())
    <div class="payments-title">Historique des paiements</div>
    <table class="payments">
        <thead>
            <tr>
                <th>Date</th>
                <th>Mode</th>
                <th>Référence</th>
                <th class="text-right">Montant ({{ $devise }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->paiements as $paiement)
                <tr>
                    <td>{{ $paiement->date_paiement?->format('d/m/Y') ?? $paiement->created_at->format('d/m/Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $paiement->mode)) }}</td>
                    <td>{{ $paiement->reference ?? '—' }}</td>
                    <td class="text-right">{{ number_format($paiement->montant, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- ================= SIGNATURES ================= --}}
<div class="signatures">
    <div>
        <div class="sign-line"></div>
        <div class="sign-label">Signature du client</div>
    </div>
    <div>
        <div class="sign-line"></div>
        <div class="sign-label">Cachet et signature</div>
    </div>
</div>

{{-- ================= PIED DE PAGE ================= --}}
<div class="footer">
    @if($settings?->footer_text)
        <div class="thanks">{{ $settings->footer_text }}</div>
    @endif
    <div class="legal">
        Document généré le {{ now()->format('d/m/Y \à H:i') }}
        @if($settings?->company_name) — {{ $settings->company_name }}@endif
    </div>
</div>

</body>
</html>
