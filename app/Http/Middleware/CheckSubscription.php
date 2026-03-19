<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

use App\Support\TableExistenceCache;
class CheckSubscription
{
    private const ALLOWED_ROUTE_NAMES = [
        'prestataire.subscription.payment',
        'prestataire.subscription.process-payment',
        'prestataire.subscriptions.index',
        'prestataire.subscriptions.plans',
        'prestataire.subscriptions.subscribe',
        'prestataire.subscriptions.cancel',
        'prestataire.subscriptions.my',
        'prestataire.subscriptions.benefits',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = $request->user();

            // Pas connecté = laisser passer
            if (!$user) {
                return $next($request);
            }

            // Pas prestataire = laisser passer
            if (!isset($user->role) || $user->role !== 'prestataire') {
                return $next($request);
            }

            if ($this->isAllowedRoute($request)) {
                return $next($request);
            }

            // Vérifier si le mode abonnement est activé
            if (!$this->isSubscriptionEnabled()) {
                // Abonnement désactivé = TOUT EST GRATUIT
                return $next($request);
            }

            // Vérifier si l'utilisateur a un abonnement actif
            if ($this->hasActiveSubscription($user)) {
                return $next($request);
            }

            return redirect()->route('prestataire.subscription.payment')
                ->with('warning', 'Veuillez activer votre abonnement pour accéder à cette fonctionnalité.');

        } catch (\Throwable $e) {
            Log::error('CheckSubscription error: ' . $e->getMessage());
            abort(503, 'Vérification d’abonnement temporairement indisponible.');
        }
    }

    /**
     * Vérifier si le mode abonnement est activé
     */
    protected function isSubscriptionEnabled(): bool
    {
        return Cache::remember('subscription_enabled', 30, function () {
            if (!TableExistenceCache::has('settings')) {
                throw new \RuntimeException('Table settings indisponible pour vérifier les abonnements.');
            }

            $value = DB::table('settings')
                ->where('key', 'subscription_enabled')
                ->value('value');

            return $value === '1';
        });
    }

    /**
     * Vérifier si l'utilisateur a un abonnement actif
     */
    protected function hasActiveSubscription($user): bool
    {
        return Cache::remember("user_subscription_{$user->id}", 30, function () use ($user) {
            if (!TableExistenceCache::has('user_subscriptions')) {
                throw new \RuntimeException('Table user_subscriptions indisponible pour vérifier les abonnements.');
            }

            $subscription = DB::table('user_subscriptions')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('current_period_end')
                        ->orWhere('current_period_end', '>', now());
                })
                ->first();

            return $subscription !== null;
        });
    }

    protected function isAllowedRoute(Request $request): bool
    {
        $routeName = (string) ($request->route()?->getName() ?? '');

        return in_array($routeName, self::ALLOWED_ROUTE_NAMES, true);
    }
}
