<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de ventes - {{ ucfirst($period) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { margin: 0; }
        h1 { font-size: 20px; }
        h2 { font-size: 16px; }
        h3 { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 10px; }
        .mb-2 { margin-bottom: 10px; }
        .badge { display: inline-block; padding: 3px 6px; font-size: 10px; color: #fff; border-radius: 3px; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
    </style>
</head>
<body>

    <h1>Rapport de ventes - {{ ucfirst($period) }}</h1>
    <h3>Période : {{ $start->format('d/m/Y') }} à {{ $end->format('d/m/Y') }}</h3>
    <h3>Nombre de factures : {{ $factures->count() }}</h3>
    <h3>Total encaissé : {{ number_format($total, 2, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</h3>

    <hr class="mt-2 mb-2">

    @forelse($factures as $facture)
        <h3>Facture #{{ $facture->numero_facture }}
            <span class="badge {{ $facture->type_document=='pro-forma' ? 'badge-warning' : 'badge-success' }}">
                {{ ucfirst($facture->type_document) }}
            </span>
        </h3>
        <p>
            <strong>Client :</strong> {{ $facture->client->nom ?? '—' }} {{ $facture->client->prenom ?? '' }}<br>
            <strong>Date :</strong> {{ $facture->created_at->format('d/m/Y H:i') }}
        </p>

        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix unitaire ({{ $app_settings->devise ?? 'FCFA' }})</th>
                    <th>Total ligne ({{ $app_settings->devise ?? 'FCFA' }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture->lignes as $ligne)
                    <tr>
                        <td>{{ $ligne->produit->nom ?? '—' }}</td>
                        <td>{{ $ligne->quantite }}</td>
                        <td class="text-right">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($ligne->total_ligne, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="text-right fw-bold">Total facture : {{ number_format($facture->total, 2, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</p>
        <hr class="mt-2 mb-2">
    @empty
        <p>Aucune facture enregistrée pour cette période.</p>
    @endforelse

    <h3>Total général : {{ number_format($total, 2, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</h3>

</body>
</html>
