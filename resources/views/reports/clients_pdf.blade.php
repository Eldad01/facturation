<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État clients</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { margin: 0; }
        h1 { font-size: 20px; }
        h3 { font-size: 13px; font-weight: normal; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table th, table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-danger { color: #c0392b; }
        .text-success { color: #1e8449; }
    </style>
</head>
<body>
    <h1>État clients</h1>
    <h3>Généré le {{ now()->format('d/m/Y H:i') }} — {{ $clients->count() }} client(s)</h3>

    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Nb factures</th>
                <th class="text-right">Total facturé ({{ $app_settings->devise ?? 'FCFA' }})</th>
                <th class="text-right">Total payé ({{ $app_settings->devise ?? 'FCFA' }})</th>
                <th class="text-right">Solde dû ({{ $app_settings->devise ?? 'FCFA' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                @php
                    $totalFacture = $client->total_facture ?? 0;
                    $totalPaye = $client->total_paye ?? 0;
                    $solde = max(0, $totalFacture - $totalPaye);
                @endphp
                <tr>
                    <td>{{ $client->nom }} {{ $client->prenom }}</td>
                    <td>{{ $client->factures_count }}</td>
                    <td class="text-right">{{ number_format($totalFacture, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($totalPaye, 0, ',', ' ') }}</td>
                    <td class="text-right {{ $solde > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($solde, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
