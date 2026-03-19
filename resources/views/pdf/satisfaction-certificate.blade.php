<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de Satisfaction - {{ $certificate->certificate_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #333; background: #fff; }
        .certificate { max-width: 800px; margin: 0 auto; padding: 40px; position: relative; }
        .border-frame {
            border: 3px solid #c9a84c;
            padding: 40px;
            position: relative;
        }
        .border-frame::before {
            content: '';
            position: absolute;
            top: 8px; left: 8px; right: 8px; bottom: 8px;
            border: 1px solid #c9a84c;
        }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 28px; font-weight: bold; color: #c9a84c; margin-bottom: 5px; }
        .subtitle { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 2px; }
        .title-section { text-align: center; margin: 30px 0; }
        .title-section h1 {
            font-size: 32px;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }
        .title-section .year { font-size: 22px; color: #c9a84c; font-weight: bold; }
        .divider {
            width: 100px;
            height: 2px;
            background: #c9a84c;
            margin: 20px auto;
        }
        .recipient { text-align: center; margin: 30px 0; }
        .recipient .label { font-size: 14px; color: #888; margin-bottom: 8px; }
        .recipient .name { font-size: 24px; font-weight: bold; color: #1a1a1a; }
        .stats-section { margin: 30px 0; }
        .stats-grid { display: table; width: 100%; }
        .stat-item { display: table-cell; text-align: center; padding: 15px; width: 33.33%; }
        .stat-value { font-size: 28px; font-weight: bold; color: #c9a84c; }
        .stat-label { font-size: 11px; color: #888; text-transform: uppercase; margin-top: 5px; }
        .description { text-align: center; margin: 25px 0; font-size: 14px; line-height: 1.6; color: #555; }
        .certificate-info { margin-top: 30px; }
        .info-grid { display: table; width: 100%; }
        .info-left, .info-right { display: table-cell; width: 50%; vertical-align: bottom; }
        .info-left { text-align: left; }
        .info-right { text-align: right; }
        .info-item { font-size: 11px; color: #888; margin-bottom: 5px; }
        .info-item strong { color: #555; }
        .seal { text-align: center; margin-top: 20px; }
        .seal-circle {
            display: inline-block;
            width: 80px;
            height: 80px;
            border: 2px solid #c9a84c;
            border-radius: 50%;
            line-height: 80px;
            font-size: 10px;
            color: #c9a84c;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #aaa; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="border-frame">
            <div class="header">
                <div class="logo">TapRestation</div>
                <div class="subtitle">Plateforme de services professionnels</div>
            </div>

            <div class="title-section">
                <h1>Certificat de Satisfaction</h1>
                <div class="year">{{ $certificate->year }}</div>
            </div>

            <div class="divider"></div>

            <div class="recipient">
                <div class="label">Décerné à</div>
                <div class="name">{{ $certificate->prestataire->user->name ?? 'Prestataire' }}</div>
            </div>

            <div class="description">
                En reconnaissance de l'excellence de ses prestations et de la satisfaction
                exceptionnelle de ses clients au cours de l'année {{ $certificate->year }}.
            </div>

            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($certificate->satisfaction_rate, 1) }}%</div>
                        <div class="stat-label">Taux de satisfaction</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $certificate->total_reviews }}</div>
                        <div class="stat-label">Avis reçus</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            @if($certificate->satisfaction_rate >= 95)
                                Or
                            @elseif($certificate->satisfaction_rate >= 90)
                                Argent
                            @else
                                Bronze
                            @endif
                        </div>
                        <div class="stat-label">Niveau</div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="certificate-info">
                <div class="info-grid">
                    <div class="info-left">
                        <div class="info-item"><strong>N° Certificat :</strong> {{ $certificate->certificate_number }}</div>
                        <div class="info-item"><strong>Délivré le :</strong> {{ $certificate->issued_at ? $certificate->issued_at->format('d/m/Y') : now()->format('d/m/Y') }}</div>
                        <div class="info-item"><strong>Valide jusqu'au :</strong> {{ $certificate->expires_at ? $certificate->expires_at->format('d/m/Y') : 'Illimité' }}</div>
                    </div>
                    <div class="info-right">
                        <div class="seal">
                            <div class="seal-circle">Certifié</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer">
                Ce certificat est généré automatiquement par TapRestation. Il atteste de la qualité des prestations fournies sur la plateforme.
            </div>
        </div>
    </div>
</body>
</html>
