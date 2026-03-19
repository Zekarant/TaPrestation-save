<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Prestataire - Routes de paiement d'abonnement (SANS middleware subscription)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:prestataire'])->prefix('prestataire')->name('prestataire.')->group(function () {
    Route::get('/subscription/payment', [App\Http\Controllers\Subscription\SubscriptionController::class, 'showPaymentPage'])->name('subscription.payment');
    Route::post('/subscription/process-payment', [App\Http\Controllers\Subscription\SubscriptionController::class, 'processPayment'])->name('subscription.process-payment');
});

/*
|--------------------------------------------------------------------------
| Prestataire area
|--------------------------------------------------------------------------
*/

Route::middleware(['role:prestataire', 'profile.complete', 'subscription'])->prefix('prestataire')->name('prestataire.')->group(function () {
    Route::get('videos-manage', [App\Http\Controllers\Prestataire\VideoController::class, 'manage'])->name('videos.manage');

    Route::put('availability/update-weekly', [App\Http\Controllers\Prestataire\AvailabilityController::class, 'updateWeeklyAvailability'])->name('availability.updateWeekly');
    Route::resource('bookings', App\Http\Controllers\Prestataire\BookingController::class)->only(['index', 'show']);
    Route::get('/bookings-history', [App\Http\Controllers\BookingController::class, 'prestataireHistory'])->name('bookings.history');
    Route::patch('/bookings/{booking}/accept', [App\Http\Controllers\Prestataire\BookingController::class, 'accept'])->middleware('throttle:10,1')->name('bookings.accept');
    Route::patch('/bookings/{booking}/reject', [App\Http\Controllers\Prestataire\BookingController::class, 'reject'])->middleware('throttle:10,1')->name('bookings.reject');
    Route::patch('/bookings/{booking}/complete', [App\Http\Controllers\Prestataire\BookingController::class, 'complete'])->middleware('throttle:10,1')->name('bookings.complete.prestataire');
    Route::post('/bookings/{booking}/confirm-cash', [App\Http\Controllers\Prestataire\BookingController::class, 'confirmCashPayment'])->middleware('throttle:10,1')->name('bookings.confirm-cash');

    Route::resource('agenda', App\Http\Controllers\Prestataire\AgendaController::class)->only(['index']);
    Route::get('/dashboard', [\App\Http\Controllers\Prestataire\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [\App\Http\Controllers\Prestataire\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Prestataire\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/personal', [\App\Http\Controllers\Prestataire\ProfileController::class, 'updatePersonalInfo'])->name('profile.update.personal');
    Route::put('/profile/security', [\App\Http\Controllers\Prestataire\ProfileController::class, 'updateSecurity'])->name('profile.update.security');
    Route::delete('/profile/photo', [\App\Http\Controllers\Prestataire\ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
    Route::delete('/profile/destroy', [\App\Http\Controllers\Prestataire\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/preview', [\App\Http\Controllers\Prestataire\ProfileController::class, 'preview'])->name('profile.preview');
    Route::get('/profile/{prestataire}', [\App\Http\Controllers\Prestataire\ProfileController::class, 'show'])->name('profile');

    // Dashboard Paiements Unifié (remplace les anciens)
    Route::prefix('payments')->name('payments.')->group(function () {
        // Dashboard unifié principal
        Route::get('/', [\App\Http\Controllers\Prestataire\PaymentDashboardController::class, 'index'])->name('index');
        Route::get('/unified', [\App\Http\Controllers\Prestataire\PaymentDashboardController::class, 'index'])->name('unified');
        Route::get('/unified/export', [\App\Http\Controllers\Prestataire\PaymentDashboardController::class, 'export'])->name('unified.export');
        Route::get('/unified/{type}/{id}', [\App\Http\Controllers\Prestataire\PaymentDashboardController::class, 'show'])->name('unified.show');

        // Anciennes routes pour compatibilité
        Route::get('/history', [\App\Http\Controllers\Payment\PaymentController::class, 'prestataireHistory'])->name('history');
        Route::post('/withdraw', [\App\Http\Controllers\Payment\PaymentController::class, 'prestataireWithdraw'])->middleware('throttle:5,1')->name('withdraw');
    });

    // Factures Prestataire (Relevés de vente)
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\InvoiceController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Prestataire\InvoiceController::class, 'export'])->name('export');
        Route::get('/{invoice}', [\App\Http\Controllers\Prestataire\InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [\App\Http\Controllers\Prestataire\InvoiceController::class, 'download'])->name('download');
    });

    // Vérification prestataire (KYC)
    Route::prefix('verification')->name('verification.')->group(function () {
        Route::get('/', [App\Http\Controllers\Prestataire\VerificationController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Prestataire\VerificationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Prestataire\VerificationController::class, 'store'])->name('store');
        Route::get('/{verificationRequest}', [App\Http\Controllers\Prestataire\VerificationController::class, 'show'])->name('show');
        Route::get('/{verificationRequest}/document/{documentIndex}', [App\Http\Controllers\Prestataire\VerificationController::class, 'downloadDocument'])->name('download-document');
        Route::post('/check-automatic', [App\Http\Controllers\Prestataire\VerificationController::class, 'checkAutomaticCriteria'])->name('check-automatic');
    });

    // Services prestataire
    Route::resource('services', \App\Http\Controllers\Prestataire\ServiceController::class);

    // Wizard steps are defined in wizards.php (lighter middleware for wizard access)

    Route::get('services/{service}/availabilities', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'index'])->name('availabilities.index');
    Route::post('services/{service}/availabilities', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'store'])->name('availabilities.store');
    Route::delete('availabilities/{availability}', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'destroy'])->name('availabilities.destroy');
    Route::delete('/services/images/{image}', [App\Http\Controllers\Prestataire\ServiceImageController::class, 'destroy'])->name('services.images.destroy');

    Route::prefix('availability')->name('availability.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'index'])->name('index');
        Route::get('/events', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'events'])->name('events');
        Route::post('/update-settings', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'updateBookingSettings'])->name('update-settings');
    });

    Route::get('/calendar', [App\Http\Controllers\PrestataireController::class, 'calendar'])->name('calendar');
    Route::get('/visibility', [App\Http\Controllers\PrestataireController::class, 'visibility'])->name('visibility');

    // Messagerie prestataire (redirigée vers MessagingController)
    Route::get('/messages', [\App\Http\Controllers\MessagingController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [\App\Http\Controllers\MessagingController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [\App\Http\Controllers\MessagingController::class, 'store'])->name('messages.store');
    Route::post('/messages/send-ajax', [\App\Http\Controllers\MessageController::class, 'sendAjax'])->name('messages.send-ajax');
    Route::post('/messages/start-conversation/{user}', [\App\Http\Controllers\MessagingController::class, 'startConversation'])->name('messages.start-conversation');

    // Gestion des équipements prestataire
    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'store'])->name('store');

        Route::delete('/{equipment}/photos/{photoIndex}', [\App\Http\Controllers\Prestataire\EquipmentImageController::class, 'destroy'])->name('photos.destroy');

        // Wizard steps are defined in wizards.php (lighter middleware for wizard access)

        Route::get('/{equipment}', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'show'])->name('show');
        Route::get('/{equipment}/edit', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'edit'])->name('edit');
        Route::put('/{equipment}', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'destroy'])->name('destroy');
        Route::post('/{equipment}/toggle-status', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Demandes de location d'équipement (prestataire)
    Route::prefix('equipment-rental-requests')->name('equipment-rental-requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\EquipmentRentalRequestController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Prestataire\EquipmentRentalRequestController::class, 'show'])->name('show');
        Route::patch('/{request}/accept', [\App\Http\Controllers\Prestataire\EquipmentRentalRequestController::class, 'accept'])->name('accept');
        Route::patch('/{request}/reject', [\App\Http\Controllers\Prestataire\EquipmentRentalRequestController::class, 'reject'])->name('reject');
        Route::post('/{rentalRequest}/cancel', [\App\Http\Controllers\Prestataire\EquipmentRentalRequestController::class, 'cancel'])->name('cancel');
    });

    // Locations d'équipement (prestataire)
    Route::prefix('equipment-rentals')->name('equipment-rentals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\EquipmentRentalController::class, 'index'])->name('index');
        Route::get('/{rental}', [\App\Http\Controllers\Prestataire\EquipmentRentalController::class, 'show'])->name('show');
        Route::post('/{rental}/start', [\App\Http\Controllers\Prestataire\EquipmentRentalController::class, 'start'])->name('start');
        Route::post('/{rental}/complete', [\App\Http\Controllers\Prestataire\EquipmentRentalController::class, 'complete'])->name('complete');
        Route::post('/{rental}/report-issue', [\App\Http\Controllers\Prestataire\EquipmentRentalController::class, 'reportIssue'])->name('report-issue');
    });

    // Annonces urgentes (prestataire)
    Route::prefix('urgent-sales')->name('urgent-sales.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'create'])->name('create');
        Route::get('/select-inventory', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'selectFromInventory'])->name('select-inventory');
        Route::get('/from-inventory/{id}', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createFromInventoryItem'])->name('from-inventory');
        Route::get('/from-equipment/{id}', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createFromEquipment'])->name('from-equipment');
        Route::get('/subcategories/{categoryId}', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'getSubcategories'])->name('subcategories');
        Route::post('/', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'store'])->name('store');
        Route::get('/{urgentSale}', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'show'])->name('show');
        Route::get('/{urgentSale}/edit', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'edit'])->name('edit');
        Route::put('/{urgentSale}', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'update'])->name('update');
        Route::delete('/{urgentSale}', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'destroy'])->name('destroy');
        Route::post('/{urgentSale}/update-status', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'updateStatus'])->name('update-status');
        Route::get('/{urgentSale}/contacts', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'contacts'])->name('contacts');
        Route::post('/contacts/{contact}/respond', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'respondToContact'])->name('contacts.respond');
        Route::patch('/contacts/{contact}/accept', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'acceptContact'])->name('contacts.accept');
        Route::patch('/contacts/{contact}/reject', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'rejectContact'])->name('contacts.reject');

        Route::delete('/{urgentSale}/photos/{photoIndex}', [\App\Http\Controllers\Prestataire\UrgentSaleImageController::class, 'destroy'])->name('photos.destroy');
        Route::get('/{urgentSale}/inventory', [\App\Http\Controllers\Prestataire\UrgentSaleReservationController::class, 'showInventory'])->name('inventory');
    });

    // Réservations ventes urgentes (prestataire)
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\UrgentSaleReservationController::class, 'index'])->name('index');
        Route::post('/{reservation}/confirm', [\App\Http\Controllers\Prestataire\UrgentSaleReservationController::class, 'confirm'])->name('confirm');
        Route::post('/{reservation}/cancel', [\App\Http\Controllers\Prestataire\UrgentSaleReservationController::class, 'cancel'])->name('cancel');
        Route::post('/{reservation}/complete', [\App\Http\Controllers\Prestataire\UrgentSaleReservationController::class, 'complete'])->name('complete');
        Route::post('/{reservation}/rate-client', [\App\Http\Controllers\Prestataire\UrgentSaleReservationController::class, 'rateClient'])->name('rate-client');
    });

    // Vidéos prestataire
    Route::prefix('videos')->name('videos.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\VideoController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Prestataire\VideoController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\Prestataire\VideoController::class, 'store'])->middleware('throttle:3,1')->name('store');
        Route::get('/{video}/stream', [\App\Http\Controllers\Prestataire\VideoController::class, 'stream'])->name('stream');
        Route::get('/create/step1', [\App\Http\Controllers\Prestataire\VideoController::class, 'createStep1'])->name('create.step1');
        Route::post('/create/step1', [\App\Http\Controllers\Prestataire\VideoController::class, 'storeStep1'])->middleware('throttle:3,1')->name('create.step1.store');
        Route::get('/create/step2', [\App\Http\Controllers\Prestataire\VideoController::class, 'createStep2'])->name('create.step2');
        Route::post('/create/step2', [\App\Http\Controllers\Prestataire\VideoController::class, 'storeStep2'])->middleware('throttle:3,1')->name('create.step2.store');
        Route::get('/{video}/edit', [\App\Http\Controllers\Prestataire\VideoController::class, 'edit'])->name('edit');
        Route::put('/{video}', [\App\Http\Controllers\Prestataire\VideoController::class, 'update'])->name('update')->middleware('check.file.upload');
        Route::delete('/{video}', [\App\Http\Controllers\Prestataire\VideoController::class, 'destroy'])->name('destroy');
    });

    // Aide prestataire
    Route::get('/help', [\App\Http\Controllers\Prestataire\HelpController::class, 'index'])->name('help.index');

    // QR Code prestataire
    Route::get('/qrcode', [App\Http\Controllers\QrCodeController::class, 'show'])->name('qrcode.show');

    // Agenda prestataire
    Route::get('/agenda', [\App\Http\Controllers\Prestataire\AgendaController::class, 'index'])->name('agenda.index');
    Route::get('/agenda/events', [\App\Http\Controllers\Prestataire\AgendaController::class, 'events'])->name('agenda.events');
    Route::post('/agenda/events', [\App\Http\Controllers\Prestataire\AgendaController::class, 'storeEvent'])->name('agenda.events.store');
    Route::delete('/agenda/events/{event}', [\App\Http\Controllers\Prestataire\AgendaController::class, 'destroyEvent'])->name('agenda.events.destroy');
    Route::get('/agenda/booking/{booking}', [\App\Http\Controllers\Prestataire\AgendaController::class, 'show'])->name('agenda.booking.show');
    Route::put('/agenda/booking/{booking}/status', [\App\Http\Controllers\Prestataire\AgendaController::class, 'updateStatus'])->name('agenda.booking.update-status');
    Route::get('/agenda/equipment-request/{rentalRequest}', [\App\Http\Controllers\Prestataire\AgendaController::class, 'showEquipmentRequest'])->name('agenda.equipment-request.show');
    Route::get('/agenda/equipment-rental/{rental}', [\App\Http\Controllers\Prestataire\AgendaController::class, 'showEquipmentRental'])->name('agenda.equipment-rental.show');
    Route::put('/agenda/equipment-request/{rentalRequest}/accept', [\App\Http\Controllers\Prestataire\AgendaController::class, 'acceptEquipmentRequest'])->name('agenda.equipment-request.accept');
    Route::put('/agenda/equipment-request/{rentalRequest}/reject', [\App\Http\Controllers\Prestataire\AgendaController::class, 'rejectEquipmentRequest'])->name('agenda.equipment-request.reject');

    // ============================================================================
    // 15 NOUVELLES FONCTIONNALITÉS INTÉGRÉES POUR PRESTATAIRE
    // ============================================================================

    // 1. Paiements - Routes supplémentaires (le dashboard unifié est défini plus haut)
    Route::prefix('payments')->name('payments.')->group(function () {
        // SÉCURITÉ H8: Les routes booking form/intent/confirm sont SUPPRIMÉES côté prestataire
        // Un prestataire ne doit PAS pouvoir initier/confirmer un paiement sur ses propres bookings
        // Seul le client (routes client.payments.*) peut le faire
        Route::get('/revenue', [\App\Http\Controllers\Prestataire\PaymentDashboardController::class, 'index'])->name('revenue');
        Route::get('/sales/{transaction}', [App\Http\Controllers\Payment\PaymentController::class, 'prestataireShow'])->name('sales.show');

        // Connexion comptes de paiement (Stripe Connect)
        Route::get('/connect', [\App\Http\Controllers\Prestataire\PaymentConnectController::class, 'index'])->name('connect');
        Route::get('/dashboard', [\App\Http\Controllers\Prestataire\PaymentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/stripe/connect', [\App\Http\Controllers\Prestataire\PaymentConnectController::class, 'stripeConnect'])->name('stripe.connect');
        Route::get('/stripe/callback', [\App\Http\Controllers\Prestataire\PaymentConnectController::class, 'stripeCallback'])->name('stripe.callback');
        Route::get('/stripe/complete', [\App\Http\Controllers\Prestataire\PaymentConnectController::class, 'stripeComplete'])->name('stripe.complete');
        Route::get('/stripe/dashboard', [\App\Http\Controllers\Prestataire\PaymentConnectController::class, 'stripeDashboard'])->name('stripe.dashboard');
        Route::post('/stripe/save', [\App\Http\Controllers\Prestataire\PaymentConnectController::class, 'saveStripeKeys'])->middleware('throttle:2,1')->name('stripe.save');
    });

    // 2. Abonnements - Subscription Plans Management
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Subscription\SubscriptionController::class, 'prestataireIndex'])->name('index');
        Route::get('/plans', [App\Http\Controllers\Subscription\SubscriptionController::class, 'showPlans'])->name('plans');
        Route::post('/plan/{plan}/subscribe', [App\Http\Controllers\Subscription\SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/cancel', [App\Http\Controllers\Subscription\SubscriptionController::class, 'cancel'])->name('cancel');
        Route::get('/my-subscription', [App\Http\Controllers\Subscription\SubscriptionController::class, 'mySubscription'])->name('my');
        Route::get('/benefits', [App\Http\Controllers\Subscription\SubscriptionController::class, 'prestataireIndex'])->name('benefits');
    });

    // 3. Enchères - Auction Bidding Management
    Route::prefix('auctions')->name('auctions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Auction\AuctionController::class, 'prestataireIndex'])->name('index');
        Route::get('/service/{service}', [App\Http\Controllers\Auction\AuctionController::class, 'showBids'])->name('bids');
        Route::post('/service/{service}/bid', [App\Http\Controllers\Auction\AuctionController::class, 'placeBid'])->name('place-bid');
        Route::post('/bid/{bid}/accept', [App\Http\Controllers\Auction\AuctionController::class, 'acceptBid'])->name('accept');
        Route::post('/bid/{bid}/reject', [App\Http\Controllers\Auction\AuctionController::class, 'rejectBid'])->name('reject');
        Route::get('/my-bids', [App\Http\Controllers\Auction\AuctionController::class, 'myBids'])->name('my-bids');
        Route::get('/stats', [App\Http\Controllers\Auction\AuctionController::class, 'stats'])->name('stats');
    });

    // 4. Livraison - Hub Unifié (Food, Ventes Urgentes, Services, Équipements)
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Prestataire\UnifiedDeliveryController::class, 'index'])->name('index');
        Route::post('/start/{type}/{id}', [\App\Http\Controllers\Prestataire\UnifiedDeliveryController::class, 'startDelivery'])->name('start');
        Route::post('/complete/{type}/{id}', [\App\Http\Controllers\Prestataire\UnifiedDeliveryController::class, 'completeDelivery'])->name('complete');
        // Legacy routes kept for compatibility
        Route::get('/legacy', [App\Http\Controllers\Delivery\DeliveryController::class, 'prestataireIndex'])->name('legacy');
        Route::get('/create', [App\Http\Controllers\Delivery\DeliveryController::class, 'create'])->name('create');
        Route::get('/providers', [App\Http\Controllers\Delivery\DeliveryController::class, 'getAvailableProviders'])->name('providers');
        Route::post('/calculate', [App\Http\Controllers\Delivery\DeliveryController::class, 'calculateShipping'])->name('calculate');
        Route::post('/track', [App\Http\Controllers\Delivery\DeliveryController::class, 'track'])->name('track');
        Route::get('/orders', [App\Http\Controllers\Delivery\DeliveryController::class, 'prestataireIndex'])->name('orders');
        Route::post('/booking/{booking}/setup', [App\Http\Controllers\Delivery\DeliveryController::class, 'setupDelivery'])->name('setup');
    });

    // 4.1 Logistique Avancée - Advanced Logistics System
    Route::prefix('logistics')->name('logistics.')->group(function () {
        // Dashboard & Overview
        Route::get('/', [\App\Http\Controllers\Delivery\LogisticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/deliveries', [\App\Http\Controllers\Delivery\LogisticsController::class, 'index'])->name('index');

        // Delivery CRUD
        Route::get('/new', [\App\Http\Controllers\Delivery\LogisticsController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\Delivery\LogisticsController::class, 'store'])->name('store');
        Route::get('/{delivery}', [\App\Http\Controllers\Delivery\LogisticsController::class, 'show'])->name('show');

        // Status Updates
        Route::post('/{delivery}/status', [\App\Http\Controllers\Delivery\LogisticsController::class, 'updateStatus'])->name('update-status');
        Route::post('/{delivery}/ready-for-pickup', [\App\Http\Controllers\Delivery\LogisticsController::class, 'markReadyForPickup'])->name('ready-for-pickup');
        Route::post('/{delivery}/picked-up', [\App\Http\Controllers\Delivery\LogisticsController::class, 'markPickedUp'])->name('picked-up');
        Route::post('/{delivery}/delivered', [\App\Http\Controllers\Delivery\LogisticsController::class, 'markDelivered'])->name('delivered');
        Route::post('/{delivery}/failed', [\App\Http\Controllers\Delivery\LogisticsController::class, 'markFailed'])->name('failed');
        Route::post('/{delivery}/cancel', [\App\Http\Controllers\Delivery\LogisticsController::class, 'cancel'])->name('cancel');

        // Driver Assignment
        Route::post('/{delivery}/assign-driver', [\App\Http\Controllers\Delivery\LogisticsController::class, 'assignDriver'])->name('assign-driver');
        Route::post('/{delivery}/auto-assign', [\App\Http\Controllers\Delivery\LogisticsController::class, 'autoAssignDriver'])->name('auto-assign');

        // Tracking
        Route::get('/{delivery}/tracking', [\App\Http\Controllers\Delivery\LogisticsController::class, 'trackingInfo'])->name('tracking-info');

        // Management
        Route::get('/manage/zones', [\App\Http\Controllers\Delivery\LogisticsController::class, 'zones'])->name('zones');
        Route::get('/manage/drivers', [\App\Http\Controllers\Delivery\LogisticsController::class, 'drivers'])->name('drivers');
        Route::get('/manage/reports', [\App\Http\Controllers\Delivery\LogisticsController::class, 'reports'])->name('reports');
    });

    // 5. Carnet d'adresses - Address Book Management
    Route::prefix('address-book')->name('address-book.')->group(function () {
        Route::get('/', [App\Http\Controllers\Address\AddressBookController::class, 'prestataireIndex'])->name('index');
        Route::post('/add', [App\Http\Controllers\Address\AddressBookController::class, 'store'])->name('store');
        Route::put('/{address}', [App\Http\Controllers\Address\AddressBookController::class, 'update'])->name('update');
        Route::delete('/{address}', [App\Http\Controllers\Address\AddressBookController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [App\Http\Controllers\Address\AddressBookController::class, 'setDefault'])->name('set-default');
    });

    // 6. Inventaire - Equipment/Service Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [App\Http\Controllers\Inventory\InventoryController::class, 'prestataireIndex'])->name('index');
        Route::get('/create', [App\Http\Controllers\Inventory\InventoryController::class, 'create'])->name('create');
        Route::get('/analytics', [App\Http\Controllers\Inventory\InventoryController::class, 'prestataireAnalytics'])->name('analytics');
        Route::post('/export', [App\Http\Controllers\Inventory\InventoryController::class, 'export'])->name('export');
        Route::post('/', [App\Http\Controllers\Inventory\InventoryController::class, 'store'])->name('store');
        Route::get('/{item}', [App\Http\Controllers\Inventory\InventoryController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [App\Http\Controllers\Inventory\InventoryController::class, 'edit'])->name('edit');
        Route::put('/{item}', [App\Http\Controllers\Inventory\InventoryController::class, 'update'])->name('update');
        Route::delete('/{item}', [App\Http\Controllers\Inventory\InventoryController::class, 'destroy'])->name('destroy');
        Route::post('/{item}/adjust-stock', [App\Http\Controllers\Inventory\InventoryController::class, 'adjustStock'])->name('adjust-stock');
    });

    // 7. Paramètres de Notifications - Notification Preferences
    Route::prefix('notification-settings')->name('notification-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'prestataireIndex'])->name('index');
        Route::put('/', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'update'])->name('update');
        Route::post('/test-email', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'testEmail'])->name('test-email');
        Route::post('/test-sms', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'testSms'])->name('test-sms');
        Route::post('/test-push', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'testPush'])->name('test-push');
        Route::post('/quiet-hours', [App\Http\Controllers\Notifications\NotificationSettingsController::class, 'updateQuietHours'])->name('quiet-hours');
    });
});
