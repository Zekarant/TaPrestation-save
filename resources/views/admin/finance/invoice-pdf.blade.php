<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number ?? $invoice->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #222 }
        .header { text-align: left; margin-bottom: 12px }
        .company { float: right; text-align: right }
        .items { width: 100%; border-collapse: collapse; margin-top: 12px }
        .items th, .items td { border: 1px solid #ddd; padding: 8px }
        .items th { background: #f8f8f8 }
        .summary { margin-top: 16px; float: right; width: 300px }
    </style>
</head>
<body>
    <div class="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2>{{ config('app.name') }}</h2>
                <div>{{ config('app.address', '') }}</div>
            </div>
            <div class="company">
                <div><strong>Facture</strong></div>
                <div>N°: {{ $invoice->number ?? $invoice->id }}</div>
                <div>Date: {{ \Carbon\Carbon::parse($invoice->created_at ?? now())->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div style="clear:both;margin-top:20px;">
        <strong>Facturer à :</strong>
        <div>{{ $invoice->name ?? '—' }}</div>
        <div>{{ $invoice->email ?? '—' }}</div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th style="width:80px">Quantité</th>
                <th style="width:120px">Prix unitaire</th>
                <th style="width:120px">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->description ?? $item->name ?? 'Item' }}</td>
                <td style="text-align:center">{{ $item->quantity ?? 1 }}</td>
                <td style="text-align:right">{{ number_format($item->price ?? $item->unit_price ?? 0, 2, ',', ' ') }} €</td>
                <td style="text-align:right">{{ number_format(($item->quantity ?? 1) * ($item->price ?? $item->unit_price ?? 0), 2, ',', ' ') }} €</td>
            </tr>
            @empty
            <tr><td colspan="4">Aucun item</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table style="width:100%">
            <tr><td>Sous-total</td><td style="text-align:right">{{ number_format($invoice->subtotal ?? $invoice->total ?? 0, 2, ',', ' ') }} €</td></tr>
            <tr><td>Taxe</td><td style="text-align:right">{{ number_format($invoice->tax ?? 0, 2, ',', ' ') }} €</td></tr>
            <tr><td><strong>Total</strong></td><td style="text-align:right"><strong>{{ number_format($invoice->total ?? 0, 2, ',', ' ') }} €</strong></td></tr>
        </table>
    </div>

</body>
</html>
