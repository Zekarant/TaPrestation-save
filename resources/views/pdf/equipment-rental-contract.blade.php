<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de Location #{{ $rental->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2563eb; padding-bottom: 15px; }
        .header h1 { font-size: 22px; color: #2563eb; margin-bottom: 5px; }
        .header p { font-size: 12px; color: #666; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .info-grid { width: 100%; }
        .info-grid td { padding: 4px 8px; vertical-align: top; }
        .info-grid .label { font-weight: bold; color: #555; width: 180px; }
        .info-grid .value { color: #111; }
        .two-col { width: 100%; }
        .two-col td { width: 50%; vertical-align: top; padding: 5px; }
        .amount-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .amount-table th, .amount-table td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        .amount-table th { background-color: #f3f4f6; font-weight: bold; }
        .amount-table .total-row { font-weight: bold; background-color: #eff6ff; }
        .terms { font-size: 10px; color: #555; }
        .terms ol { padding-left: 20px; }
        .terms li { margin-bottom: 4px; }
        .signatures { margin-top: 40px; }
        .signatures table { width: 100%; }
        .signatures td { width: 50%; padding: 10px; }
        .signature-box { border: 1px solid #ccc; height: 80px; margin-top: 5px; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-blue { background-color: #dbeafe; color: #1d4ed8; }
        .badge-green { background-color: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="container">
        {{-- EN-TÊTE --}}
        <div class="header">
            <h1>CONTRAT DE LOCATION D'ÉQUIPEMENT</h1>
            <p>Contrat N° LOC-{{ str_pad($rental->id, 6, '0', STR_PAD_LEFT) }} | Établi le {{ now()->format('d/m/Y') }}</p>
        </div>

        {{-- PARTIES --}}
        <div class="section">
            <div class="section-title">PARTIES CONTRACTANTES</div>
            <table class="two-col">
                <tr>
                    <td>
                        <strong>LOUEUR (Prestataire)</strong><br>
                        {{ $rental->prestataire->user->name ?? 'N/A' }}<br>
                        @if($rental->prestataire->company_name)
                            {{ $rental->prestataire->company_name }}<br>
                        @endif
                        {{ $rental->prestataire->user->email ?? '' }}<br>
                        {{ $rental->prestataire->user->phone ?? '' }}
                    </td>
                    <td>
                        <strong>LOCATAIRE (Client)</strong><br>
                        {{ $rental->client->user->name ?? 'N/A' }}<br>
                        {{ $rental->client->user->email ?? '' }}<br>
                        {{ $rental->client->user->phone ?? '' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- ÉQUIPEMENT --}}
        <div class="section">
            <div class="section-title">ÉQUIPEMENT LOUÉ</div>
            <table class="info-grid">
                <tr>
                    <td class="label">Nom de l'équipement :</td>
                    <td class="value">{{ $rental->equipment->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Catégorie :</td>
                    <td class="value">{{ $rental->equipment->category->name ?? 'N/A' }}</td>
                </tr>
                @if($rental->equipment->brand)
                <tr>
                    <td class="label">Marque / Modèle :</td>
                    <td class="value">{{ $rental->equipment->brand }} {{ $rental->equipment->model ?? '' }}</td>
                </tr>
                @endif
                @if($rental->equipment->serial_number)
                <tr>
                    <td class="label">Numéro de série :</td>
                    <td class="value">{{ $rental->equipment->serial_number }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">État à la prise en charge :</td>
                    <td class="value">{{ $rental->equipment_condition ?? 'Bon état' }}</td>
                </tr>
            </table>
        </div>

        {{-- PÉRIODE ET TARIFS --}}
        <div class="section">
            <div class="section-title">PÉRIODE DE LOCATION</div>
            <table class="info-grid">
                <tr>
                    <td class="label">Date de début :</td>
                    <td class="value">{{ \Carbon\Carbon::parse($rental->start_date)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Date de fin :</td>
                    <td class="value">{{ \Carbon\Carbon::parse($rental->end_date)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Durée :</td>
                    <td class="value">{{ $rental->duration ?? \Carbon\Carbon::parse($rental->start_date)->diffInDays(\Carbon\Carbon::parse($rental->end_date)) }} jour(s)</td>
                </tr>
            </table>
        </div>

        {{-- MONTANTS --}}
        <div class="section">
            <div class="section-title">DÉTAILS FINANCIERS</div>
            <table class="amount-table">
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Montant</th>
                </tr>
                <tr>
                    <td>Prix unitaire (par jour)</td>
                    <td style="text-align: right;">{{ number_format($rental->unit_price ?? 0, 2) }} €</td>
                </tr>
                <tr>
                    <td>Montant de base</td>
                    <td style="text-align: right;">{{ number_format($rental->base_amount ?? 0, 2) }} €</td>
                </tr>
                @if($rental->security_deposit > 0)
                <tr>
                    <td>Caution</td>
                    <td style="text-align: right;">{{ number_format($rental->security_deposit, 2) }} €</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td style="text-align: right;">{{ number_format($rental->total_amount ?? 0, 2) }} €</td>
                </tr>
            </table>
        </div>

        {{-- CONDITIONS --}}
        <div class="section">
            <div class="section-title">CONDITIONS GÉNÉRALES</div>
            <div class="terms">
                <ol>
                    <li>Le locataire s'engage à utiliser l'équipement conformément à sa destination et dans le respect des consignes du loueur.</li>
                    <li>L'équipement doit être restitué dans l'état dans lequel il a été remis, sous réserve de l'usure normale.</li>
                    <li>En cas de dommage, perte ou vol de l'équipement, le locataire en assume l'entière responsabilité et s'engage à indemniser le loueur.</li>
                    <li>La caution sera restituée au locataire dans un délai de 48h après la restitution, sous réserve de la vérification de l'état de l'équipement.</li>
                    <li>Toute annulation effectuée moins de 24h avant le début de la location ne donnera lieu à aucun remboursement.</li>
                    <li>Le loueur se réserve le droit de résilier le contrat en cas de non-respect des conditions d'utilisation.</li>
                    <li>En cas de litige, les parties s'engagent à rechercher une solution amiable avant toute action en justice.</li>
                </ol>
            </div>
        </div>

        {{-- SIGNATURES --}}
        <div class="signatures">
            <div class="section-title">SIGNATURES</div>
            <table>
                <tr>
                    <td>
                        <strong>Le Loueur</strong><br>
                        <small>{{ $rental->prestataire->user->name ?? 'N/A' }}</small>
                        <div class="signature-box"></div>
                        <small>Date : ____/____/________</small>
                    </td>
                    <td>
                        <strong>Le Locataire</strong><br>
                        <small>{{ $rental->client->user->name ?? 'N/A' }}</small>
                        <div class="signature-box"></div>
                        <small>Date : ____/____/________</small>
                    </td>
                </tr>
            </table>
        </div>

        {{-- PIED DE PAGE --}}
        <div class="footer">
            Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }} — Contrat N° LOC-{{ str_pad($rental->id, 6, '0', STR_PAD_LEFT) }}<br>
            Ce document fait office de contrat de location entre les parties mentionnées ci-dessus.
        </div>
    </div>
</body>
</html>
