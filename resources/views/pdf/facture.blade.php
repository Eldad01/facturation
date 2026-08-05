<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $titre }} - {{ $facture->numero_facture }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 30px;
            color: #000;
        }

        .header {
            width: 100%;
            display: table;
            margin-bottom: 20px;
        }

        .header > div {
            display: table-cell;
            vertical-align: top;
        }

        .company {
            width: 33%;
            font-size: 11px;
        }

        .logo {
            width: 33%;
            text-align: center;
        }

        .country {
            width: 33%;
            text-align: right;
            font-size: 11px;
        }

        h1 {
            text-align: center;
            margin: 25px 0 10px;
            letter-spacing: 1px;
        }

        .client {
            margin-top: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total {
            margin-top: 12px;
            text-align: right;
            font-weight: bold;
            font-size: 13px;
        }

        .date {
            margin-top: 10px;
            text-align: right;
            font-size: 11px;
        }

    </style>
</head>

<body>

<!-- ENTÊTE -->
<div class="header">
    <!-- GAUCHE : société -->
    <div class="company">
        <strong>{{ $settings?->company_name }}</strong><br>
        @if($settings?->ifu) IFU : {{ $settings->ifu }}<br>@endif
        @if($settings?->rccm) RCCM : {{ $settings->rccm }}<br>@endif
        @if($settings?->phone) {{ $settings->phone }}<br>@endif
        @if($settings?->email) {{ $settings->email }}<br>@endif
        @if($settings?->address) {{ $settings->address }}@endif
    </div>

    <!-- CENTRE : logo -->
    <div class="logo">
        @if($settings?->logo)
            <img src="{{ public_path('logos/'.$settings->logo) }}" style="max-height:90px; max-width:150px;">
        @endif
    </div>

    <!-- DROITE : pays + client -->
    <div class="country">
        <strong style="text-align: center; display: block;">{{ $settings?->country ?? 'BURKINA FASO' }}</strong>
        <em>{{ $settings?->national_motto ?? 'La patrie ou la mort, nous vaincrons' }}</em>

        <div class="client">
            <strong >À :</strong><br>
            {{ $facture->client->nom }} {{ $facture->client->prenom ?? '' }}<br>
            {{ $facture->client->telephone ?? '' }}
        </div>
    </div>
</div>

<!-- TITRE -->
<h1>{{ $titre }}</h1>

<!-- TABLE DES OPÉRATIONS -->
<table>
    <thead>
        <tr>
            <th colspan="4" class="text-center">Facture #{{ $facture->numero_facture }}</th>
        </tr>
        <tr>
            <th>Produit</th>
            <th>Qté</th>
            <th>PU ({{ $app_settings->devise ?? 'FCFA' }})</th>
            <th>Total ({{ $app_settings->devise ?? 'FCFA' }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($facture->lignes as $ligne)
            <tr>
                <td>{{ $ligne->produit->nom ?? '—' }}</td>
                <td class="text-right">{{ $ligne->quantite }}</td>
                <td class="text-right">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne->total_ligne, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="total">
    TOTAL : {{ number_format($facture->total, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}
</div><br><br><br><br>

<!-- DATE -->
<div class="date">
    @if($facture->type_document === 'recu')
        Ouagadougou, le {{ $facture->created_at->format('d/m/Y') }}
    @else
        Ouagadougou, le {{ $facture->updated_at->format('d/m/Y') }}
    @endif
</div>

</body>
</html>
