<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification e-mail — TaPrestation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f3f4f6;
            color: #111827;
        }
        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
            padding: 24px 24px 20px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .logo-badge {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
        }
        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }
        .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
        }
        .alert {
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-success {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }
        p {
            font-size: 14px;
            color: #4b5563;
            margin: 8px 0;
        }
        .actions {
            margin-top: 16px;
        }
        .btn {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, transform .1s, box-shadow .15s;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.3);
        }
        .btn-secondary {
            margin-top: 8px;
            background: #e5e7eb;
            color: #111827;
        }
        .btn-secondary:hover {
            background: #d1d5db;
            transform: translateY(-1px);
        }
        .footer-text {
            margin-top: 16px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .footer-text strong {
            color: #111827;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <div class="logo">
            <div class="logo-badge">TP</div>
            <div>
                <div style="font-weight:600;font-size:15px;">TaPrestation</div>
                <div style="font-size:11px;color:#6b7280;">Plateforme de services & prestations</div>
            </div>
        </div>

        <h1>Vérifiez votre adresse e-mail</h1>
        <div class="subtitle">
            Nous avons besoin de confirmer que cette adresse e-mail vous appartient.
        </div>

        @if (session('status') === 'verification-link-sent' || session('resent'))
            <div class="alert alert-success">
                Un nouveau lien de vérification vient d’être envoyé à votre adresse e-mail.
            </div>
        @else
            <div class="alert alert-info">
                Un lien de vérification vous a été envoyé après votre inscription.
            </div>
        @endif

        <p>
            Avant de continuer, merci de vérifier vos e-mails (boîte de réception et spam).
        </p>
        <p>
            Si vous n’avez pas reçu l’e-mail, vous pouvez en demander un nouveau en cliquant sur le bouton ci-dessous.
        </p>

        <div class="actions">
            <form method="POST" action="{{ route('verification.send') }}" style="margin:0 0 8px;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Renvoyer le lien de vérification
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    Se déconnecter
                </button>
            </form>
        </div>

        <div class="footer-text">
            <strong>Astuce :</strong> une fois votre e-mail vérifié, reconnectez-vous pour accéder à votre tableau de bord.
        </div>
    </div>
</div>
</body>
</html>
