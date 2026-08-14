<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chiffre d'affaires &amp; bénéfices</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { margin: 0; }
        h1 { font-size: 20px; }
        h3 { font-size: 13px; font-weight: normal; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table th, table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .warning { margin-top: 16px; padding: 10px; background: #fff3cd; border: 1px solid #ffe08a; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Chiffre d'affaires &amp; bénéfices</h1>
    <h3>Période : {{ $label }} — Généré le {{ now()->format('d/m/Y H:i') }}</h3>

    <table>
        <tbody>
            <tr><td>Nombre de factures</td><td class="text-right">{{ $nbFactures }}</td></tr>
            <tr><td>Chiffre d'affaires</td><td class="text-right">{{ number_format($chiffreAffaires, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td></tr>
            <tr><td>Bénéfice estimé</td><td class="text-right">{{ number_format($benefice, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</td></tr>
        </tbody>
    </table>

    <div class="warning">
        Le bénéfice est estimé à partir du coût moyen d'achat actuel (CUMP) de chaque produit, et non du coût
        réel au moment de chaque vente (pas d'historique de coût par vente dans l'application). Ce chiffre est
        donc une estimation.
    </div>
</body>
</html>
