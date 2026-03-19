<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodExploreController;
use App\Http\Controllers\Prestataire\FoodProductController;
use App\Http\Controllers\Prestataire\FoodOrderController as PrestataireFoodOrderController;
use App\Http\Controllers\Prestataire\FoodDeliverySettingsController;
use App\Http\Controllers\Client\FoodOrderController as ClientFoodOrderController;
use App\Http\Controllers\Client\FoodPaymentController;

/*
|--------------------------------------------------------------------------
| Food Order Routes
|--------------------------------------------------------------------------
|
| Routes pour le système de commande alimentaire
|
*/

// ============================================================================
// ROUTES FOOD - EXPLORATION (publique)
// ============================================================================
Route::name('food.')->group(function () {
    // Page d'exploration des prestataires food (comme UberEats)
    Route::get('/food', [FoodExploreController::class, 'index'])->name('explore');
    
    // Menu d'un prestataire
    Route::get('/food/{prestataire}', [FoodExploreController::class, 'menu'])->name('menu');
});

// ============================================================================
// ROUTES CLIENT (Panier et commandes)
// ============================================================================
Route::middleware(['auth', 'role:client|administrateur'])->prefix('commandes-food')->name('food.')->group(function () {
    // Panier
    Route::get('/{prestataire}/panier', [ClientFoodOrderController::class, 'cart'])->name('cart');
    Route::post('/{prestataire}/panier/ajouter/{product}', [ClientFoodOrderController::class, 'addToCart'])->name('cart.add');
    Route::patch('/{prestataire}/panier/modifier', [ClientFoodOrderController::class, 'updateCart'])->name('cart.update');
    Route::delete('/{prestataire}/panier/supprimer', [ClientFoodOrderController::class, 'removeFromCart'])->name('cart.remove');
    Route::delete('/{prestataire}/panier/vider', [ClientFoodOrderController::class, 'clearCart'])->name('cart.clear');
    
    // Checkout
    Route::get('/{prestataire}/checkout', [ClientFoodOrderController::class, 'checkout'])->name('checkout');
    Route::post('/{prestataire}/commander', [ClientFoodOrderController::class, 'placeOrder'])->name('place-order');
    
    // Mes commandes
    Route::get('/mes-commandes', [ClientFoodOrderController::class, 'myOrders'])->name('orders');
    Route::get('/commande/{foodOrder}', [ClientFoodOrderController::class, 'show'])->name('orders.show');
    Route::get('/commande/{foodOrder}/suivi', [ClientFoodOrderController::class, 'track'])->name('orders.track');
    Route::get('/commande/{foodOrder}/statut', [ClientFoodOrderController::class, 'getStatus'])->name('orders.status');
    Route::get('/commande/{foodOrder}/driver-location', [ClientFoodOrderController::class, 'driverLocation'])->name('orders.driver-location');
    Route::get('/route-proxy', [ClientFoodOrderController::class, 'routeProxy'])->middleware('throttle:30,1')->name('orders.route-proxy');
    
    // Actions client — throttle audit 1.11
    Route::post('/commande/{foodOrder}/annuler', [ClientFoodOrderController::class, 'cancel'])->middleware('throttle:10,1')->name('orders.cancel');
    Route::post('/commande/{foodOrder}/confirmer-reception', [ClientFoodOrderController::class, 'confirmReception'])->middleware('throttle:10,1')->name('orders.confirm');
    Route::post('/commande/{foodOrder}/noter', [ClientFoodOrderController::class, 'rate'])->middleware('throttle:10,1')->name('orders.rate');
    
    // Paiement
    Route::get('/commande/{foodOrder}/paiement', [FoodPaymentController::class, 'showPaymentForm'])->name('orders.payment');
    Route::post('/commande/{foodOrder}/paiement/intent', [FoodPaymentController::class, 'createPaymentIntent'])->middleware('throttle:10,1')->name('orders.payment.intent');
    Route::post('/commande/{foodOrder}/paiement/confirm', [FoodPaymentController::class, 'confirmPayment'])->middleware('throttle:20,1')->name('orders.payment.confirm');
    Route::post('/commande/{foodOrder}/paiement/especes', [FoodPaymentController::class, 'payCash'])->middleware('throttle:10,1')->name('orders.payment.cash');
    Route::get('/paiements', [FoodPaymentController::class, 'paymentHistory'])->name('payments.history');
});

// ============================================================================
// ROUTES CARTE INTERNE (prestataire + livreur interne par code)
// ============================================================================
Route::middleware(['food.internal.map'])->prefix('prestataire/food')->name('prestataire.')->group(function () {
    Route::get('food-orders/internal-map', [PrestataireFoodOrderController::class, 'internalMap'])->name('food-orders.internal-map');
    Route::get('food-orders/internal-map/data', [PrestataireFoodOrderController::class, 'internalMapData'])->name('food-orders.internal-map.data');
});

// ============================================================================
// ROUTES PRESTATAIRE (Gestion des produits et commandes)
// ============================================================================
Route::middleware(['auth', 'role:prestataire'])->prefix('prestataire/food')->name('prestataire.')->group(function () {
    
    // Gestion des produits alimentaires
    Route::resource('food-products', FoodProductController::class);
    Route::post('food-products/{foodProduct}/toggle-availability', [FoodProductController::class, 'toggleAvailability'])->name('food-products.toggle');
    Route::post('food-products/{foodProduct}/duplicate', [FoodProductController::class, 'duplicate'])->name('food-products.duplicate');
    Route::post('food-products/reorder', [FoodProductController::class, 'reorder'])->name('food-products.reorder');
    Route::patch('food-products/{foodProduct}/stock', [FoodProductController::class, 'updateStock'])->name('food-products.stock');
    
    // Paramètres de livraison
    Route::get('delivery-settings', [FoodDeliverySettingsController::class, 'index'])->name('food-delivery.index');
    Route::put('delivery-settings', [FoodDeliverySettingsController::class, 'update'])->name('food-delivery.update');
    
    // Gestion des commandes
    Route::get('food-orders', [PrestataireFoodOrderController::class, 'index'])->name('food-orders.index');
    Route::get('food-orders/dashboard', [PrestataireFoodOrderController::class, 'dashboard'])->name('food-orders.dashboard');
    Route::post('food-orders/toggle-open', [PrestataireFoodOrderController::class, 'toggleOpenStatus'])->name('food-orders.toggle-open');
    Route::get('food-orders/stats', [PrestataireFoodOrderController::class, 'stats'])->name('food-orders.stats');
    Route::get('food-orders/new', [PrestataireFoodOrderController::class, 'getNewOrders'])->name('food-orders.new');
    Route::get('food-orders/{foodOrder}', [PrestataireFoodOrderController::class, 'show'])->name('food-orders.show');
    
    // Actions sur les commandes — throttle audit 1.11
    Route::post('food-orders/{foodOrder}/accept', [PrestataireFoodOrderController::class, 'accept'])->middleware('throttle:10,1')->name('food-orders.accept');
    Route::post('food-orders/{foodOrder}/reject', [PrestataireFoodOrderController::class, 'reject'])->middleware('throttle:10,1')->name('food-orders.reject');
    Route::post('food-orders/{foodOrder}/cancel', [PrestataireFoodOrderController::class, 'cancelByPrestataire'])->middleware('throttle:10,1')->name('food-orders.cancel');
    Route::post('food-orders/{foodOrder}/start-preparing', [PrestataireFoodOrderController::class, 'startPreparing'])->middleware('throttle:10,1')->name('food-orders.start-preparing');
    Route::post('food-orders/{foodOrder}/ready', [PrestataireFoodOrderController::class, 'markReady'])->middleware('throttle:10,1')->name('food-orders.ready');
    Route::post('food-orders/{foodOrder}/delivered', [PrestataireFoodOrderController::class, 'markDelivered'])->middleware('throttle:10,1')->name('food-orders.delivered');
    Route::post('food-orders/{foodOrder}/deliver-myself', [PrestataireFoodOrderController::class, 'deliverMyself'])->middleware('throttle:10,1')->name('food-orders.deliver-myself');
    Route::post('food-orders/{foodOrder}/assign-driver', [PrestataireFoodOrderController::class, 'assignInternalDriver'])->middleware('throttle:10,1')->name('food-orders.assign-driver');
    Route::post('food-orders/{foodOrder}/convert-to-pickup', [PrestataireFoodOrderController::class, 'convertToPickup'])->middleware('throttle:10,1')->name('food-orders.convert-to-pickup');
    Route::post('food-orders/{foodOrder}/confirm', [PrestataireFoodOrderController::class, 'confirmReception'])->middleware('throttle:10,1')->name('food-orders.confirm');
    Route::post('food-orders/{foodOrder}/confirm-cash-payment', [PrestataireFoodOrderController::class, 'confirmCashPayment'])->middleware('throttle:10,1')->name('food-orders.confirm-cash');
    Route::post('food-orders/{foodOrder}/verify-code', [PrestataireFoodOrderController::class, 'verifyDeliveryCode'])->middleware('throttle:10,1')->name('food-orders.verify-code');
});

// ============================================================================
// API ROUTES (Calcul de frais de livraison)
// ============================================================================
Route::middleware(['auth', 'throttle:20,1'])->prefix('api/food')->name('api.food.')->group(function () {
    Route::post('/calculate-delivery-fee', [FoodDeliverySettingsController::class, 'calculateDeliveryFee'])->name('calculate-fee');
    Route::post('/calculate-distance', [FoodDeliverySettingsController::class, 'calculateDistance'])->name('calculate-distance');
});

// ============================================================================
// ROUTES PRESTATAIRE - GESTION DES LIVREURS
// ============================================================================
use App\Http\Controllers\Prestataire\PrestataireDriverController;

Route::middleware(['auth', 'role:prestataire'])->prefix('prestataire/drivers')->name('prestataire.drivers.')->group(function () {
    Route::get('/', [PrestataireDriverController::class, 'index'])->name('index');
    Route::get('/{driver}', [PrestataireDriverController::class, 'show'])->name('show');
    Route::post('/internal/store', [PrestataireDriverController::class, 'storeInternal'])->name('store-internal');
    Route::post('/{driver}/regenerate-code', [PrestataireDriverController::class, 'regenerateInternalCode'])->name('regenerate-code');
    Route::post('/{driver}/link-user', [PrestataireDriverController::class, 'linkUser'])->name('link-user');
    Route::post('/{driver}/attach-internal', [PrestataireDriverController::class, 'attachInternal'])->name('attach-internal');
    Route::post('/{driver}/detach-internal', [PrestataireDriverController::class, 'detachInternal'])->name('detach-internal');
    Route::post('/{driver}/sponsor', [PrestataireDriverController::class, 'sponsor'])->name('sponsor');
    Route::post('/{driver}/preference', [PrestataireDriverController::class, 'togglePreference'])->name('preference');
});

// ============================================================================
// ROUTES LIVREUR (Delivery Driver System)
// ============================================================================
use App\Http\Controllers\Driver\DriverController;

Route::prefix('driver')->name('driver.')->group(function () {
    // Acces code interne (sans compte prestataire)
    Route::get('/internal/access', [DriverController::class, 'internalAccessForm'])->name('internal.access');
    Route::post('/internal/access', [DriverController::class, 'internalAccessLogin'])
        ->middleware('throttle:8,1')
        ->name('internal.access.submit');
    Route::post('/internal/logout', [DriverController::class, 'internalLogout'])->name('internal.logout');
    // Carte accessible: si pas de session livreur, on affiche le formulaire code sur cette meme URL.
    Route::get('/map', [DriverController::class, 'routeMap'])->name('map');

    // Inscription livreur classique (compte connecte)
    Route::middleware(['auth'])->group(function () {
        Route::get('/register', [DriverController::class, 'registerForm'])->name('register');
        Route::post('/register', [DriverController::class, 'register'])->name('register.submit');
    });

    // Routes protégées par le middleware driver (compte ou code interne valide)
    Route::middleware(['driver'])->group(function () {
        // Dashboard principal
        Route::get('/dashboard', [DriverController::class, 'dashboard'])->name('dashboard');

        // Historique des livraisons
        Route::get('/deliveries', [DriverController::class, 'myDeliveries'])->name('deliveries');
        Route::get('/deliveries/{foodOrder}', [DriverController::class, 'showDelivery'])->name('deliveries.show');

        // Statistiques
        Route::get('/stats', [DriverController::class, 'stats'])->name('stats');

        // Grille tarifaire (lecture seule - tarifs fixés par la plateforme)
        Route::get('/tarifs', [DriverController::class, 'showTarifs'])->name('tarifs');
        Route::get('/pricing', [DriverController::class, 'showTarifs'])->name('pricing');

        // Actions sur les livraisons — throttle audit 1.11
        Route::post('/deliveries/{foodOrder}/accept', [DriverController::class, 'acceptDelivery'])->middleware('throttle:10,1')->name('accept');
        Route::post('/deliveries/{foodOrder}/pickup', [DriverController::class, 'pickUp'])->middleware('throttle:10,1')->name('pickup');
        Route::post('/deliveries/{foodOrder}/start', [DriverController::class, 'startDelivery'])->middleware('throttle:10,1')->name('start');
        Route::post('/deliveries/{foodOrder}/deliver', [DriverController::class, 'deliver'])->middleware('throttle:10,1')->name('deliver');
        Route::post('/deliveries/{foodOrder}/problem', [DriverController::class, 'reportProblem'])->middleware('throttle:10,1')->name('problem');

        // Toggle disponibilité
        Route::post('/toggle-availability', [DriverController::class, 'toggleAvailability'])->name('toggle-availability');

        // Mise à jour position GPS
        Route::post('/location', [DriverController::class, 'updateLocation'])->name('update-location');

        // Optimisation d'itinéraire
        Route::post('/optimize-route', [DriverController::class, 'optimizeRoute'])->name('optimize-route');

        // Navigation GPS turn-by-turn
        Route::get('/navigate/{foodOrder}', [DriverController::class, 'navigate'])->name('navigate');
    });

    // Stripe reserve aux livreurs connectes avec compte classique
    Route::middleware(['auth', 'driver'])->group(function () {
        Route::get('/stripe/setup', [DriverController::class, 'stripeSetup'])->name('stripe.setup');
        Route::post('/stripe/connect', [DriverController::class, 'stripeConnect'])->name('stripe.connect');
        Route::get('/stripe/callback', [DriverController::class, 'stripeCallback'])->name('stripe.callback');
        Route::get('/stripe/dashboard', [DriverController::class, 'stripeDashboard'])->name('stripe.dashboard');
    });
});
