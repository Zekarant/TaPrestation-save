<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Service;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Models\Video;
use App\Observers\ServiceObserver;
use App\Observers\EquipmentObserver;
use App\Observers\UrgentSaleObserver;
use App\Observers\VideoObserver;
use Illuminate\Support\Facades\Log;

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
        Log::info('AppServiceProvider: Registering observers');
        Service::observe(ServiceObserver::class);
        Equipment::observe(EquipmentObserver::class);
        UrgentSale::observe(UrgentSaleObserver::class);
        Video::observe(VideoObserver::class);
        Log::info('AppServiceProvider: Observers registered');
    }
}