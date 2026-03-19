<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Configuration
    |--------------------------------------------------------------------------
    */

    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    // Simulation mode: when true, returns fake transfer/payout IDs instead of calling Stripe
    // IMPORTANT: blocked automatically in production by App\Services\Payment\StripeService
    'simulate' => env('STRIPE_SIMULATE', false),

    /*
    |--------------------------------------------------------------------------
    | Stripe Connect Configuration (for Prestataires)
    |--------------------------------------------------------------------------
    */

    'connect' => [
        'enabled' => env('STRIPE_CONNECT_ENABLED', true),
        'application_fee_percent' => env('STRIPE_APP_FEE_PERCENT', 5), // 5% platform fee
        'minimum_payout' => env('STRIPE_MINIMUM_PAYOUT', 2500), // €25 minimum
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Intents Configuration
    |--------------------------------------------------------------------------
    */

    'intents' => [
        'currency' => env('STRIPE_CURRENCY', 'eur'),
        'statement_descriptor' => 'TaPrestation Service',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'payment_intent.succeeded' => true,
        'payment_intent.payment_failed' => true,
        'customer.subscription.created' => true,
        'customer.subscription.updated' => true,
        'customer.subscription.deleted' => true,
        'charge.refunded' => true,
        'charge.dispute.created' => true,
        'charge.dispute.closed' => true,
        'transfer.failed' => true,
        'payout.failed' => true,
        'account.updated' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */

    'retries' => [
        'max_attempts' => 3,
        'delay' => 300, // 5 minutes
    ],
];
