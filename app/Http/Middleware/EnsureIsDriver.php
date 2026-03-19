<?php

namespace App\Http\Middleware;

use App\Models\DeliveryDriver;
use App\Models\Prestataire;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsDriver
{
    /**
     * Vérifie que l'utilisateur authentifié est un livreur enregistré et actif.
     * Les routes d'inscription livreur sont exclues.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $driver = null;
            if ($request->user()) {
                $driver = DeliveryDriver::where('user_id', $request->user()->id)->first();
            }

            if (!$driver) {
                $internalDriverId = (int) $request->session()->get('internal_driver_id', 0);
                if ($internalDriverId > 0) {
                    $query = DeliveryDriver::where('id', $internalDriverId);
                    if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
                        $query->where('is_internal', true);
                    }
                    if (Schema::hasColumn('delivery_drivers', 'is_active')) {
                        $query->where('is_active', true);
                    }
                    $driver = $query->first();
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('EnsureIsDriver middleware error: ' . $e->getMessage());
            return redirect()->route('home')
                ->with('error', 'Service livreur temporairement indisponible.');
        }

        if (!$driver) {
            if ($request->user()) {
                return redirect()->route('driver.register')
                    ->with('error', 'Vous devez vous inscrire comme livreur pour accéder à cette page.');
            }

            return redirect()->route('driver.internal.access')
                ->with('error', 'Accès livreur requis. Entrez votre code interne.');
        }

        if ($driver->isSuspended()) {
            if ($request->user()) {
                return redirect()->route('home')
                    ->with('error', 'Votre compte livreur est suspendu. Contactez le support.');
            }

            $request->session()->forget(['internal_driver_id', 'internal_driver_logged_at']);
            return redirect()->route('driver.internal.access')
                ->with('error', 'Votre accès livreur est suspendu.');
        }

        // Règle métier: un livreur "externe" doit avoir Stripe actif
        // (soit Stripe du profil livreur, soit Stripe du compte prestataire lié au même user).
        // Exception: mode interne (code prestataire) => Stripe non requis.
        $isInternalAssignedOnly = !empty($driver->employer_prestataire_id) && (bool) ($driver->is_internal ?? false);
        $driverStripeReady = !empty($driver->stripe_account_id) && (bool) ($driver->stripe_onboarding_complete ?? false);
        $user = $request->user();
        $prestataire = $user ? ($user->prestataire ?? Prestataire::where('user_id', $user->id)->first()) : null;
        $isPrestataireUser = $user
            ? (method_exists($user, 'isPrestataire') ? (bool) $user->isPrestataire() : ((string) ($user->role ?? '') === 'prestataire'))
            : false;
        $prestataireStripeReady = $prestataire && !empty($prestataire->stripe_account_id);

        if (!$isInternalAssignedOnly && !$driverStripeReady && !$prestataireStripeReady) {
            $routeName = (string) optional($request->route())->getName();
            $isStripeRoute = str_starts_with($routeName, 'driver.stripe.');

            if (!$isStripeRoute) {
                if ($isPrestataireUser && $prestataire && Route::has('prestataire.payments.connect')) {
                    return redirect()->route('prestataire.payments.connect')
                        ->with('error', 'Compte Stripe requis pour utiliser le mode livreur externe.');
                }

                return redirect()->route('driver.stripe.setup')
                    ->with('error', 'Activez Stripe pour accéder au mode livreur externe.');
            }
        }

        // Injecter le driver dans la requête pour éviter les requêtes répétées
        $request->attributes->set('driver', $driver);

        return $next($request);
    }
}
