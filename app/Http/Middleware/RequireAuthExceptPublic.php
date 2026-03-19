<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthExceptPublic
{
    /**
     * Enforce authentication for the platform, except a small public allowlist
     * (videos + public prestataire profiles + auth flows).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        // Home page
        if ($path === '') {
            return $next($request);
        }

        // Health check
        if ($path === 'up') {
            return $next($request);
        }

        // Public: internal one-shot demo seed bridge (protected by token in URL + config file)
        if (str_starts_with($path, 'internal/demo/seed-marketplace/')) {
            return $next($request);
        }

        // Auth / registration / password reset
        if (
            $path === 'login' ||
            str_starts_with($path, 'login/') ||
            $path === 'register' ||
            str_starts_with($path, 'password/') ||
            $path === 'csrf-token' ||
            str_starts_with($path, 'email/verify') ||
            str_starts_with($path, 'social/')
        ) {
            return $next($request);
        }

        // Public: videos
        if ($path === 'videos' || str_starts_with($path, 'videos/')) {
            return $next($request);
        }

        // Public: boutique (urgent sales)
        if ($path === 'urgent-sales' || str_starts_with($path, 'urgent-sales/')) {
            return $next($request);
        }

        // Public: services
        if ($path === 'services' || str_starts_with($path, 'services/')) {
            return $next($request);
        }

        // Public: equipment
        if ($path === 'equipment' || str_starts_with($path, 'equipment/')) {
            return $next($request);
        }

        // Public: food
        if ($path === 'food' || str_starts_with($path, 'food/')) {
            return $next($request);
        }

        // Public entry: internal delivery map (used by internal drivers with access code/session).
        if (
            $path === 'prestataire/food/food-orders/internal-map'
            || $path === 'prestataire/food/food-orders/internal-map/data'
        ) {
            return $next($request);
        }

        // Public entry points for driver flows.
        // Internal drivers authenticate via code/session (not classic user auth),
        // then route-level middleware (driver/auth) handles access control.
        if ($path === 'driver' || str_starts_with($path, 'driver/')) {
            return $next($request);
        }

        // Public: search
        if ($path === 'search' || str_starts_with($path, 'search/')) {
            return $next($request);
        }

        // Public: prestataire profiles (content only)
        if ($path === 'prestataires' || str_starts_with($path, 'prestataires/')) {
            return $next($request);
        }

        // Public: user profiles
        if (str_starts_with($path, 'users/') && str_ends_with($path, '/profile')) {
            return $next($request);
        }

        // Public: categories
        if ($path === 'categories' || str_starts_with($path, 'categories/')) {
            return $next($request);
        }

        // Public: legal & content pages
        if (in_array($path, ['faq', 'contact', 'cgu', 'cgv', 'privacy', 'terms', 'legal', 'cookies', 'mentions-legales'])) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
