<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe - TaPrestation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #1e40af;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background-color: #2563eb;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }
        .content {
            padding: 30px;
        }
        .button {
            display: inline-block;
            background-color: #1e40af;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background-color: #f0f0f0;
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo-icon">
                    <!-- Handshake icon as inline SVG for email compatibility -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4.5 12.5l3 3a.5.5 0 00.76-.04l3.04-4.56a.5.5 0 01.76-.04l2.74 3.5"></path>
                        <path d="M4 15.5l-.5-3a.5.5 0 01.22-.46l2.78-1.85a.5.5 0 00.15-.74l-1.5-2.5a.5.5 0 01.12-.65l2.5-2.5a.5.5 0 01.71 0l3.5 3.5a.5.5 0 00.71 0l2.5-2.5a.5.5 0 01.71 0l2.5 2.5a.5.5 0 01.12.65l-1.5 2.5a.5.5 0 00.15.74l2.78 1.85a.5.5 0 01.22.46l-.5 3a.5.5 0 01-.22.4l-2.78 1.85a.5.5 0 00-.15.74l1.5 2.5a.5.5 0 01-.12.65l-2.5 2.5a.5.5 0 01-.71 0l-3.5-3.5a.5.5 0 00-.71 0l-2.5 2.5a.5.5 0 01-.71 0l-2.5-2.5a.5.5 0 01-.12-.65l1.5-2.5a.5.5 0 00-.15-.74L4.22 15.9a.5.5 0 01-.22-.4z"></path>
                    </svg>
                </div>
                <div class="logo-text">TaPrestation</div>
            </div>
            <h1>Réinitialisation de mot de passe</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Bonjour {{ $user->name }},</p>
            
            <p class="message">
                Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte <strong>TaPrestation</strong>.
            </p>
            
            <div style="text-align: center;">
                <a href="{{ $url }}" class="button" style="color: white; text-decoration: none;">Réinitialiser mon mot de passe</a>
            </div>
            
            <p class="message">
                Ce lien de réinitialisation de mot de passe expirera dans {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60) }} minutes.
            </p>
            
            <p class="message">
                Si vous n'arrivez pas à cliquer sur le bouton, vous pouvez copier et coller le lien suivant dans votre navigateur :
                <br>
                <a href="{{ $url }}" style="color: #1e40af; word-break: break-all;">{{ $url }}</a>
            </p>
            
            <p class="message">
                Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune autre action n'est requise.
            </p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} TaPrestation. Tous droits réservés.</p>
            <p>
                Vous recevez cet e-mail car vous vous êtes inscrit sur notre plateforme.<br>
                Adresse : 123 Rue de la Plateforme, 75000 Paris, France
            </p>
        </div>
    </div>
</body>
</html>