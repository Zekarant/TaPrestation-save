<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PrestataireController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\MatchingAlertController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\UrgentSaleController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\Prestataire\VerificationController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Prestataire\ServiceImageController;
use App\Http\Controllers\Prestataire\AvailabilityController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SyncUrgentSaleInventoryController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Auction\AuctionController;
use App\Http\Controllers\Delivery\DeliveryController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Address\AddressBookController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Notifications\NotificationSettingsController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\AuthRedirectController;
use App\Http\Controllers\Internal\DemoMarketplaceSeedController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes HTTP de l'application (middleware "web").
|
*/

// Social Auth
Route::get('login/{provider}', [SocialAuthController::class, 'redirectToProvider'])
    ->middleware('throttle:10,1')
    ->name('social.login');
Route::get('login/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])
    ->middleware('throttle:10,1');
Route::post('social/create-account', [SocialAuthController::class, 'createAccountFromChoice'])
    ->middleware('throttle:5,1')
    ->name('social.create-account');

// Include debug routes en local
if (app()->environment('local')) {
    $debugRoutesPath = __DIR__ . '/debug-routes.php';
    if (file_exists($debugRoutesPath)) {
        require $debugRoutesPath;
    }
}

// Include escrow routes (paiement sécurisé)
$escrowRoutesPath = __DIR__ . '/escrow.php';
if (file_exists($escrowRoutesPath)) {
    require $escrowRoutesPath;
}

// Stripe webhooks (public endpoint, CSRF exempté dans VerifyCsrfToken)
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Bridge web one-shot pour générer le dataset de démonstration sans SSH.
// SÉCURITÉ: protégé par auth + rôle administrateur (audit 1.2)
Route::get('/internal/demo/seed-marketplace/{token}', DemoMarketplaceSeedController::class)
    ->where('token', '[A-Za-z0-9._-]+')
    ->middleware(['auth', 'role:administrateur', 'throttle:2,10'])
    ->name('internal.demo.seed-marketplace');

// Route de secours pour les images storage (si le lien symbolique ou .htaccess échoue)
Route::get('storage/{path}', [\App\Http\Controllers\StorageFallbackController::class, 'serve'])->where('path', '.*');

// SEO - Sitemap dynamique
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Pages légales dynamiques (synchronisées avec l'admin)
Route::get('/privacy', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'privacy')->name('privacy');
Route::get('/terms', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'terms')->name('terms');
Route::get('/cgu', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'cgu')->name('cgu');
Route::get('/cgv', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'cgv')->name('cgv');
Route::get('/cookies', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'cookies')->name('cookies');
Route::get('/legal', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'terms')->name('legal'); // Alias
Route::get('/mentions-legales', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'mentions')->name('mentions');

// Pages informatives dynamiques
Route::get('/contact', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'contact')->name('contact');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'send'])
    ->middleware('throttle:5,1')
    ->name('contact.send');
Route::get('/faq', [\App\Http\Controllers\LegalPageController::class, 'show'])->defaults('slug', 'faq')->name('faq');

// Suivi public des livraisons
Route::get('/track', [\App\Http\Controllers\Delivery\LogisticsController::class, 'track'])->name('delivery.public-track');
Route::get('/track/{tracking_number}', [\App\Http\Controllers\Delivery\LogisticsController::class, 'track'])->name('delivery.track-by-number');

/*
|--------------------------------------------------------------------------
| API simples (JSON) intégrées au web
|--------------------------------------------------------------------------
*/

// API catégories principales
Route::get('/api/categories/main', [\App\Http\Controllers\CategoryController::class, 'getMainCategories'])
    ->middleware('throttle:30,1');

// API sous-catégories
Route::get('/api/categories/{category}/subcategories', [\App\Http\Controllers\CategoryController::class, 'getSubcategories'])
    ->middleware('throttle:30,1');

// API de géocodage simple
Route::get('/api/geocode', [App\Http\Controllers\GeocodingController::class, 'geocode'])
    ->middleware('throttle:20,1');

// API de géocodage inverse
Route::get('/api/reverse-geocode', [App\Http\Controllers\GeocodingController::class, 'reverseGeocode'])
    ->middleware('throttle:20,1');

/*
|--------------------------------------------------------------------------
| Routes de vérification "prestataire" (KYC interne)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::post('/verification-requests', [VerificationController::class, 'store'])->name('verification.store');
});

// Admin routes
require base_path('routes/admin.php');

/*
|--------------------------------------------------------------------------
| Auth / Login / Register
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// CSRF token refresh endpoint
Route::get('/csrf-token', function () {
    $origin = (string) request()->headers->get('Origin', '');
    $referer = (string) request()->headers->get('Referer', '');
    $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
    $requestHost = strtolower((string) request()->getHost());
    $allowedHosts = array_filter([$appHost, $requestHost]);

    $sourceUrl = $origin !== '' ? $origin : $referer;
    $sourceHost = strtolower((string) parse_url($sourceUrl, PHP_URL_HOST));

    if ($sourceHost === '' || !in_array($sourceHost, $allowedHosts, true)) {
        abort(403);
    }

    return response()->json([
        'token' => csrf_token(),
        'timestamp' => now()
    ]);
})->middleware(['web', 'throttle:20,1']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1');

// Convenience route: legacy link used across views/controllers.
// Redirects to the main register page with a `role=prestataire` query so
// the registration form can pre-select the prestataire flow if supported.
Route::get('/prestataire/register', function () {
    return redirect()->route('register', ['role' => 'prestataire']);
})->name('prestataire.register');

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('dashboard')->with('verified', true);
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('resent', true);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Unread Count endpoints (public pour les requêtes AJAX)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/

Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->middleware('throttle:10,1')->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');

/*
|--------------------------------------------------------------------------
| Catégories
|--------------------------------------------------------------------------
*/

Route::get('/categories/{category}/subcategories', [\App\Http\Controllers\CategoryController::class, 'getSubcategories'])->name('categories.subcategories');
Route::get('/categories/main', [\App\Http\Controllers\CategoryController::class, 'getMainCategories'])->name('categories.main');
Route::get('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'getCategory'])->name('categories.show');

/*
|--------------------------------------------------------------------------
| Services (consultation publique)
|--------------------------------------------------------------------------
*/

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/services/{service}/report', [ServiceController::class, 'submitReport'])->name('services.report');
});

/*
|--------------------------------------------------------------------------
| Prestataires (consultation publique)
|--------------------------------------------------------------------------
*/

Route::get('/prestataires', [PrestataireController::class, 'index'])->name('prestataires.index');

// Profil prestataire accessible sans connexion (sans infos sensibles)
Route::get('/prestataires/{prestataire}', [PrestataireController::class, 'show'])->name('prestataires.show');

// Profil public utilisateur (pour clients dans messagerie)
Route::get('/users/{user}/profile', [\App\Http\Controllers\PublicProfileController::class, 'show'])->name('users.public.show');

// Pages publiques dédiées (structure) : boutique / services / équipements
Route::get('/prestataires/{prestataire}/boutique', [PrestataireController::class, 'boutique'])->name('prestataires.boutique');
Route::get('/prestataires/{prestataire}/services', [PrestataireController::class, 'services'])->name('prestataires.services');
Route::get('/prestataires/{prestataire}/equipements', [PrestataireController::class, 'equipements'])->name('prestataires.equipements');

// Profil public prestataire par ID (accessible sans connexion)
Route::get('/prestataire/profile/public/{id}', [\App\Http\Controllers\Prestataire\ProfileController::class, 'publicShow'])->name('prestataire.profile.public');

/*
|--------------------------------------------------------------------------
| Équipements (consultation publique)
|--------------------------------------------------------------------------
*/

Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/equipment/{equipment}/reserve', [EquipmentController::class, 'showReservationForm'])->name('equipment.reserve');
    Route::post('/equipment/{equipment}/rent', [EquipmentController::class, 'rent'])->name('equipment.rent');
});

/*
|--------------------------------------------------------------------------
| Recherche globale (déplacée en public)
|--------------------------------------------------------------------------
*/

// Routes search déclarées plus haut (avant le bloc auth principal)

/*
|--------------------------------------------------------------------------
| Vidéos
|--------------------------------------------------------------------------
*/

Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/feed', [VideoController::class, 'index'])->name('videos.feed');
Route::match(['get', 'head', 'options'], '/videos/{video}/stream', [VideoController::class, 'stream'])->name('videos.stream');
Route::post('/videos/upload', [VideoController::class, 'upload'])->middleware(['auth', 'throttle:3,1'])->name('videos.upload');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::post('/videos/{video}/like', [VideoController::class, 'like'])->middleware('auth')->name('videos.like');
Route::post('/videos/{video}/comments', [VideoController::class, 'comment'])->name('videos.comment')->middleware('auth');
Route::get('/videos/{video}/comments', [VideoController::class, 'getComments'])->name('videos.comments.get');
Route::post('/videos/{video}/increment-views', [VideoController::class, 'incrementViewCount'])
    ->middleware(['auth', 'throttle:20,1'])
    ->name('videos.increment-views');
Route::post('/prestataires/{prestataire}/follow', [VideoController::class, 'follow'])->middleware('auth')->name('prestataires.follow');

/*
|--------------------------------------------------------------------------
| Avis (public + auth)
|--------------------------------------------------------------------------
*/

Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/reviews/create', [ReviewController::class, 'create'])->middleware('auth')->name('reviews.create');
Route::post('/reviews', [ReviewController::class, 'store'])->middleware(['auth', 'throttle:5,1'])->name('reviews.store');
Route::get('/reviews/with-photos', [ReviewController::class, 'withPhotos'])->name('reviews.with-photos');
Route::get('/reviews/certificates', [ReviewController::class, 'certificates'])->name('reviews.certificates');

/*
|--------------------------------------------------------------------------
| Avis sur les clients (par les prestataires)
|--------------------------------------------------------------------------
*/

Route::get('/client/{user}/reviews', [App\Http\Controllers\ClientReviewController::class, 'showClientReviews'])
    ->middleware(['auth', 'role:prestataire'])
    ->name('client.reviews');
Route::get('/api/client/{user}/rating', [App\Http\Controllers\ClientReviewController::class, 'getClientRating'])
    ->middleware(['auth', 'role:prestataire'])
    ->name('api.client.rating');

// Redirection pour les anciennes URLs /client-reviews/{number}
Route::get('/client-reviews/{number}', [AuthRedirectController::class, 'clientReviewRedirect'])
    ->middleware(['auth', 'role:prestataire']);

Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('/client-reviews/create/{booking}', [App\Http\Controllers\ClientReviewController::class, 'create'])->name('client-reviews.create');
    Route::post('/client-reviews/{booking}', [App\Http\Controllers\ClientReviewController::class, 'store'])->name('client-reviews.store');
});

/*
|--------------------------------------------------------------------------
| Urgent Sales & Search (consultation publique)
|--------------------------------------------------------------------------
*/

Route::get('/urgent-sales', [UrgentSaleController::class, 'index'])->name('urgent-sales.index');
Route::get('/urgent-sales/{urgentSale}', [UrgentSaleController::class, 'show'])->name('urgent-sales.show');

Route::get('/search', [SearchController::class, 'searchPrestataires'])->name('search.index');
Route::post('/search', [SearchController::class, 'searchPrestataires'])->name('search.results');
Route::get('/search/prestataires', [SearchController::class, 'searchPrestataires'])->name('search.prestataires');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

/*
|--------------------------------------------------------------------------
| Routes protégées (auth)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Messagerie unifiée (accessible à tous les utilisateurs authentifiés)
    Route::get('/messaging', [\App\Http\Controllers\MessagingController::class, 'index'])->name('messaging.index');
    Route::get('/messaging/unread-count', [MessageController::class, 'getUnreadCount'])->name('messaging.unread-count');
    Route::get('/messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'show'])->whereNumber('user')->name('messaging.show');
    Route::post('/messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'store'])->whereNumber('user')->name('messaging.store');
    Route::delete('/messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'deleteConversation'])->whereNumber('user')->name('messaging.delete');
    Route::get('/messaging/conversation/{user}', [\App\Http\Controllers\MessagingController::class, 'show'])->whereNumber('user')->name('messaging.conversation');

    // Assistant de guidage
    Route::get('/guidance-assistant/boot', [\App\Http\Controllers\Assistant\GuidanceAssistantController::class, 'boot'])
        ->middleware('throttle:30,1')
        ->name('guidance-assistant.boot');
    Route::post('/guidance-assistant/chat', [\App\Http\Controllers\Assistant\GuidanceAssistantController::class, 'chat'])
        ->middleware('throttle:30,1')
        ->name('guidance-assistant.chat');

    // Booking management (actions générales) — throttle audit 1.11
    Route::post('/bookings/{booking}/refuse', [BookingController::class, 'refuse'])->middleware('throttle:10,1')->name('bookings.refuse');
    Route::put('/bookings/{booking}/complete', [BookingController::class, 'complete'])->middleware('throttle:10,1')->name('bookings.complete.client');
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->middleware('throttle:10,1')->name('bookings.confirm');

    // Prepayment routes (MUST be before the universal booking show route)
    Route::get('/bookings/prepayment', [BookingController::class, 'showPrepayment'])->name('bookings.prepayment');
    Route::post('/bookings/prepayment/intent', [BookingController::class, 'createPrepaymentIntent'])->middleware('throttle:10,1')->name('bookings.prepayment.intent');
    Route::post('/bookings/prepayment', [BookingController::class, 'processPrepayment'])->middleware('throttle:20,1')->name('bookings.prepayment.process');

    // Route universelle booking show - redirige selon le rôle
    Route::get('/bookings/universal/{booking}', [AuthRedirectController::class, 'bookingUniversal'])->name('bookings.show.universal');

    // API agenda prestataire
    Route::get('/api/prestataire/agenda/events', [App\Http\Controllers\Prestataire\AgendaController::class, 'events'])->name('api.prestataire.agenda.events');
    Route::get('/api/prestataire/agenda/recent-bookings', [App\Http\Controllers\Prestataire\AgendaController::class, 'recentBookings'])->name('api.prestataire.agenda.recent-bookings');

    // Dashboard global (redirige selon rôle)
    Route::get('/dashboard', [AuthRedirectController::class, 'dashboard'])->name('dashboard');

    // Routes génériques de profil
    Route::get('/profile/edit', [AuthRedirectController::class, 'profileEdit'])->name('profile.edit');

    Route::get('/profile/settings', [AuthRedirectController::class, 'profileSettings'])->name('profile.settings');

    /*
    |--------------------------------------------------------------------------
    | Panier partagé (clients ET prestataires peuvent acheter)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'verified', 'role:client,prestataire', 'profile.complete'])->prefix('client')->name('client.')->group(function () {
        // Panier (A+B+C) + acompte mode 2
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Client\CartController::class, 'index'])->name('index');
            Route::post('/bookings/{booking}', [\App\Http\Controllers\Client\CartController::class, 'addBooking'])->name('add.booking');
            Route::post('/equipment-rental-requests/{request}', [\App\Http\Controllers\Client\CartController::class, 'addRentalRequest'])->name('add.rental-request');
            Route::post('/urgent-sales/{urgentSale}', [\App\Http\Controllers\Client\CartController::class, 'addUrgentSale'])->name('add.urgent-sale');
            Route::patch('/items/{cartItem}', [\App\Http\Controllers\Client\CartController::class, 'updateItem'])->name('items.update');
            Route::delete('/items/{cartItem}', [\App\Http\Controllers\Client\CartController::class, 'removeItem'])->name('items.remove');
        });

        // Paiements panier
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/cart', [\App\Http\Controllers\Payment\CartPaymentController::class, 'show'])->name('cart.form');
            Route::post('/cart/intent', [\App\Http\Controllers\Payment\CartPaymentController::class, 'createIntent'])->middleware('throttle:10,1')->name('cart.intent');
            Route::post('/cart/confirm', [\App\Http\Controllers\Payment\CartPaymentController::class, 'confirm'])->middleware('throttle:20,1')->name('cart.confirm');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Client area (accessible aux clients ET prestataires en mode client)
    |--------------------------------------------------------------------------
    | Les prestataires peuvent aussi agir en tant que clients (acheter,
    | réserver chez d'autres prestataires, etc.) sauf commander à eux-mêmes.
    */

    // Client routes
    require base_path('routes/client.php');

    // Prestataire routes
    require base_path('routes/prestataire.php');

    /*
    |--------------------------------------------------------------------------
    | Équipement (actions protégées uniquement — index/show déplacés en public)
    |--------------------------------------------------------------------------
    | NOTE: reserve et rent sont déjà définis aux lignes 793-796
    */

    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::post('/{equipment}/report', [EquipmentController::class, 'submitReport'])->name('report');
    });

    /*
    |--------------------------------------------------------------------------
    | Urgent sales (boutique) - actions protégées uniquement — index/show déplacés en public
    |--------------------------------------------------------------------------
    */

    Route::prefix('urgent-sales')->name('urgent-sales.')->group(function () {
        Route::post('/{urgentSale}/contact', [UrgentSaleController::class, 'contact'])
            ->middleware('throttle:5,1')
            ->name('contact');
        Route::post('/{urgentSale}/report', [UrgentSaleController::class, 'report'])
            ->middleware('throttle:5,1')
            ->name('report');
        Route::post('/{urgentSale}/reserve', [\App\Http\Controllers\UrgentSaleReservationController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('reserve');
    });

    // Mes réservations (client)
    Route::prefix('my-reservations')->name('my-reservations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UrgentSaleReservationController::class, 'clientIndex'])->name('index');
        Route::post('/{reservation}/cancel', [\App\Http\Controllers\UrgentSaleReservationController::class, 'clientCancel'])->name('cancel');
        Route::post('/{reservation}/rate-seller', [\App\Http\Controllers\UrgentSaleReservationController::class, 'rateSeller'])->name('rate-seller');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications (générales, tous users auth)
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/unpushed', [NotificationController::class, 'getUnpushed'])->name('unpushed');
        Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
        // Redirect and mark as read in one click
        Route::get('/{notification}/redirect', [NotificationController::class, 'redirectAndMarkRead'])->name('redirect');
    });

    /*
    |--------------------------------------------------------------------------
    | Push Notifications (PWA)
    |--------------------------------------------------------------------------
    */
    Route::post('/push/subscribe', [\App\Http\Controllers\PushNotificationController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushNotificationController::class, 'unsubscribe'])->name('push.unsubscribe');

    /*
    |--------------------------------------------------------------------------
    | Prestataire en attente d'approbation
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:prestataire'])
        ->get('/prestataire/pending-approval', function () {
            return view('prestataire.pending_approval');
        })->name('prestataire.pending-approval');

    /*
    |--------------------------------------------------------------------------
    | Bookings (réservations génériques)
    |--------------------------------------------------------------------------
    */

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/create/{service}', [BookingController::class, 'create'])->name('create');
        Route::post('/', [BookingController::class, 'store'])->name('store');
        // Note: prepayment routes are defined earlier in the file (before the universal booking show route)
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::put('/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:10,1')->name('cancel');
        Route::put('/{booking}/complete', [BookingController::class, 'complete'])->name('complete');
    });

    /*
    |--------------------------------------------------------------------------
    | Messagerie (MessageController)
    |--------------------------------------------------------------------------
    */

    Route::prefix('messaging')->name('messaging.')->group(function () {
        Route::get('/start/{user}', [MessageController::class, 'start'])->name('start');
        Route::get('/conversation', function () {
            return redirect()->route('messaging.index');
        });
        Route::post('/send/{receiver}', [MessageController::class, 'send'])->name('send');
        Route::post('/send-ajax', [MessageController::class, 'sendAjax'])->name('send.ajax');
        Route::get('/new-messages/{user}', [MessageController::class, 'getNewMessages'])->name('new-messages');
        Route::get('/user-status/{user}', [MessageController::class, 'getUserOnlineStatus'])->name('user-status');
        Route::post('/mark-as-read', [MessageController::class, 'markAsRead'])->name('mark-as-read');
    });

    /*
    |--------------------------------------------------------------------------
    | Avis (CRUD complet côté auth)
    |--------------------------------------------------------------------------
    */

    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
        Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Alertes
    |--------------------------------------------------------------------------
    */

    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', [MatchingAlertController::class, 'index'])->name('index');
        Route::post('/', [MatchingAlertController::class, 'store'])->name('store');
        Route::put('/{alert}', [MatchingAlertController::class, 'update'])->name('update');
        Route::delete('/{alert}', [MatchingAlertController::class, 'destroy'])->name('destroy');
        Route::put('/{alert}/mark-read', [MatchingAlertController::class, 'markAsRead'])->name('mark-read');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin (gros bloc administrateur)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:administrateur'])
        ->prefix('administrateur')
        ->name('administrateur.')
        ->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
            Route::get('/dashboard/chart', [\App\Http\Controllers\Admin\DashboardController::class, 'getChartData'])->name('dashboard.chart');

            // Users
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
            Route::post('/users/{user}/toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('users.toggle-block');
            Route::post('/users/bulk-unblock', [\App\Http\Controllers\Admin\UserController::class, 'bulkUnblock'])->name('users.bulk-unblock');
            Route::post('/users/bulk-block', [\App\Http\Controllers\Admin\UserController::class, 'bulkBlock'])->name('users.bulk-block');
            Route::post('/users/bulk-delete', [\App\Http\Controllers\Admin\UserController::class, 'bulkDelete'])->name('users.bulk-delete');
            Route::get('/users/export', [\App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');

            // Prestataires
            Route::resource('prestataires', \App\Http\Controllers\Admin\PrestataireController::class);
            Route::get('/prestataires-pending', [\App\Http\Controllers\Admin\PrestataireController::class, 'pending'])->name('prestataires.pending');
            Route::post('/prestataires/{prestataire}/approve', [\App\Http\Controllers\Admin\PrestataireController::class, 'approve'])->name('prestataires.approve');
            Route::post('/prestataires/{prestataire}/revoke', [\App\Http\Controllers\Admin\PrestataireController::class, 'revoke'])->name('prestataires.revoke');
            Route::post('/prestataires/{prestataire}/toggle-block', [\App\Http\Controllers\Admin\PrestataireController::class, 'toggleBlock'])->name('prestataires.toggle-block');
            Route::post('/prestataires/bulk-unblock', [\App\Http\Controllers\Admin\PrestataireController::class, 'bulkUnblock'])->name('prestataires.bulk-unblock');
            Route::post('/prestataires/bulk-block', [\App\Http\Controllers\Admin\PrestataireController::class, 'bulkBlock'])->name('prestataires.bulk-block');
            Route::post('/prestataires/bulk-delete', [\App\Http\Controllers\Admin\PrestataireController::class, 'bulkDelete'])->name('prestataires.bulk-delete');
            Route::get('/prestataires/export', [\App\Http\Controllers\Admin\PrestataireController::class, 'export'])->name('prestataires.export');

            // Clients
            Route::resource('clients', \App\Http\Controllers\Admin\ClientController::class);
            Route::post('/clients/{client}/toggle-block', [\App\Http\Controllers\Admin\ClientController::class, 'toggleBlock'])->name('clients.toggle-block');
            Route::post('/clients/bulk-unblock', [\App\Http\Controllers\Admin\ClientController::class, 'bulkUnblock'])->name('clients.bulk-unblock');
            Route::post('/clients/bulk-block', [\App\Http\Controllers\Admin\ClientController::class, 'bulkBlock'])->name('clients.bulk-block');
            Route::post('/clients/bulk-delete', [\App\Http\Controllers\Admin\ClientController::class, 'bulkDelete'])->name('clients.bulk-delete');
            Route::get('/clients/export', [\App\Http\Controllers\Admin\ClientController::class, 'export'])->name('clients.export');

            // Catégories
            Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

            // Compétences
            Route::resource('skills', \App\Http\Controllers\Admin\SkillController::class);

            // Services (modération admin)
            Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
            Route::post('/services/{service}/toggle-visibility', [\App\Http\Controllers\Admin\ServiceController::class, 'toggleVisibility'])->name('services.toggleVisibility');
            Route::get('/services/export', [\App\Http\Controllers\Admin\ServiceController::class, 'export'])->name('services.export');

            // Avis (modération admin)
            Route::resource('reviews', \App\Http\Controllers\Admin\ReviewController::class);
            Route::post('/reviews/{review}/moderate', [\App\Http\Controllers\Admin\ReviewController::class, 'moderate'])->name('reviews.moderate');

            // Réservations (admin)
            Route::resource('bookings', \App\Http\Controllers\Admin\BookingController::class);
            Route::post('/bookings/{booking}/update-status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.update-status');
            Route::get('/bookings/export', [\App\Http\Controllers\Admin\BookingController::class, 'export'])->name('bookings.export');

            // Recherche users AJAX (audit 3.10)
            Route::get('/users/search', function (\Illuminate\Http\Request $request) {
                $q = trim((string) $request->query('q', ''));
                if (mb_strlen($q) < 2) {
                    return response()->json([]);
                }
                $escaped = str_replace(['%', '_'], ['\%', '\_'], $q);
                return \App\Models\User::where(function ($query) use ($escaped) {
                    $query->where('name', 'like', "%{$escaped}%")
                        ->orWhere('email', 'like', "%{$escaped}%");
                })
                    ->select('id', 'name', 'email')
                    ->limit(30)
                    ->get();
            })->middleware('throttle:30,1')->name('users.search');

            // Notifications (admin)
            Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class);
            Route::post('/notifications/{notification}/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
            Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
            Route::post('/notifications/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('notifications.send');
            Route::post('/notifications/send-custom', [\App\Http\Controllers\Admin\NotificationController::class, 'sendCustom'])->name('notifications.send-custom');
            Route::delete('/notifications/cleanup', [\App\Http\Controllers\Admin\NotificationController::class, 'cleanup'])->name('notifications.cleanup');
            Route::get('/notifications/analytics', [\App\Http\Controllers\Admin\NotificationController::class, 'analytics'])->name('notifications.analytics');
            Route::get('/notifications/export', [\App\Http\Controllers\Admin\NotificationController::class, 'export'])->name('notifications.export');
            Route::post('/notifications/mark-selected-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markSelectedAsRead'])->name('notifications.mark-selected-read');
            Route::post('/notifications/bulk-delete', [\App\Http\Controllers\Admin\NotificationController::class, 'bulkDelete'])->name('notifications.bulk-delete');

            // Signalements
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::prefix('urgent-sales')->name('urgent-sales.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\UrgentSaleReportController::class, 'index'])->name('index');
                    Route::get('/{report}', [\App\Http\Controllers\Admin\UrgentSaleReportController::class, 'show'])->name('show');
                    Route::post('/{report}/update-status', [\App\Http\Controllers\Admin\UrgentSaleReportController::class, 'updateStatus'])->name('update-status');
                    Route::delete('/{report}', [\App\Http\Controllers\Admin\UrgentSaleReportController::class, 'destroy'])->name('destroy');
                });

                Route::prefix('services')->name('services.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\ServiceReportController::class, 'index'])->name('index');
                    Route::get('/{report}', [\App\Http\Controllers\Admin\ServiceReportController::class, 'show'])->name('show');
                    Route::post('/{report}/update-status', [\App\Http\Controllers\Admin\ServiceReportController::class, 'updateStatus'])->name('update-status');
                    Route::delete('/{report}', [\App\Http\Controllers\Admin\ServiceReportController::class, 'destroy'])->name('destroy');
                });

                Route::prefix('equipments')->name('equipments.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\EquipmentReportController::class, 'index'])->name('index');
                    Route::get('/{report}', [\App\Http\Controllers\Admin\EquipmentReportController::class, 'show'])->name('show');
                    Route::post('/{report}/update-status', [\App\Http\Controllers\Admin\EquipmentReportController::class, 'updateStatus'])->name('update-status');
                    Route::delete('/{report}', [\App\Http\Controllers\Admin\EquipmentReportController::class, 'destroy'])->name('destroy');
                });

                Route::prefix('all')->name('all.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\AllReportsController::class, 'index'])->name('index');
                    Route::post('/{type}/{id}/update-status', [\App\Http\Controllers\Admin\AllReportsController::class, 'updateStatus'])->name('update-status');
                    Route::delete('/{type}/{id}/delete-content', [\App\Http\Controllers\Admin\AllReportsController::class, 'deleteContent'])->name('delete-content');
                    Route::delete('/{type}/{id}', [\App\Http\Controllers\Admin\AllReportsController::class, 'destroy'])->name('destroy');
                });
            });

            // Analytics
            Route::prefix('analytics')->name('analytics.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
                Route::get('/dashboard', [\App\Http\Controllers\Admin\ReportController::class, 'dashboard'])->name('dashboard');
                Route::get('/dashboard-modern', [\App\Http\Controllers\Admin\ReportController::class, 'dashboardModern'])->name('dashboard-modern');
                Route::get('/users', [\App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
                Route::get('/services', [\App\Http\Controllers\Admin\ReportController::class, 'services'])->name('services');
                Route::get('/bookings', [\App\Http\Controllers\Admin\ReportController::class, 'bookings'])->name('bookings');
                Route::get('/financial', [\App\Http\Controllers\Admin\ReportController::class, 'financial'])->name('financial');
                Route::get('/export/{type}', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('export');
            });

            // Équipements (admin)
            Route::resource('equipment', \App\Http\Controllers\Admin\EquipmentController::class);

            // Commandes
            Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
        });
});

/*
|--------------------------------------------------------------------------
| Test & Wizards (hors gros groupe auth si besoin)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get('/test/availability', function () {
        return view('test.availability_test');
    })->name('test.availability');
}

// Wizard routes (creation flows)
require base_path('routes/wizards.php');

// New Features Routes
require base_path('routes/features.php');

// Tender System Routes (Appels d'Offres)
require base_path('routes/tenders.php');

// Driver entry fallback:
// keep a direct /driver redirect even if route cache contains an older food.php set.
Route::redirect('/driver', '/driver/internal/access', 302);

// Food Ordering System Routes (Commandes Alimentaires)
require base_path('routes/food.php');

