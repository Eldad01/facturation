<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>INVENTAIRE - {{ $inventaire->reference }}</title>

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

        .status-validee { background: #198754; }
        .status-brouillon { background: #fd7e14; }

        /* ---------- Bloc traçabilité (audit) ---------- */
        .audit-box {
            width: 100%;
            display: table;
            border: 1px solid #dee2e6;
            border-left: 3px solid {{ $accent }};
            border-radius: 2px;
            margin-bottom: 18px;
        }

        .audit-box .col {
            display: table-cell;
            width: 50%;
            padding: 10px 14px;
            vertical-align: top;
        }

        .audit-box .col + .col {
            border-left: 1px solid #dee2e6;
        }

        .audit-box .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .audit-box .value {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .audit-box .value:last-child {
            margin-bottom: 0;
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

        table.items tfoot td {
            border-top: 1.5px solid #212529;
            font-weight: bold;
            padding-top: 8px;
            font-size: 11px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .text-danger { color: #dc3545; }
        .text-primary { color: {{ $accent }}; }

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
    </div>
</div>

{{-- ================= TITRE DU DOCUMENT ================= --}}
<div class="doc-title-bar">FICHE D'INVENTAIRE N° {{ $inventaire->reference }}</div>

<div class="doc-sub-meta">
    Date d'édition : {{ now()->format('d/m/Y H:i') }}
    <span class="status-badge {{ $inventaire->isValidee() ? 'status-validee' : 'status-brouillon' }}">
        {{ $inventaire->isValidee() ? 'Validé' : 'Brouillon' }}
    </span>
</div>

{{-- ================= TRAÇABILITÉ (AUDIT) ================= --}}
<div class="audit-box">
    <div class="col">
        <div class="label">Ouvert par</div>
        <div class="value">{{ $inventaire->userCreation->name ?? '—' }}</div>
        <div class="label">Ouvert le</div>
        <div class="value">{{ $inventaire->created_at->format('d/m/Y à H:i') }}</div>
    </div>
    <div class="col">
        <div class="label">Validé par</div>
        <div class="value">{{ $inventaire->userValidation->name ?? '— (non validé)' }}</div>
        <div class="label">Validé le</div>
        <div class="value">{{ $inventaire->date_validation?->format('d/m/Y à H:i') ?? '—' }}</div>
    </div>
</div>

@if($inventaire->notes)
    <div style="font-size:10.5px; margin-bottom:16px;">
        <strong style="text-transform:uppercase; font-size:9px; color:#495057; letter-spacing:0.5px;">Notes :</strong>
        {{ $inventaire->notes }}
    </div>
@endif

{{-- ================= LIGNES ================= --}}
@php
    $totalEcartValorise = 0;
    $nbLignesComptees = 0;
    $nbEcarts = 0;
@endphp

<table class="items">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th>Produit</th>
            <th style="width:10%;">SKU</th>
            <th class="text-center" style="width:9%;">Stock théo.</th>
            <th class="text-center" style="width:9%;">Stock réel</th>
            <th class="text-center" style="width:8%;">Écart</th>
            <th class="text-right" style="width:14%;">CUMP achat ({{ $devise }})</th>
            <th class="text-right" style="width:16%;">Écart valorisé ({{ $devise }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inventaire->lignes as $index => $ligne)
            @php
                if ($ligne->stock_reel !== null) {
                    $nbLignesComptees++;
                    if ($ligne->ecart != 0) $nbEcarts++;
                }
                $totalEcartValorise += $ligne->ecart_valorise ?? 0;
            @endphp
            <tr>
                <td class="text-center text-muted">{{ $index + 1 }}</td>
                <td>{{ $ligne->produit->nom ?? 'Produit supprimé' }}</td>
                <td>{{ $ligne->produit->sku ?? '—' }}</td>
                <td class="text-center">{{ $ligne->stock_theorique }}</td>
                <td class="text-center">{{ $ligne->stock_reel ?? '—' }}</td>
                <td class="text-center">
                    @if($ligne->ecart === null)
                        —
                    @elseif($ligne->ecart == 0)
                        0
                    @else
                        <span class="{{ $ligne->ecart > 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $ligne->ecart > 0 ? '+' : '' }}{{ $ligne->ecart }}
                        </span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($ligne->produit->prix_achat ?? 0, 0, ',', ' ') }}</td>
                <td class="text-right">
                    @if($ligne->ecart_valorise === null)
                        —
                    @else
                        <span class="{{ $ligne->ecart_valorise > 0 ? 'text-primary' : ($ligne->ecart_valorise < 0 ? 'text-danger' : '') }}">
                            {{ $ligne->ecart_valorise > 0 ? '+' : '' }}{{ number_format($ligne->ecart_valorise, 0, ',', ' ') }}
                        </span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="text-right">Total écart valorisé</td>
            <td class="text-right">
                <span class="{{ $totalEcartValorise > 0 ? 'text-primary' : ($totalEcartValorise < 0 ? 'text-danger' : '') }}">
                    {{ $totalEcartValorise > 0 ? '+' : '' }}{{ number_format($totalEcartValorise, 0, ',', ' ') }} {{ $devise }}
                </span>
            </td>
        </tr>
    </tfoot>
</table>

<div style="font-size:10px; color:#6c757d; margin-top:8px;">
    {{ $inventaire->lignes->count() }} produit(s) au total — {{ $nbLignesComptees }} compté(s) — {{ $nbEcarts }} écart(s) constaté(s).
</div>

{{-- ================= SIGNATURES ================= --}}
<div class="signatures">
    <div>
        <div class="sign-line"></div>
        <div class="sign-label">Responsable du comptage</div>
    </div>
    <div>
        <div class="sign-line"></div>
        <div class="sign-label">Validé par (Direction)</div>
    </div>
</div>

{{-- ================= PIED DE PAGE ================= --}}
<div class="footer">
    <div class="legal">
        Document généré le {{ now()->format('d/m/Y \à H:i') }}
        @if($settings?->company_name) — {{ $settings->company_name }}@endif
        — Document interne à conserver pour audit / contrôle.
    </div>
</div>

</body>
</html>
