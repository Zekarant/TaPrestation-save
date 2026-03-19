<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiements securises</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f8fb;
            color: #16213e;
        }

        .wrap {
            max-width: 960px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbe2ef;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .header {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .title {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .subtitle {
            margin: 0;
            color: #52607a;
        }

        .alert {
            margin: 16px 24px 0;
            padding: 14px 16px;
            border-radius: 12px;
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
        }

        .actions {
            display: flex;
            gap: 12px;
            padding: 24px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-primary {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-secondary {
            background: #eef2ff;
            color: #1e3a8a;
        }

        .list {
            padding: 0 24px 24px;
        }

        .item {
            padding: 18px 0;
            border-top: 1px solid #e5e7eb;
        }

        .item:first-child {
            border-top: 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
        }

        .meta {
            margin-top: 8px;
            font-size: 14px;
            color: #64748b;
        }

        .amount {
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .empty {
            padding: 32px 24px;
            color: #64748b;
        }

        @media (max-width: 640px) {
            .row {
                flex-direction: column;
            }

            .amount {
                white-space: normal;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="header">
                <h1 class="title">Mes paiements securises</h1>
                <p class="subtitle">La vue detaillee a ete remplacee temporairement par une version de secours pour eviter l'erreur 500.</p>
            </div>

            @if(!empty($errorMessage))
                <div class="alert">{{ $errorMessage }}</div>
            @endif

            <div class="actions">
                <a class="btn btn-primary" href="{{ url('/client/dashboard') }}">Retour au dashboard</a>
                <a class="btn btn-secondary" href="{{ url('/client/escrow') }}">Recharger la page</a>
            </div>

            @if(($escrows->count() ?? 0) === 0)
                <div class="empty">
                    Aucune transaction securisee disponible pour le moment.
                </div>
            @else
                <div class="list">
                    @foreach($escrows as $escrow)
                        @php
                            $escrowId = (int) ($escrow->id ?? 0);
                            $escrowType = (string) ($escrow->escrowable_type ?? 'Transaction');
                            $escrowStatus = (string) ($escrow->status ?? 'inconnu');
                            $escrowAmount = (float) ($escrow->total_amount ?? ($escrow->amount ?? 0));
                            $escrowDeposit = (float) ($escrow->deposit_amount ?? 0);
                            $escrowCreatedAt = $escrow->created_at ?? null;

                            try {
                                $escrowCreatedLabel = $escrowCreatedAt
                                    ? \Carbon\Carbon::parse($escrowCreatedAt)->format('d/m/Y H:i')
                                    : 'Date indisponible';
                            } catch (\Throwable $e) {
                                $escrowCreatedLabel = 'Date indisponible';
                            }
                        @endphp
                        <div class="item">
                            <div class="row">
                                <div>
                                    <strong>#{{ $escrowId }}</strong> {{ $escrowType }}
                                    <div class="meta">
                                        Cree le {{ $escrowCreatedLabel }}
                                        @if($escrowDeposit > 0)
                                            <span> • Caution {{ number_format($escrowDeposit, 2, ',', ' ') }} EUR</span>
                                        @endif
                                    </div>
                                    <span class="badge">{{ $escrowStatus }}</span>
                                </div>
                                <div>
                                    <div class="amount">{{ number_format($escrowAmount, 2, ',', ' ') }} EUR</div>
                                    @if($escrowId > 0)
                                        <div class="meta">
                                            <a href="{{ url('/client/escrow/' . $escrowId) }}">Voir le detail</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</body>
</html>
