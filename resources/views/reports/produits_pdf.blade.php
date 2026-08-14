<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État produits</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { margin: 0; }
        h1 { font-size: 20px; }
        h3 { font-size: 13px; font-weight: normal; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .mt-3 { margin-top: 16px; }
    </style>
</head>
<body>
    <h1>État produits</h1>
    <h3>Généré le {{ now()->format('d/m/Y H:i') }} — Top ventes période : {{ $label }}</h3>
    <h3>Valorisation totale du stock : {{ number_format($valorisationTotale, 0, ',', ' ') }} {{ $app_settings->devise ?? 'FCFA' }}</h3>

    <h2 class="mt-3">Top produits vendus</h2>
    <table>
        <thead>
            <tr><th>Produit</th><th class="text-right">Quantité vendue</th></tr>
        </thead>
        <tbody>
            @forelse($topProduits as $tp)
                <tr><td>{{ $tp->produit->nom ?? '—' }}</td><td class="text-right">{{ $tp->total }}</td></tr>
            @empty
                <tr><td colspan="2">Aucune vente sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="mt-3">Stock &amp; valorisation</h2>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Coût moyen (CUMP)</th>
                <th class="text-right">Valorisation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produits as $produit)
                <tr>
                    <td>{{ $produit->nom }}</td>
                    <td class="text-right">{{ $produit->stock }}</td>
                    <td class="text-right">{{ number_format($produit->prix_achat, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($produit->stock * $produit->prix_achat, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
