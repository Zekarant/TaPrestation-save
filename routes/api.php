<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Notifications\NotificationSettingsController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\Api\DriverApiController;

Route::middleware('auth:sanctum')->group(function () {
    // Notification settings
    Route::middleware('throttle:30,1')->group(function () {
        Route::get('/notification-settings', [NotificationSettingsController::class, 'apiShow']);
        Route::put('/notification-settings', [NotificationSettingsController::class, 'update']);
        Route::post('/notification-settings/quiet-hours', [NotificationSettingsController::class, 'updateQuietHours']);
    });

    // Payments
    Route::post('/bookings/{booking}/payment-intents', [PaymentController::class, 'createPaymentIntent'])->middleware('throttle:10,1');
    Route::post('/bookings/{booking}/confirm-payment', [PaymentController::class, 'confirmPayment'])->middleware('throttle:20,1');
    Route::get('/payments/transactions', [PaymentController::class, 'apiTransactions']);

    // Delivery
    Route::prefix('delivery')->middleware('throttle:20,1')->group(function () {
        Route::post('/providers', [DeliveryController::class, 'getAvailableProviders']);
        Route::post('/calculate', [DeliveryController::class, 'calculateShipping']);
        Route::post('/track', [DeliveryController::class, 'track']);
        Route::post('/booking/{booking}/setup', [DeliveryController::class, 'setupDelivery']);
    });

    // Driver API (app livreur)
    Route::prefix('driver')->group(function () {
        // GPS - toutes les 10 secondes
        Route::post('/location', [DriverApiController::class, 'updateLocation'])->middleware('throttle:120,1');

        // Courses disponibles
        Route::middleware('throttle:30,1')->group(function () {
            Route::get('/deliveries', [DriverApiController::class, 'getAvailableDeliveries']);
            Route::get('/deliveries/{batch}', [DriverApiController::class, 'getDeliveryDetails']);
            Route::post('/deliveries/{batch}/accept', [DriverApiController::class, 'acceptDelivery']);
        });
    });
});
