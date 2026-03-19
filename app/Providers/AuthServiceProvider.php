<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\MatchingAlert;
use App\Models\Equipment;
use App\Models\SavedSearch;
use App\Models\Video;
use App\Models\UrgentSale;
use App\Models\Quote;
use App\Policies\BookingPolicy;
use App\Policies\EquipmentPolicy;
use App\Policies\MatchingAlertPolicy;
use App\Policies\SavedSearchPolicy;
use App\Policies\VideoPolicy;
use App\Policies\UrgentSalePolicy;
use App\Policies\QuotePolicy;
use App\Models\Service;
use App\Policies\ServicePolicy;
use App\Models\TenderRequest;
use App\Policies\TenderRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Service::class => ServicePolicy::class,
        Equipment::class => EquipmentPolicy::class,
        Video::class => VideoPolicy::class,
        UrgentSale::class => UrgentSalePolicy::class,
        Booking::class => BookingPolicy::class,
        Quote::class => QuotePolicy::class,
        TenderRequest::class => TenderRequestPolicy::class,
        MatchingAlert::class => MatchingAlertPolicy::class,
        SavedSearch::class => SavedSearchPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate pour les fonctionnalités avancées (requiert abonnement actif ou rôle admin)
        Gate::define('manage-advanced-features', function ($user) {
            return $user->hasRole('administrateur') || $user->hasActiveSubscription();
        });

        Gate::define('admin-access', function ($user) {
            return $user->role === 'admin' || $user->role === 'administrateur';
        });

        Gate::define('prestataire-access', function ($user) {
            return $user->role === 'prestataire';
        });

        Gate::define('client-access', function ($user) {
            return $user->role === 'client';
        });

        // Gate pour les fonctionnalités de géolocalisation
        Gate::define('use-geolocation', function ($user) {
            return $user !== null;
        });

        // Gate pour les recherches sauvegardées
        Gate::define('create-saved-searches', function ($user) {
            return $user !== null;
        });

        // Gate pour les alertes de correspondance
        Gate::define('receive-matching-alerts', function ($user) {
            return $user !== null;
        });

        // Gate pour les fonctionnalités de communication avancées
        Gate::define('use-advanced-messaging', function ($user) {
            return $user !== null;
        });

        // Gate pour les appels vidéo
        Gate::define('initiate-video-calls', function ($user) {
            return $user !== null;
        });

        // Gate pour les conversations de groupe
        Gate::define('create-group-conversations', function ($user) {
            return $user !== null;
        });
    }
}