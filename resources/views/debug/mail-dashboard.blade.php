<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📧 Email Dashboard - TaPrestation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #667eea;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn {
            background: white;
            border: none;
            padding: 15px 25px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: #667eea;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .config-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .config-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .config-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .config-item strong {
            color: #667eea;
        }

        .config-item code {
            background: #333;
            color: #0f0;
            padding: 5px 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
        }

        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .preview-frame {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
        }

        .tips {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .tips h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .tips ul {
            margin-left: 20px;
            color: #856404;
        }

        .tips li {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📧 Email Testing Dashboard</h1>
            <p>Testez et prévisualisez vos emails en environnement local</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Configuration actuelle</h3>
                <div class="value">{{ config('mail.default') }}</div>
                <span class="status {{ config('mail.default') === 'log' ? 'active' : 'inactive' }}">
                    {{ config('mail.default') === 'log' ? 'Active' : 'Autre' }}
                </span>
            </div>

            <div class="stat-card">
                <h3>Environnement</h3>
                <div class="value">{{ app()->environment() }}</div>
                <span class="status {{ app()->environment('local') ? 'active' : 'inactive' }}">
                    {{ app()->environment('local') ? 'Local' : 'Autre' }}
                </span>
            </div>

            <div class="stat-card">
                <h3>Laravel Version</h3>
                <div class="value">{{ app()->version() }}</div>
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('mail.preview') }}" target="_blank" class="btn btn-primary">
                👁️ Prévisualiser Email de Vérification
            </a>

            <a href="{{ route('mail.send-test') }}" target="_blank" class="btn">
                📤 Envoyer Email de Test
            </a>

            <a href="{{ route('mail.list') }}" target="_blank" class="btn">
                📋 Liste des Emails Envoyés
            </a>

            <button onclick="window.location.href='{{ url('/register') }}'" class="btn">
                📝 Tester l'Inscription
            </button>
        </div>

        <div class="config-section">
            <h2>⚙️ Configuration Email</h2>

            <div class="config-item">
                <strong>MAIL_MAILER</strong>
                <code>{{ config('mail.default') }}</code>
            </div>

            <div class="config-item">
                <strong>MAIL_FROM_ADDRESS</strong>
                <code>{{ config('mail.from.address') }}</code>
            </div>

            <div class="config-item">
                <strong>MAIL_FROM_NAME</strong>
                <code>{{ config('mail.from.name') }}</code>
            </div>

            <div class="config-item">
                <strong>Logs Location</strong>
                <code>{{ storage_path('logs/laravel.log') }}</code>
            </div>
        </div>

        <div class="config-section">
            <h2>🚀 Configuration Optimale Laravel 12</h2>
            <p style="margin-bottom: 20px; color: #666;">
                Pour une expérience optimale en développement local, voici les configurations recommandées :
            </p>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px; color: #667eea;">Option 1 : Mailtrap (Recommandé)</h3>
                <pre style="background: #333; color: #0f0; padding: 15px; border-radius: 5px; overflow-x: auto;">
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls</pre>
                <p style="margin-top: 10px; color: #666;">
                    ✅ Interface web élégante<br>
                    ✅ Voir les emails en temps réel<br>
                    ✅ Tester les liens de vérification<br>
                    ✅ Gratuit jusqu'à 500 emails/mois
                </p>
                <a href="https://mailtrap.io" target="_blank" class="btn btn-primary"
                    style="display: inline-block; margin-top: 15px;">
                    🔗 Créer un compte Mailtrap (Gratuit)
                </a>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
                <h3 style="margin-bottom: 15px; color: #667eea;">Option 2 : Log (Actuel)</h3>
                <pre style="background: #333; color: #0f0; padding: 15px; border-radius: 5px; overflow-x: auto;">
MAIL_MAILER=log
MAIL_LOG_CHANNEL=stack</pre>
                <p style="margin-top: 10px; color: #666;">
                    ✅ Aucune configuration externe<br>
                    ✅ Emails dans storage/logs/laravel.log<br>
                    ⚠️ Moins pratique pour visualiser
                </p>
            </div>

            <div class="tips">
                <h4>💡 Conseils</h4>
                <ul>
                    <li>Utilisez <strong>Mailtrap</strong> pour une expérience optimale en développement</li>
                    <li>Après avoir configuré Mailtrap, exécutez : <code>php artisan config:clear</code></li>
                    <li>Testez l'inscription pour voir les emails en action</li>
                    <li>Les routes /_mail/* sont uniquement disponibles en local</li>
                </ul>
            </div>
        </div>

        <div class="preview-frame">
            <h2 style="margin-bottom: 20px; color: #333;">👁️ Prévisualisation de l'Email de Vérification</h2>
            <iframe src="{{ route('mail.preview') }}" frameborder="0"></iframe>
        </div>
    </div>
</body>

</html>