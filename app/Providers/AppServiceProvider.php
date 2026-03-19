<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Service;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Models\UrgentSaleReservation;
use App\Models\Video;
use App\Models\Notification;
use App\Observers\ServiceObserver;
use App\Observers\EquipmentObserver;
use App\Observers\UrgentSaleObserver;
use App\Observers\UrgentSaleReservationObserver;
use App\Observers\VideoObserver;
use App\Observers\NotificationObserver;
use App\Listeners\SendOneSignalPush;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Apple\AppleExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(SocialiteWasCalled::class, AppleExtendSocialite::class);

        // OneSignal Push Notifications - Écoute toutes les notifications envoyées
        Event::listen(NotificationSent::class, SendOneSignalPush::class);

        if (!$this->app->environment('production')) {
            Log::info('AppServiceProvider: Registering observers');
        }
        Service::observe(ServiceObserver::class);
        Equipment::observe(EquipmentObserver::class);
        UrgentSale::observe(UrgentSaleObserver::class);
        UrgentSaleReservation::observe(UrgentSaleReservationObserver::class);
        Video::observe(VideoObserver::class);
        Notification::observe(NotificationObserver::class);
        if (!$this->app->environment('production')) {
            Log::info('AppServiceProvider: Observers registered');
        }

        // Share AI config globally with all views and clients
        View::share('ai', config('ai'));
    }
}
