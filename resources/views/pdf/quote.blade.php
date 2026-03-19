<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $quote->reference_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .document { max-width: 800px; margin: 0 auto; padding: 30px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 30px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { text-align: right; }
        .company-name { font-size: 22px; font-weight: bold; color: #2563eb; margin-bottom: 5px; }
        .company-info { font-size: 10px; color: #666; line-height: 1.6; }
        .quote-label { font-size: 28px; font-weight: bold; color: #1a1a1a; text-transform: uppercase; }
        .quote-ref { font-size: 14px; color: #2563eb; margin-top: 5px; }
        .quote-meta { font-size: 10px; color: #888; margin-top: 8px; }

        /* Parties */
        .parties { display: table; width: 100%; margin-bottom: 25px; }
        .party { display: table-cell; width: 48%; vertical-align: top; }
        .party-spacer { display: table-cell; width: 4%; }
        .party-label { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #2563eb; margin-bottom: 8px; letter-spacing: 1px; }
        .party-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; }
        .party-name { font-weight: bold; font-size: 13px; margin-bottom: 4px; }

        /* Service */
        .service-section { margin-bottom: 20px; }
        .section-title { font-size: 13px; font-weight: bold; color: #1a1a1a; border-bottom: 2px solid #2563eb; padding-bottom: 5px; margin-bottom: 10px; }

        /* Items table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table thead th {
            background: #2563eb;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table thead th:last-child,
        .items-table thead th:nth-child(3),
        .items-table thead th:nth-child(4) { text-align: right; }
        .items-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .items-table tbody td:last-child,
        .items-table tbody td:nth-child(3),
        .items-table tbody td:nth-child(4) { text-align: right; }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }

        /* Totals */
        .totals { width: 50%; margin-left: auto; margin-bottom: 25px; }
        .totals-table { width: 100%; }
        .totals-table td { padding: 5px 10px; font-size: 11px; }
        .totals-table td:last-child { text-align: right; }
        .totals-table .total-row { border-top: 2px solid #2563eb; }
        .totals-table .total-row td { font-size: 14px; font-weight: bold; color: #2563eb; padding-top: 8px; }
        .discount-row td { color: #16a34a; }

        /* Notes & Terms */
        .notes-section { margin-bottom: 20px; }
        .notes-section .section-title { border-bottom-color: #94a3b8; }
        .notes-content { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; font-size: 11px; color: #555; }

        /* Validity banner */
        .validity-banner {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            padding: 10px 15px;
            text-align: center;
            font-size: 11px;
            color: #92400e;
            margin-bottom: 20px;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background: #e2e8f0; color: #475569; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-viewed { background: #fef3c7; color: #92400e; }
        .status-accepted { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        /* Footer */
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #aaa; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="document">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">TapRestation</div>
                <div class="company-info">
                    Plateforme de services professionnels<br>
                    @if($quote->prestataire && $quote->prestataire->user)
                        {{ $quote->prestataire->user->name }}<br>
                        {{ $quote->prestataire->adresse ?? '' }}<br>
                        {{ $quote->prestataire->user->email ?? '' }}<br>
                        {{ $quote->prestataire->telephone ?? '' }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="quote-label">Devis</div>
                <div class="quote-ref">{{ $quote->reference_number }}</div>
                <div class="quote-meta">
                    Créé le : {{ $quote->created_at ? $quote->created_at->format('d/m/Y') : 'N/A' }}<br>
                    @if($quote->sent_at)
                        Envoyé le : {{ $quote->sent_at->format('d/m/Y') }}<br>
                    @endif
                    Statut : <span class="status-badge status-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party">
                <div class="party-label">Prestataire</div>
                <div class="party-box">
                    @if($quote->prestataire && $quote->prestataire->user)
                        <div class="party-name">{{ $quote->prestataire->user->name }}</div>
                        <div>{{ $quote->prestataire->adresse ?? '' }}</div>
                        <div>{{ $quote->prestataire->user->email ?? '' }}</div>
                        <div>{{ $quote->prestataire->telephone ?? '' }}</div>
                    @else
                        <div class="party-name">Prestataire</div>
                    @endif
                </div>
            </div>
            <div class="party-spacer"></div>
            <div class="party">
                <div class="party-label">Client</div>
                <div class="party-box">
                    @if($quote->client && $quote->client->user)
                        <div class="party-name">{{ $quote->client->user->name }}</div>
                        <div>{{ $quote->client->user->email ?? '' }}</div>
                        <div>{{ $quote->client->telephone ?? '' }}</div>
                    @else
                        <div class="party-name">Client</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Service -->
        @if($quote->service)
            <div class="service-section">
                <div class="section-title">Service concerné</div>
                <p>{{ $quote->service->title ?? $quote->service->name ?? 'N/A' }}</p>
            </div>
        @endif

        <!-- Description -->
        @if($quote->title || $quote->description)
            <div class="service-section">
                <div class="section-title">{{ $quote->title ?? 'Description' }}</div>
                @if($quote->description)
                    <p>{{ $quote->description }}</p>
                @endif
            </div>
        @endif

        <!-- Validity -->
        @if($quote->valid_until)
            <div class="validity-banner">
                ⏱ Ce devis est valable jusqu'au <strong>{{ $quote->valid_until->format('d/m/Y') }}</strong>
                @if($quote->valid_until->isPast())
                    — <strong style="color: #dc2626;">EXPIRÉ</strong>
                @endif
            </div>
        @endif

        <!-- Items -->
        @if($quote->items && count($quote->items) > 0)
            <div class="section-title">Détail des prestations</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">Désignation</th>
                        <th style="width: 15%;">Quantité</th>
                        <th style="width: 20%;">Prix unitaire</th>
                        <th style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item['description'] ?? $item['name'] ?? 'Article' }}</strong>
                                @if(!empty($item['details']))
                                    <br><span style="color: #888; font-size: 10px;">{{ $item['details'] }}</span>
                                @endif
                            </td>
                            <td style="text-align: right;">{{ $item['quantity'] ?? 1 }}</td>
                            <td style="text-align: right;">{{ number_format($item['unit_price'] ?? $item['price'] ?? 0, 2, ',', ' ') }} €</td>
                            <td style="text-align: right;">{{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['price'] ?? 0), 2, ',', ' ') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Totals -->
        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td>Sous-total HT</td>
                    <td>{{ number_format($quote->subtotal ?? 0, 2, ',', ' ') }} €</td>
                </tr>
                @if($quote->discount_amount > 0)
                    <tr class="discount-row">
                        <td>Remise
                            @if($quote->discount_type === 'percentage')
                                ({{ $quote->discount_amount }}%)
                            @endif
                        </td>
                        <td>-{{ number_format($quote->discount_type === 'percentage' ? ($quote->subtotal * $quote->discount_amount / 100) : $quote->discount_amount, 2, ',', ' ') }} €</td>
                    </tr>
                @endif
                @if($quote->tax_rate > 0)
                    <tr>
                        <td>TVA ({{ number_format($quote->tax_rate, 1) }}%)</td>
                        <td>{{ number_format($quote->tax_amount ?? 0, 2, ',', ' ') }} €</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>Total TTC</td>
                    <td>{{ number_format($quote->total ?? 0, 2, ',', ' ') }} €</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($quote->notes)
            <div class="notes-section">
                <div class="section-title">Notes</div>
                <div class="notes-content">{{ $quote->notes }}</div>
            </div>
        @endif

        <!-- Terms -->
        @if($quote->terms)
            <div class="notes-section">
                <div class="section-title">Conditions</div>
                <div class="notes-content">{{ $quote->terms }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Devis généré le {{ now()->format('d/m/Y à H:i') }} via TapRestation &mdash;
            Réf. {{ $quote->reference_number }}
        </div>
    </div>
</body>
</html>
