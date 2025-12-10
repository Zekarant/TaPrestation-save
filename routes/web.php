<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes HTTP de l'application (middleware "web").
|
*/

// Include debug routes en local
if (app()->environment('local')) {
    $debugRoutesPath = __DIR__ . '/debug-routes.php';
    if (file_exists($debugRoutesPath)) {
        require $debugRoutesPath;
    }
}

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Page RGPD
Route::get('/rgpd', function () {
    return view('rgpd');
})->name('rgpd');

/*
|--------------------------------------------------------------------------
| API simples (JSON) intégrées au web
|--------------------------------------------------------------------------
*/

// API sous-catégories
Route::get('/api/categories/{category}/subcategories', function ($categoryId) {
    $subcategories = \App\Models\Category::where('parent_id', $categoryId)
        ->orderBy('name')
        ->get();

    return response()->json($subcategories);
});

// API de géocodage simple
Route::get('/api/geocode', function (Request $request) {
    $address = $request->get('address');

    if (empty($address)) {
        return response()->json(['success' => false, 'message' => 'Adresse requise']);
    }

    $frenchCities = [
        'paris' => ['latitude' => 48.8566, 'longitude' => 2.3522],
        'marseille' => ['latitude' => 43.2965, 'longitude' => 5.3698],
        'lyon' => ['latitude' => 45.7640, 'longitude' => 4.8357],
        'toulouse' => ['latitude' => 43.6047, 'longitude' => 1.4442],
        'nice' => ['latitude' => 43.7102, 'longitude' => 7.2620],
        'nantes' => ['latitude' => 47.2184, 'longitude' => -1.5536],
        'montpellier' => ['latitude' => 43.6110, 'longitude' => 3.8767],
        'strasbourg' => ['latitude' => 48.5734, 'longitude' => 7.7521],
        'bordeaux' => ['latitude' => 44.8378, 'longitude' => -0.5792],
        'lille' => ['latitude' => 50.6292, 'longitude' => 3.0573],
        'rennes' => ['latitude' => 48.1173, 'longitude' => -1.6778],
        'reims' => ['latitude' => 49.2583, 'longitude' => 4.0317],
        'toulon' => ['latitude' => 43.1242, 'longitude' => 5.9280],
        'saint-etienne' => ['latitude' => 45.4397, 'longitude' => 4.3872],
        'le havre' => ['latitude' => 49.4944, 'longitude' => 0.1079],
        'grenoble' => ['latitude' => 45.1885, 'longitude' => 5.7245],
        'dijon' => ['latitude' => 47.3220, 'longitude' => 5.0415],
        'angers' => ['latitude' => 47.4784, 'longitude' => -0.5632],
        'nimes' => ['latitude' => 43.8367, 'longitude' => 4.3601],
        'villeurbanne' => ['latitude' => 45.7665, 'longitude' => 4.8795],
    ];

    $addressLower = strtolower($address);

    foreach ($frenchCities as $city => $coords) {
        if (strpos($addressLower, $city) !== false) {
            return response()->json([
                'success' => true,
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
                'city' => ucfirst($city),
            ]);
        }
    }

    return response()->json(['success' => false, 'message' => 'Ville non trouvée']);
});

// API de géocodage inverse
Route::get('/api/reverse-geocode', [App\Http\Controllers\GeocodingController::class, 'reverseGeocode']);

/*
|--------------------------------------------------------------------------
| Routes de vérification "prestataire" (KYC interne)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::post('/verification-requests', [VerificationController::class, 'store'])->name('verification.store');
});

/*
|--------------------------------------------------------------------------
| Admin routes (dashboard + vérifications + modération)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:administrateur'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Vérifications prestataires
        Route::prefix('verifications')->name('verifications.')->group(function () {
            Route::get('/', [AdminVerificationController::class, 'index'])->name('index');
            Route::get('/{verificationRequest}', [AdminVerificationController::class, 'show'])->name('show');
            Route::patch('/{verificationRequest}/approve', [AdminVerificationController::class, 'approve'])->name('approve');
            Route::patch('/{verificationRequest}/reject', [AdminVerificationController::class, 'reject'])->name('reject');
            Route::get('/{verificationRequest}/document/{documentIndex}', [AdminVerificationController::class, 'downloadDocument'])->name('download-document');
            Route::post('/run-automatic', [AdminVerificationController::class, 'runAutomaticVerification'])->name('run-automatic');
            Route::patch('/{prestataire}/revoke', [AdminVerificationController::class, 'revokeVerification'])->name('revoke');
        });

        // Équipements (admin)
        Route::resource('equipments', App\Http\Controllers\Admin\EquipmentController::class)
            ->except(['create', 'store', 'destroy'])
            ->names('equipments');

        // Ventes urgentes (annonces)
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UrgentSaleController::class, 'index'])->name('index');
            Route::get('/{urgentSale}', [App\Http\Controllers\Admin\UrgentSaleController::class, 'show'])->name('show');
            Route::patch('/{urgentSale}/suspend', [App\Http\Controllers\Admin\UrgentSaleController::class, 'suspend'])->name('suspend');
            Route::patch('/{urgentSale}/reactivate', [App\Http\Controllers\Admin\UrgentSaleController::class, 'reactivate'])->name('reactivate');
            Route::delete('/{urgentSale}', [App\Http\Controllers\Admin\UrgentSaleController::class, 'destroy'])->name('destroy');
            Route::get('/dashboard', [App\Http\Controllers\Admin\UrgentSaleController::class, 'dashboard'])->name('dashboard');
        });

        // Services (vue admin)
        Route::resource('services', App\Http\Controllers\Admin\ServiceController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('services');

        // Avis
        Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('reviews');

        // Réservations
        Route::resource('bookings', App\Http\Controllers\Admin\BookingController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('bookings');

        // Notifications
        Route::resource('notifications', App\Http\Controllers\Admin\NotificationController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('notifications');

        // Messages
        Route::resource('messages', App\Http\Controllers\Admin\MessageController::class)
            ->except(['create', 'store', 'edit', 'update', 'destroy'])
            ->names('messages');
    });

/*
|--------------------------------------------------------------------------
| Auth / Login / Register
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Vérification e-mail Laravel (STANDARD)
|--------------------------------------------------------------------------
*/

// Page "Vérifiez votre adresse e-mail"
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

// Lien dans l'e-mail de vérification
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // marque l'e-mail comme vérifié

    // Redirection après vérification OK
    $user = $request->user();
    if ($user->isClient()) {
        return redirect()->route('client.dashboard')->with('verified', true);
    } elseif ($user->isPrestataire()) {
        return redirect()->route('prestataire.dashboard')->with('verified', true);
    } else {
        return redirect()->route('home')->with('verified', true);
    }
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

// Bouton "Envoyer / Renvoyer" l'e-mail de vérification
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('resent', true);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Routes debug CSRF / token
|--------------------------------------------------------------------------
*/

Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_started' => session()->isStarted(),
        'session_token' => session()->token(),
    ]);
});

Route::get('/debug-csrf', function () {
    session()->start();

    return response()->json([
        'csrf_token' => csrf_token(),
        'session_token' => session()->token(),
        'session_id' => session()->getId(),
        'session_started' => session()->isStarted(),
        'app_key_set' => !empty(config('app.key')),
    ]);
});

Route::post('/test-csrf', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'CSRF token is working!',
        'token_received' => $request->input('_token'),
        'session_token' => session()->token(),
    ]);
});

Route::get('/test-form', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>CSRF Test</title>
        <meta name="csrf-token" content="' . csrf_token() . '">
    </head>
    <body>
        <h1>CSRF Test Form</h1>
        <form method="POST" action="/test-csrf">
            ' . csrf_field() . '
            <input type="text" name="test_field" value="test_value" required>
            <button type="submit">Test Submit</button>
        </form>

        <script>
        document.querySelector("form").addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("/test-csrf", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").getAttribute("content")
                }
            })
            .then(response => response.json())
            .then(data => {
                alert("Success: " + JSON.stringify(data));
            })
            .catch(error => {
                alert("Error: " + error);
            });
        });
        </script>
    </body>
    </html>';
});

/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/

Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

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
| Services (public)
|--------------------------------------------------------------------------
*/

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/services/{service}/report', [ServiceController::class, 'submitReport'])->name('services.report');

/*
|--------------------------------------------------------------------------
| Prestataires (public)
|--------------------------------------------------------------------------
*/

Route::get('/prestataires', [PrestataireController::class, 'index'])->name('prestataires.index');
Route::get('/prestataires/{prestataire}', [PrestataireController::class, 'show'])->name('prestataires.show');

/*
|--------------------------------------------------------------------------
| Équipements (public "simple")
|--------------------------------------------------------------------------
*/

Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');
Route::get('/equipment/{equipment}/reserve', [EquipmentController::class, 'reserve'])->name('equipment.reserve');
Route::post('/equipment/{equipment}/rent', [EquipmentController::class, 'rent'])->name('equipment.rent');

/*
|--------------------------------------------------------------------------
| Recherche globale
|--------------------------------------------------------------------------
*/

Route::get('/search', [SearchController::class, 'searchPrestataires'])->name('search.index');
Route::post('/search', [SearchController::class, 'searchPrestataires'])->name('search.results');
Route::get('/search/prestataires', [SearchController::class, 'searchPrestataires'])->name('search.prestataires');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

/*
|--------------------------------------------------------------------------
| Vidéos
|--------------------------------------------------------------------------
*/

Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/feed', [VideoController::class, 'index'])->name('videos.feed');
Route::get('/approve-all-videos', function () {
    $updatedCount = App\Models\Video::where('status', 'pending')->update(['status' => 'approved']);
    return $updatedCount . ' videos have been approved.';
});
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::post('/videos/{video}/like', [VideoController::class, 'like'])->name('videos.like');
Route::post('/videos/{video}/comments', [VideoController::class, 'comment'])->name('videos.comment')->middleware('auth');
Route::get('/videos/{video}/comments', [VideoController::class, 'getComments'])->name('videos.comments.get');
Route::post('/videos/{video}/increment-views', [VideoController::class, 'incrementViewCount'])->name('videos.increment-views');
Route::post('/prestataires/{prestataire}/follow', [VideoController::class, 'follow'])->name('prestataires.follow');

/*
|--------------------------------------------------------------------------
| Avis (public + auth)
|--------------------------------------------------------------------------
*/

Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::get('/reviews/with-photos', [ReviewController::class, 'withPhotos'])->name('reviews.with-photos');
Route::get('/reviews/certificates', [ReviewController::class, 'certificates'])->name('reviews.certificates');

/*
|--------------------------------------------------------------------------
| Routes protégées (auth)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Booking management (actions générales)
    Route::post('/bookings/{booking}/refuse', [BookingController::class, 'refuse'])->name('bookings.refuse');
    Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::put('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete.client');
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');

    // API agenda prestataire
    Route::get('/api/prestataire/agenda/events', [App\Http\Controllers\Prestataire\AgendaController::class, 'events'])->name('api.prestataire.agenda.events');
    Route::get('/api/prestataire/agenda/recent-bookings', [App\Http\Controllers\Prestataire\AgendaController::class, 'recentBookings'])->name('api.prestataire.agenda.recent-bookings');

    // Dashboard global (redirige selon rôle)
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        } elseif ($user->hasRole('prestataire')) {
            return redirect()->route('prestataire.dashboard');
        } elseif ($user->hasRole('administrateur')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    })->name('dashboard');

    // Routes génériques de profil
    Route::get('/profile/edit', function () {
        $user = auth()->user();

        if ($user->hasRole('client')) {
            return redirect()->route('client.profile.edit');
        } elseif ($user->hasRole('prestataire')) {
            return redirect()->route('prestataire.profile.edit');
        }

        return redirect()->route('dashboard');
    })->name('profile.edit');

    Route::get('/profile/settings', function () {
        $user = auth()->user();

        if ($user->hasRole('client')) {
            return redirect()->route('client.profile.edit');
        } elseif ($user->hasRole('prestataire')) {
            return redirect()->route('prestataire.profile.edit');
        }

        return redirect()->route('profile.edit');
    })->name('profile.settings');

    /*
    |--------------------------------------------------------------------------
    | Client area
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Client\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/personal', [\App\Http\Controllers\Client\ProfileController::class, 'updatePersonalInfo'])->name('profile.update.personal');
        Route::post('/profile/security', [\App\Http\Controllers\Client\ProfileController::class, 'updateSecurity'])->name('profile.update.security');
        Route::delete('/profile/delete-avatar', [\App\Http\Controllers\Client\ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
        Route::delete('/profile/destroy', [\App\Http\Controllers\Client\ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/bookings', [BookingController::class, 'clientBookings'])->name('bookings.index');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

        Route::get('/favorites', [ClientController::class, 'favorites'])->name('favorites');
        Route::post('/favorites/{prestataire}', [ClientController::class, 'toggleFavorite'])->name('favorites.toggle');

        Route::get('/follows', [ClientController::class, 'follows'])->name('follows.index');

        // Suivi prestataires
        Route::post('/prestataire-follows/{prestataire}/follow', [\App\Http\Controllers\Client\PrestataireFollowController::class, 'follow'])->name('prestataire-follows.follow');
        Route::delete('/prestataire-follows/{prestataire}/unfollow', [\App\Http\Controllers\Client\PrestataireFollowController::class, 'unfollow'])->name('prestataire-follows.unfollow');
        Route::get('/prestataire-follows', [\App\Http\Controllers\Client\PrestataireFollowController::class, 'index'])->name('prestataire-follows.index');

        // Messagerie unifiée
        Route::get('messaging', [\App\Http\Controllers\MessagingController::class, 'index'])->name('messaging.index');
        Route::get('messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'show'])->name('messaging.show');
        Route::post('messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'store'])->name('messaging.store');
        Route::delete('messaging/{user}', [\App\Http\Controllers\MessagingController::class, 'deleteConversation'])->name('messaging.delete');
        Route::get('messaging/start/{prestataire}', [\App\Http\Controllers\MessagingController::class, 'startConversationWithPrestataire'])->name('messaging.start');
        Route::get('messaging/start-conversation-from-request/{clientRequestId}', [\App\Http\Controllers\MessagingController::class, 'startConversationFromRequest'])->name('messaging.start-conversation-from-request');
        Route::get('messaging-test', function () {
            return view('messaging.test');
        })->name('messaging.test');

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
    });

    /*
    |--------------------------------------------------------------------------
    | Prestataire area
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:prestataire'])->prefix('prestataire')->name('prestataire.')->group(function () {
        Route::get('videos-manage', [App\Http\Controllers\Prestataire\VideoController::class, 'manage'])->name('videos.manage');

        Route::put('availability/update-weekly', [App\Http\Controllers\Prestataire\AvailabilityController::class, 'updateWeeklyAvailability'])->name('availability.updateWeekly');
        Route::resource('bookings', App\Http\Controllers\Prestataire\BookingController::class)->only(['index', 'show']);
        Route::patch('/bookings/{booking}/accept', [App\Http\Controllers\Prestataire\BookingController::class, 'accept'])->name('bookings.accept');
        Route::patch('/bookings/{booking}/reject', [App\Http\Controllers\Prestataire\BookingController::class, 'reject'])->name('bookings.reject');
        Route::patch('/bookings/{booking}/complete', [App\Http\Controllers\Prestataire\BookingController::class, 'complete'])->name('bookings.complete.prestataire');

        Route::resource('agenda', App\Http\Controllers\Prestataire\AgendaController::class)->only(['index']);
        Route::get('/dashboard', [\App\Http\Controllers\Prestataire\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [\App\Http\Controllers\Prestataire\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Prestataire\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile/photo', [\App\Http\Controllers\Prestataire\ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
        Route::delete('/profile/destroy', [\App\Http\Controllers\Prestataire\ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/profile/preview', [\App\Http\Controllers\Prestataire\ProfileController::class, 'preview'])->name('profile.preview');
        Route::get('/profile/public/{id}', [\App\Http\Controllers\Prestataire\ProfileController::class, 'publicShow'])->name('profile.public');
        Route::get('/profile/{prestataire}', [\App\Http\Controllers\Prestataire\ProfileController::class, 'show'])->name('profile');

        // Vérification prestataire (KYC)
        Route::prefix('verification')->name('verification.')->group(function () {
            Route::get('/', [VerificationController::class, 'index'])->name('index');
            Route::get('/create', [VerificationController::class, 'create'])->name('create');
            Route::post('/', [VerificationController::class, 'store'])->name('store');
            Route::get('/{verificationRequest}', [VerificationController::class, 'show'])->name('show');
            Route::get('/{verificationRequest}/document/{documentIndex}', [VerificationController::class, 'downloadDocument'])->name('download-document');
            Route::post('/check-automatic', [VerificationController::class, 'checkAutomaticCriteria'])->name('check-automatic');
        });

        // Services prestataire
        Route::resource('services', \App\Http\Controllers\Prestataire\ServiceController::class);

        Route::get('services/create/step1', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep1'])->name('services.create.step1');
        Route::post('services/create/step1', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep1'])->name('services.create.step1.store');
        Route::get('services/create/step2', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep2'])->name('services.create.step2');
        Route::post('services/create/step2', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep2'])->name('services.create.step2.store');
        Route::get('services/create/step3', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep3'])->name('services.create.step3');
        Route::post('services/create/step3', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep3'])->name('services.create.step3.store');
        Route::get('services/create/step4', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep4'])->name('services.create.step4');
        Route::post('services/create/step4', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep4'])->name('services.create.step4.store');
        Route::get('services/create/review', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createReview'])->name('services.create.review');

        Route::get('services/{service}/availabilities', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'index'])->name('availabilities.index');
        Route::post('services/{service}/availabilities', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'store'])->name('availabilities.store');
        Route::delete('availabilities/{availability}', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'destroy'])->name('availabilities.destroy');
        Route::delete('/services/images/{image}', [ServiceImageController::class, 'destroy'])->name('services.images.destroy');
        Route::get('/bookings', [\App\Http\Controllers\Prestataire\BookingController::class, 'index'])->name('bookings.index');

        Route::prefix('availability')->name('availability.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'index'])->name('index');
            Route::get('/events', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'events'])->name('events');
            Route::post('/update-settings', [\App\Http\Controllers\Prestataire\AvailabilityController::class, 'updateBookingSettings'])->name('update-settings');
        });

        Route::get('/calendar', [PrestataireController::class, 'calendar'])->name('calendar');
        Route::get('/visibility', [PrestataireController::class, 'visibility'])->name('visibility');

        // Messagerie prestataire (redirigée vers MessagingController)
        Route::get('/messages', [\App\Http\Controllers\MessagingController::class, 'index'])->name('messages.index');
        Route::get('/messages/{user}', [\App\Http\Controllers\MessagingController::class, 'show'])->name('messages.show');
        Route::post('/messages/{user}', [\App\Http\Controllers\MessagingController::class, 'store'])->name('messages.store');
        Route::delete('/messages/{user}', [\App\Http\Controllers\MessagingController::class, 'deleteConversation'])->name('messages.delete');
        Route::post('/messages/start-conversation/{user}', [\App\Http\Controllers\MessagingController::class, 'startConversation'])->name('messages.start-conversation');

        // Gestion des équipements prestataire
        Route::prefix('equipment')->name('equipment.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'store'])->name('store');

            Route::delete('/{equipment}/photos/{photoIndex}', [\App\Http\Controllers\Prestataire\EquipmentImageController::class, 'destroy'])->name('photos.destroy');

            Route::get('/create/step1', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep1'])->name('create.step1');
            Route::post('/create/step1', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep1'])->name('store.step1');
            Route::get('/create/step2', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep2'])->name('create.step2');
            Route::post('/create/step2', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep2'])->name('store.step2');
            Route::get('/create/step3', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep3'])->name('create.step3');
            Route::post('/create/step3', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep3'])->name('store.step3');
            Route::get('/create/step4', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep4'])->name('create.step4');

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
        });

        // Vidéos prestataire
        Route::prefix('videos')->name('videos.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Prestataire\VideoController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Prestataire\VideoController::class, 'create'])->name('create');
            Route::get('/record', [\App\Http\Controllers\Prestataire\VideoController::class, 'record'])->name('record');
            Route::get('/create/step1', [\App\Http\Controllers\Prestataire\VideoController::class, 'createStep1'])->name('create.step1');
            Route::post('/create/step1', [\App\Http\Controllers\Prestataire\VideoController::class, 'storeStep1'])->name('create.step1.store');
            Route::get('/create/step2', [\App\Http\Controllers\Prestataire\VideoController::class, 'createStep2'])->name('create.step2');
            Route::post('/create/step2', [\App\Http\Controllers\Prestataire\VideoController::class, 'storeStep2'])->name('create.step2.store');
            Route::get('/{video}/edit', [\App\Http\Controllers\Prestataire\VideoController::class, 'edit'])->name('edit');
            Route::put('/{video}', [\App\Http\Controllers\Prestataire\VideoController::class, 'update'])->name('update')->middleware('check.file.upload');
            Route::delete('/{video}', [\App\Http\Controllers\Prestataire\VideoController::class, 'destroy'])->name('destroy');
        });

        // Aide prestataire
        Route::get('/help', [\App\Http\Controllers\Prestataire\HelpController::class, 'index'])->name('help.index');

        // QR Code prestataire
        Route::get('/qrcode', [QrCodeController::class, 'show'])->name('qrcode.show');

        // Agenda prestataire
        Route::get('/agenda', [\App\Http\Controllers\Prestataire\AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/agenda/events', [\App\Http\Controllers\Prestataire\AgendaController::class, 'events'])->name('agenda.events');
        Route::get('/agenda/booking/{booking}', [\App\Http\Controllers\Prestataire\AgendaController::class, 'show'])->name('agenda.booking.show');
        Route::put('/agenda/booking/{booking}/status', [\App\Http\Controllers\Prestataire\AgendaController::class, 'updateStatus'])->name('agenda.booking.update-status');
        Route::get('/agenda/equipment-request/{request}', [\App\Http\Controllers\Prestataire\AgendaController::class, 'showEquipmentRequest'])->name('agenda.equipment-request.show');
        Route::get('/agenda/equipment-rental/{rental}', [\App\Http\Controllers\Prestataire\AgendaController::class, 'showEquipmentRental'])->name('agenda.equipment-rental.show');
        Route::put('/agenda/equipment-request/{request}/accept', [\App\Http\Controllers\Prestataire\AgendaController::class, 'acceptEquipmentRequest'])->name('agenda.equipment-request.accept');
        Route::put('/agenda/equipment-request/{request}/reject', [\App\Http\Controllers\Prestataire\AgendaController::class, 'rejectEquipmentRequest'])->name('agenda.equipment-request.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Équipement (public côté site, mais routes à accès auth)
    |--------------------------------------------------------------------------
    */

    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EquipmentController::class, 'index'])->name('index');
        Route::get('/{equipment}', [\App\Http\Controllers\EquipmentController::class, 'show'])->name('show');
        Route::get('/{equipment}/reserve', [\App\Http\Controllers\EquipmentController::class, 'showReservationForm'])->name('reserve');
        Route::post('/{equipment}/rent', [\App\Http\Controllers\EquipmentController::class, 'rent'])->name('rent');
        Route::post('/{equipment}/report', [\App\Http\Controllers\EquipmentController::class, 'submitReport'])->name('report');
    });

    /*
    |--------------------------------------------------------------------------
    | Urgent sales (annonces publiques avec actions auth)
    |--------------------------------------------------------------------------
    */

    Route::prefix('urgent-sales')->name('urgent-sales.')->group(function () {
        Route::get('/', [\App\Http\Controllers\UrgentSaleController::class, 'index'])->name('index');
        Route::get('/{urgentSale}', [\App\Http\Controllers\UrgentSaleController::class, 'show'])->name('show');
        Route::post('/{urgentSale}/contact', [\App\Http\Controllers\UrgentSaleController::class, 'contact'])->name('contact');
        Route::post('/{urgentSale}/report', [\App\Http\Controllers\UrgentSaleController::class, 'report'])->name('report');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications (générales, tous users auth)
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/recent', [\App\Http\Controllers\NotificationController::class, 'getRecent'])->name('recent');
    });

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
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::put('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        Route::put('/{booking}/complete', [BookingController::class, 'complete'])->name('complete');
    });

    /*
    |--------------------------------------------------------------------------
    | Messagerie (MessageController)
    |--------------------------------------------------------------------------
    */

    Route::prefix('messaging')->name('messaging.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/start/{user}', [MessageController::class, 'start'])->name('start');
        Route::get('/conversation', function () {
            return redirect()->route('messaging.index');
        });
        Route::get('/conversation/{user}', [MessageController::class, 'conversation'])->name('conversation');
        Route::post('/send/{receiver}', [MessageController::class, 'send'])->name('send');
        Route::post('/send-ajax', [MessageController::class, 'sendAjax'])->name('send.ajax');
        Route::get('/new-messages/{user}', [MessageController::class, 'getNewMessages'])->name('new-messages');
        Route::get('/unread-count', [MessageController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/user-status/{user}', [MessageController::class, 'getUserOnlineStatus'])->name('user-status');
        Route::post('/mark-as-read', [MessageController::class, 'markAsRead'])->name('mark-as-read');
    });

    /*
    |--------------------------------------------------------------------------
    | Avis (CRUD complet côté auth)
    |--------------------------------------------------------------------------
    */

    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::post('/', [ReviewController::class, 'store'])->name('store');
        Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
        Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Recherches sauvegardées
    |--------------------------------------------------------------------------
    */

    Route::prefix('saved-searches')->name('saved-searches.')->group(function () {
        Route::get('/', [SavedSearchController::class, 'index'])->name('index');
        Route::post('/', [SavedSearchController::class, 'store'])->name('store');
        Route::delete('/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('destroy');
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

Route::get('/test/availability', function () {
    return view('test.availability_test');
})->name('test.availability');

// Wizards services
Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('prestataire/services/create/step1', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep1'])->name('prestataire.services.create.step1');
    Route::post('prestataire/services/create/step1', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep1'])->name('prestataire.services.create.step1.store');
    Route::get('prestataire/services/create/step2', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep2'])->name('prestataire.services.create.step2');
    Route::post('prestataire/services/create/step2', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep2'])->name('prestataire.services.create.step2.store');
    Route::get('prestataire/services/create/step3', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep3'])->name('prestataire.services.create.step3');
    Route::post('prestataire/services/create/step3', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep3'])->name('prestataire.services.create.step3.store');
    Route::get('prestataire/services/create/step4', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createStep4'])->name('prestataire.services.create.step4');
    Route::post('prestataire/services/create/step4', [\App\Http\Controllers\Prestataire\ServiceController::class, 'storeStep4'])->name('prestataire.services.create.step4.store');
    Route::get('prestataire/services/create/review', [\App\Http\Controllers\Prestataire\ServiceController::class, 'createReview'])->name('prestataire.services.create.review');
    Route::post('prestataire/services/create', [\App\Http\Controllers\Prestataire\ServiceController::class, 'store'])->name('prestataire.services.create.store');
});

// Wizard équipement
Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('prestataire/equipment/create/step1', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep1'])->name('prestataire.equipment.create.step1');
    Route::post('prestataire/equipment/create/step1', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep1'])->name('prestataire.equipment.store.step1');
    Route::get('prestataire/equipment/create/step2', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep2'])->name('prestataire.equipment.create.step2');
    Route::post('prestataire/equipment/create/step2', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep2'])->name('prestataire.equipment.store.step2');
    Route::get('prestataire/equipment/create/step3', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep3'])->name('prestataire.equipment.create.step3');
    Route::post('prestataire/equipment/create/step3', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'storeStep3'])->name('prestataire.equipment.store.step3');
    Route::get('prestataire/equipment/create/step4', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'createStep4'])->name('prestataire.equipment.create.step4');
    Route::post('prestataire/equipment', [\App\Http\Controllers\Prestataire\EquipmentController::class, 'store'])->name('prestataire.equipment.store');
});

// Wizard urgent-sales
Route::middleware(['auth', 'role:prestataire'])->group(function () {
    Route::get('prestataire/urgent-sales/create', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'create'])->name('prestataire.urgent-sales.create');
    Route::get('prestataire/urgent-sales/create/step1', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep1'])->name('prestataire.urgent-sales.create.step1');
    Route::post('prestataire/urgent-sales/create/step1', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'storeStep1'])->name('prestataire.urgent-sales.create.step1.store');
    Route::get('prestataire/urgent-sales/create/step2', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep2'])->name('prestataire.urgent-sales.create.step2');
    Route::post('prestataire/urgent-sales/create/step2', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'storeStep2'])->name('prestataire.urgent-sales.create.step2.store');
    Route::get('prestataire/urgent-sales/create/step3', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep3'])->name('prestataire.urgent-sales.create.step3');
    Route::post('prestataire/urgent-sales/create/step3', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'storeStep3'])->name('prestataire.urgent-sales.create.step3.store');
    Route::get('prestataire/urgent-sales/create/step4', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'createStep4'])->name('prestataire.urgent-sales.create.step4');
    Route::post('prestataire/urgent-sales', [\App\Http\Controllers\Prestataire\UrgentSaleController::class, 'store'])->name('prestataire.urgent-sales.store');
});

// Ping de test
Route::get('/ping', function () {
    return 'pong';
});
