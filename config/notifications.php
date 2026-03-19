<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Channels Configuration
    |--------------------------------------------------------------------------
    */

    'channels' => [
        'email' => env('MAIL_DRIVER', 'smtp'),
        'sms' => env('SMS_DRIVER', 'twilio'),
        'push' => env('PUSH_DRIVER', 'fcm'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration (Twilio)
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
        'enabled' => env('TWILIO_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notifications Configuration (Firebase Cloud Messaging)
    |--------------------------------------------------------------------------
    */

    'fcm' => [
        'api_key' => env('FCM_API_KEY'),
        'enabled' => env('FCM_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Templates
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'booking_confirmed' => true,
        'booking_rejected' => true,
        'payment_received' => true,
        'payment_failed' => true,
        'message_received' => true,
        'review_received' => true,
        'equipment_available' => true,
        'price_alert' => true,
        'auction_outbid' => true,
        'auction_won' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Frequency
    |--------------------------------------------------------------------------
    */

    'frequency' => [
        'immediate' => ['payment_received', 'booking_confirmed'],
        'daily_digest' => ['message_received', 'review_received'],
        'weekly_digest' => ['price_alert', 'equipment_available'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Hours (quiet hours)
    |--------------------------------------------------------------------------
    */

    'quiet_hours' => [
        'start' => 22, // 10 PM
        'end' => 8,    // 8 AM
    ],
];
