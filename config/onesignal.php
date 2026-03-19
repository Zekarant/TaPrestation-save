<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OneSignal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les notifications push via OneSignal
    |
    */

    'app_id' => env('ONESIGNAL_APP_ID', ''),
    'rest_api_key' => env('ONESIGNAL_REST_API_KEY', ''),
    
    // URL de l'API OneSignal
    'api_url' => 'https://onesignal.com/api/v1/notifications',
    
    // Activer/désactiver OneSignal
    'enabled' => env('ONESIGNAL_ENABLED', true),

    // Domaine autorisé pour initialiser le SDK Web OneSignal.
    // Utile quand APP_URL est en www mais OneSignal n'autorise que le non-www (ou inversement).
    // Exemple: ONESIGNAL_ALLOWED_HOST=taprestation.com
    'allowed_host' => env('ONESIGNAL_ALLOWED_HOST', null),
    
    // Safari Web Push ID (nécessaire pour Safari macOS et iOS PWA 16.4+)
    // Récupérez-le depuis OneSignal Dashboard > Settings > Safari Web Push
    'safari_web_id' => env('ONESIGNAL_SAFARI_WEB_ID', ''),
];
