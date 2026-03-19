<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/advanced.php'));
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
            'refresh.csrf' => \App\Http\Middleware\RefreshCsrfToken::class,
            'check.file.upload' => \App\Http\Middleware\CheckFileUploadSize::class,
            'update.online.status' => \App\Http\Middleware\UpdateUserOnlineStatus::class,
            'nocache' => \App\Http\Middleware\NoCacheHeaders::class,
            'profile.complete' => \App\Http\Middleware\EnsureProfileComplete::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'driver' => \App\Http\Middleware\EnsureIsDriver::class,
            'food.internal.map' => \App\Http\Middleware\EnsureFoodInternalMapAccess::class,
        ]);
        
        // Apply online status middleware and no-cache headers to web routes
        $middleware->web(append: [
            \App\Http\Middleware\RedirectToCanonicalHost::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\UpdateUserOnlineStatus::class,
            \App\Http\Middleware\NoCacheHeaders::class,
            \App\Http\Middleware\RequireAuthExceptPublic::class,
        ]);
        
        // Use custom CSRF middleware with register route exception
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch'], 419);
            }
            
            // For registration form, redirect back with error
            if ($request->is('register')) {
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors(['csrf' => 'Votre session a expiré. Veuillez réessayer.']);
            }
            
            return redirect()->route('login')->withErrors(['csrf' => 'Votre session a expiré. Veuillez vous reconnecter.']);
        });
    })->withProviders([
        Spatie\Permission\PermissionServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        // App\Providers\HashIdServiceProvider::class, // Désactivé temporairement

    ])->create();
