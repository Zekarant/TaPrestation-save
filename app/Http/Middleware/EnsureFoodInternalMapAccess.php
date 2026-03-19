<?php

namespace App\Http\Middleware;

use App\Models\DeliveryDriver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureFoodInternalMapAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (($user->role ?? null) === 'prestataire' || (method_exists($user, 'isPrestataire') && $user->isPrestataire()))) {
            return $next($request);
        }

        $internalDriverId = (int) $request->session()->get('internal_driver_id', 0);

        if ($internalDriverId <= 0) {
            return redirect()->route('driver.internal.access')
                ->with('error', 'Acces interne requis.');
        }

        $query = DeliveryDriver::query()->where('id', $internalDriverId);

        if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
            $query->where('is_internal', true);
        }

        if (Schema::hasColumn('delivery_drivers', 'is_active')) {
            $query->where('is_active', true);
        }

        if (!$query->exists()) {
            $request->session()->forget(['internal_driver_id', 'internal_driver_logged_at']);

            return redirect()->route('driver.internal.access')
                ->with('error', 'Votre session interne a expire.');
        }

        return $next($request);
    }
}
