<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HashIdService;

class HashIdServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(HashIdService::class, function ($app) {
            return new HashIdService();
        });
        
        // Alias pour faciliter l'accès
        $this->app->alias(HashIdService::class, 'hashid');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publier la config
        $this->publishes([
            __DIR__.'/../../config/hashids.php' => config_path('hashids.php'),
        ], 'hashids-config');
    }
}
