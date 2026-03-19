<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1;
        }
        h1 {
            color: #1f2937;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .content {
            color: #4b5563;
            font-size: 16px;
            margin-bottom: 25px;
        }
        .content p {
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
        }
        .button:hover {
            opacity: 0.9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎯 TaPrestation</div>
        </div>
        
        <h1>{{ $title }}</h1>
        
        <div class="content">
            <p>Bonjour {{ $user->name }},</p>
            
            {!! nl2br(e($messageContent)) !!}
        </div>
        
        @if($actionUrl)
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
        </div>
        @endif
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par TaPrestation.</p>
            <p>© {{ date('Y') }} TaPrestation - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>
