<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:client,prestataire', 'profile.complete'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Client\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [\App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/personal', [\App\Http\Controllers\Client\ProfileController::class, 'updatePersonalInfo'])->name('profile.update.personal');
    Route::put('/profile/security', [\App\Http\Controllers\Client\ProfileController::class, 'updateSecurity'])->name('profile.update.security');
    Route::delete('/profile/delete-avatar', [\App\Http\Controllers\Client\ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::delete('/profile/destroy', [\App\Http\Controllers\Client\ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/bookings', [App\Http\Controllers\BookingController::class, 'clientBookings'])->name('bookings.index');
    Route::get('/bookings/{booking}', [App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings-history', [App\Http\Controllers\BookingController::class, 'clientHistory'])->name('bookings.history');

    Route::get('/favorites', [App\Http\Controllers\ClientController::class, 'favorites'])->name('favorites');
    Route::post('/favorites/{prestataire}', [App\Http\Controllers\ClientController::class, 'toggleFavorite'])->name('favorites.toggle');

    Route::get('/follows', [App\Http\Controllers\ClientController::class, 'follows'])->name('follows.index');

    // Suivi prestataires
    Route::post('/prestataire-follows/{prestataire}/follow', [\App\Http\Controllers\Client\PrestataireFollowController::class, 'follow'])->name('prestataire-follows.follow');
    Route::delete('/prestataire-follows/{prestataire}/unfollow', [\App\Http\Controllers\Client\PrestataireFollowController::class, 'unfollow'])->name('prestataire-follows.unfollow');
    Route::get('/prestataire-follows', [\App\Http\Controllers\Client\PrestataireFollowController::class, 'index'])->name('prestataire-follows.index');

    // Messagerie unifiée
    Route::get('messaging', [\App\Http\Controllers\MessagingController::class, 'index'])->name('messaging.index');
    Route::get('messaging/unread-count', [App\Http\Controllers\MessageController::class, 'getUnreadCount'])->name('messaging.unread-count');
    Route::get('messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'show'])->whereNumber('user')->name('messaging.show');
    Route::post('messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'store'])->whereNumber('user')->name('messaging.store');
    Route::delete('messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'deleteConversation'])->whereNumber('user')->name('messaging.delete');
    Route::get('messaging/start/{prestataire}', [\App\Http\Controllers\MessagingController::class, 'startConversationWithPrestataire'])->name('messaging.start');
    Route::get('messaging/start-conversation-from-request/{clientRequestId}', [\App\Http\Controllers\MessagingController::class, 'startConversationFromRequest'])->name('messaging.start-conversation-from-request');
    if (app()->environment('local')) {
        Route::get('messaging-test', function () {
            return view('messaging.test');
        })->name('messaging.test');
    }

    // Navigation prestataires
    Route::get('browse/prestataires', [\App\Http\Controllers\Client\PrestataireController::class, 'index'])->name('browse.prestataires');
    Route::get('browse/prestataire/{prestataire}', [\App\Http\Controllers\Client\PrestataireController::class, 'show'])->name('browse.prestataire');
    Route::get('browse/prestataires/{prestataire}', [\App\Http\Controllers\Client\PrestataireController::class, 'show'])->name('browse.prestataires.show');
    Route::get('prestataires', [\App\Http\Controllers\Client\PrestataireController::class, 'index'])->name('prestataires.index');
    Route::get('prestataires/{prestataire}', [\App\Http\Controllers\Client\PrestataireController::class, 'show'])->name('prestataires.show');

    // Actualités client
    Route::get('/news', [\App\Http\Controllers\Client\NewsController::class, 'index'])->name('news.index');

    // Aide client
    Route::get('/help', [\App\Http\Controllers\Client\HelpController::class, 'index'])->name('help.index');

    // Location matériel côté client
    Route::prefix('equipment-rental-requests')->name('equipment-rental-requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Client\EquipmentRentalRequestController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Client\EquipmentRentalRequestController::class, 'store'])->name('store');
        Route::post('/{request}/cancel', [\App\Http\Controllers\Client\EquipmentRentalRequestController::class, 'cancel'])->name('cancel');
        Route::get('/{request}', [\App\Http\Controllers\Client\EquipmentRentalRequestController::class, 'show'])->name('show');
        Route::delete('/{request}', [\App\Http\Controllers\Client\EquipmentRentalRequestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('equipment-rentals')->name('equipment-rentals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Client\EquipmentRentalController::class, 'index'])->name('index');
        Route::get('/{rental}', [\App\Http\Controllers\Client\EquipmentRentalController::class, 'show'])->name('show');
        Route::post('/{rental}/confirm-receipt', [\App\Http\Controllers\Client\EquipmentRentalController::class, 'confirmReceipt'])->name('confirm-receipt');
        Route::post('/{rental}/confirm-return', [\App\Http\Controllers\Client\EquipmentRentalController::class, 'confirmReturn'])->name('confirm-return');
        Route::post('/{rental}/review', [\App\Http\Controllers\Client\EquipmentRentalController::class, 'review'])->name('review');
    });

    // ============================================================================
    // 15 NOUVELLES FONCTIONNALITÉS INTÉGRÉES POUR CLIENT
    // ============================================================================

    // 1. Paiements - Transaction History & Payment Management
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Payment\PaymentController::class, 'clientIndex'])->name('index');
        Route::get('/transactions/{transaction}', [App\Http\Controllers\Payment\PaymentController::class, 'clientShow'])->name('show');
        Route::get('/booking/{booking}', [App\Http\Controllers\Payment\PaymentController::class, 'showPaymentForm'])->name('form');
        Route::post('/booking/{booking}/intent', [App\Http\Controllers\Payment\PaymentController::class, 'createPaymentIntent'])->middleware('throttle:10,1')->name('intent');
        Route::post('/booking/{booking}/confirm', [App\Http\Controllers\Payment\PaymentController::class, 'confirmPayment'])->middleware('throttle:20,1')->name('confirm');
        Route::post('/booking/{booking}/refund', [App\Http\Controllers\Payment\PaymentController::class, 'requestRefund'])->middleware('throttle:5,1')->name('refund');
        Route::get('/history', [App\Http\Controllers\Payment\PaymentController::class, 'transactionHistory'])->name('history');

        // Equipment rental request payment (Stripe)
        Route::get('/equipment-rental-request/{request}', [\App\Http\Controllers\Payment\EquipmentRentalRequestPaymentController::class, 'show'])->name('rental.form');
        Route::post('/equipment-rental-request/{request}/intent', [\App\Http\Controllers\Payment\EquipmentRentalRequestPaymentController::class, 'createIntent'])->middleware('throttle:10,1')->name('rental.intent');
        Route::post('/equipment-rental-request/{request}/confirm', [\App\Http\Controllers\Payment\EquipmentRentalRequestPaymentController::class, 'confirm'])->middleware('throttle:20,1')->name('rental.confirm');

        // Cart checkout (Stripe) - Moved to shared cart routes above for client+prestataire access
    });

    // 2. Abonnements - Subscription Management
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Subscription\SubscriptionController::class, 'clientIndex'])->name('index');
        Route::get('/plans', [App\Http\Controllers\Subscription\SubscriptionController::class, 'showPlans'])->name('plans');
        Route::post('/plan/{plan}/subscribe', [App\Http\Controllers\Subscription\SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/cancel', [App\Http\Controllers\Subscription\SubscriptionController::class, 'cancel'])->name('cancel');
        Route::get('/my-subscription', [App\Http\Controllers\Subscription\SubscriptionController::class, 'mySubscription'])->name('my');
    });

    // 3. Enchères - Auction & Bidding System
    Route::prefix('auctions')->name('auctions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Auction\AuctionController::class, 'clientIndex'])->name('index');
        Route::get('/service/{service}', [App\Http\Controllers\Auction\AuctionController::class, 'showBids'])->name('bids');
        Route::post('/service/{service}/bid', [App\Http\Controllers\Auction\AuctionController::class, 'placeBid'])->name('place-bid');
        Route::post('/bid/{bid}/accept', [App\Http\Controllers\Auction\AuctionController::class, 'acceptBid'])->name('accept');
        Route::post('/bid/{bid}/reject', [App\Http\Controllers\Auction\AuctionController::class, 'rejectBid'])->name('reject');
        Route::get('/my-bids', [App\Http\Controllers\Auction\AuctionController::class, 'myBids'])->name('my-bids');
        Route::get('/stats', [App\Http\Controllers\Auction\AuctionController::class, 'stats'])->name('stats');
    });

    // 4. Livraison - Delivery Management
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/', [App\Http\Controllers\Delivery\DeliveryController::class, 'clientIndex'])->name('index');
        Route::get('/providers', [App\Http\Controllers\Delivery\DeliveryController::class, 'getAvailableProviders'])->name('providers');
        Route::post('/calculate', [App\Http\Controllers\Delivery\DeliveryController::class, 'calculateShipping'])->name('calculate');
        Route::post('/track', [App\Http\Controllers\Delivery\DeliveryController::class, 'track'])->name('track');
        Route::get('/orders', [App\Http\Controllers\Delivery\DeliveryController::class, 'myOrders'])->name('orders');
        Route::post('/booking/{booking}/setup', [App\Http\Controllers\Delivery\DeliveryController::class, 'setupDelivery'])->name('setup');
    });

    // 5. Carnet d'adresses - Address Book Management
    Route::prefix('address-book')->name('address-book.')->group(function () {
        Route::get('/', [App\Http\Controllers\Address\AddressBookController::class, 'clientIndex'])->name('index');
        Route::post('/add', [App\Http\Controllers\Address\AddressBookController::class, 'store'])->name('store');
        Route::put('/{address}', [App\Http\Controllers\Address\AddressBookController::class, 'update'])->name('update');
        Route::delete('/{address}', [App\Http\Controllers\Address\AddressBookController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [App\Http\Controllers\Address\AddressBookController::class, 'setDefault'])->name('set-default');
    });

    // 6. Inventaire - Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [App\Http\Controllers\Inventory\InventoryController::class, 'clientIndex'])->name('index');
        Route::get('/analytics', [App\Http\Controllers\Inventory\InventoryController::class, 'analytics'])->name('analytics');
        Route::post('/export', [App\Http\Controllers\Inventory\InventoryController::class, 'export'])->name('export');
    });

    // 7. Paramètres de Notifications - Notification Settings
    Route::prefix('notification-settings')->name('notification-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'clientIndex'])->name('index');
        Route::put('/', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'update'])->name('update');
        Route::post('/test-email', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'testEmail'])->name('test-email');
        Route::post('/test-sms', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'testSms'])->name('test-sms');
        Route::post('/test-push', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'testPush'])->name('test-push');
        Route::post('/quiet-hours', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'updateQuietHours'])->name('quiet-hours');
    });

    // 8. Factures - Client Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [App\Http\Controllers\Client\InvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [App\Http\Controllers\Client\InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [App\Http\Controllers\Client\InvoiceController::class, 'download'])->name('download');
    });

    // 9. Mes annonces vente flash (client - limite 5, sans paiement en ligne)
    Route::prefix('my-urgent-sales')->name('my-urgent-sales.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'store'])->name('store');
        Route::get('/{urgentSale}', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'show'])->name('show');
        Route::get('/{urgentSale}/edit', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'edit'])->name('edit');
        Route::put('/{urgentSale}', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'update'])->name('update');
        Route::patch('/{urgentSale}/mark-sold', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'markSold'])->name('mark-sold');

        // Permet de migrer une annonce client vers l'espace prestataire (si l'utilisateur a un prestataire actif)
        Route::post('/{id}/migrate', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'migrateToPrestataire'])->name('migrate-to-prestataire');
        Route::delete('/{urgentSale}', [\App\Http\Controllers\Client\ClientUrgentSaleController::class, 'destroy'])->name('destroy');
    });
});
