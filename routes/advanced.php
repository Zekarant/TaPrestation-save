<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\GeolocationController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\MatchingAlertController;

/*
|--------------------------------------------------------------------------
| Advanced Features Routes
|--------------------------------------------------------------------------
|
| Here are the routes for advanced features like messaging, search,
| geolocation, saved searches, and matching alerts.
|
*/

// Routes pour la messagerie avancée
Route::middleware(['auth'])->group(function () {
    
    // API Routes pour la messagerie
    Route::prefix('api/messaging')->group(function () {
        
        Route::put('/messages/{message}', [MessagingController::class, 'updateMessage'])->name('api.messaging.update');
        Route::delete('/messages/{message}', [MessagingController::class, 'deleteMessage'])->name('api.messaging.delete');
        Route::delete('/conversations/{user}', [MessagingController::class, 'deleteConversation'])->name('api.messaging.delete-conversation');
        Route::post('/mark-read', [MessagingController::class, 'markAsRead'])->name('api.messaging.mark-read');
        Route::post('/video-call/initiate', [MessagingController::class, 'initiateVideoCall'])->name('api.messaging.video-call.initiate');
        Route::get('/search-users', [MessagingController::class, 'searchUsers'])->name('api.messaging.search-users');
    });
    
    // Routes pour les conversations de groupe

    
    // Routes pour la géolocalisation
    Route::prefix('api/geolocation')->group(function () {
        Route::post('/update', [GeolocationController::class, 'updateLocation'])->name('api.geolocation.update');
        Route::get('/nearby-prestataires', [GeolocationController::class, 'getNearbyPrestataires'])->name('api.geolocation.nearby-prestataires');
        Route::get('/distance/{prestataire}', [GeolocationController::class, 'getDistance'])->name('api.geolocation.distance');
    });
    
    // Routes pour les recherches sauvegardées
    Route::prefix('saved-searches')->group(function () {
        Route::get('/', [SavedSearchController::class, 'index'])->name('saved-searches.index');
        Route::post('/', [SavedSearchController::class, 'store'])->name('saved-searches.store');
        Route::get('/{savedSearch}', [SavedSearchController::class, 'show'])->name('saved-searches.show');
        Route::put('/{savedSearch}', [SavedSearchController::class, 'update'])->name('saved-searches.update');
        Route::delete('/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');
        Route::post('/{savedSearch}/toggle-alerts', [SavedSearchController::class, 'toggleAlerts'])->name('saved-searches.toggle-alerts');
        Route::post('/{savedSearch}/run', [SavedSearchController::class, 'runSearch'])->name('saved-searches.run');
    });
    
    // Routes pour les alertes de correspondance
    Route::prefix('matching-alerts')->group(function () {
        Route::get('/', [MatchingAlertController::class, 'index'])->name('matching-alerts.index');
        Route::get('/{alert}', [MatchingAlertController::class, 'show'])->name('matching-alerts.show');
        Route::post('/{alert}/mark-read', [MatchingAlertController::class, 'markAsRead'])->name('matching-alerts.mark-read');
        Route::post('/{alert}/dismiss', [MatchingAlertController::class, 'dismiss'])->name('matching-alerts.dismiss');
        Route::delete('/{alert}', [MatchingAlertController::class, 'destroy'])->name('matching-alerts.destroy');
        Route::get('/stats/summary', [MatchingAlertController::class, 'getStats'])->name('matching-alerts.stats');
    });
    

});

// Routes publiques pour la géolocalisation
Route::prefix('api/public/geolocation')->group(function () {
    Route::get('/cities', [GeolocationController::class, 'getCities'])->name('api.public.geolocation.cities');
    Route::get('/regions', [GeolocationController::class, 'getRegions'])->name('api.public.geolocation.regions');
});