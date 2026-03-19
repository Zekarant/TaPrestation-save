<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Support\TableExistenceCache;
class AdminDriverController extends Controller
{
    /**
     * Liste de tous les livreurs avec filtres
     */
    public function index(Request $request)
    {
        if (!TableExistenceCache::has('delivery_drivers')) {
            return view('admin.drivers.index', [
                'drivers' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'stats' => $this->emptyStats(),
                'tableNotExists' => true,
            ]);
        }

        $query = DeliveryDriver::with('user');

        // Filtres
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('is_active', true)->where('is_suspended', false);
            } elseif ($status === 'suspended') {
                $query->where('is_suspended', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'pending') {
                $query->where('is_active', false)->where('is_suspended', false);
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $drivers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total' => DeliveryDriver::count(),
            'active' => DeliveryDriver::where('is_active', true)->where('is_suspended', false)->count(),
            'suspended' => DeliveryDriver::where('is_suspended', true)->count(),
            'pending' => DeliveryDriver::where('is_active', false)->where('is_suspended', false)->count(),
            'online' => DeliveryDriver::where('is_active', true)
                ->where('status', DeliveryDriver::STATUS_AVAILABLE ?? 'available')
                ->count(),
        ];

        return view('admin.drivers.index', compact('drivers', 'stats'));
    }

    /**
     * Détail d'un livreur
     */
    public function show(DeliveryDriver $driver)
    {
        $driver->load(['user', 'sponsorPrestataire']);

        $recentOrders = $driver->foodOrders()
            ->with(['prestataire', 'client'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $monthlyStats = [
            'deliveries' => $driver->foodOrders()
                ->where('delivery_status', 'delivered')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'earnings' => $driver->foodOrders()
                ->where('delivery_status', 'delivered')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('driver_commission'),
            'avg_time' => $driver->average_delivery_time ?? 0,
        ];

        return view('admin.drivers.show', compact('driver', 'recentOrders', 'monthlyStats'));
    }

    /**
     * Approuver un livreur en attente
     */
    public function approve(DeliveryDriver $driver)
    {
        $driver->update([
            'is_active' => true,
            'is_suspended' => false,
            'activated_at' => now(),
        ]);

        // Notifier le livreur
        try {
            if ($driver->user) {
                $driver->user->notify(new \App\Notifications\DriverApprovedNotification($driver));
            }
        } catch (\Exception $e) {
            Log::warning('Notification DriverApproved non envoyée: ' . $e->getMessage());
        }

        return back()->with('success', "Livreur {$driver->first_name} {$driver->last_name} approuvé avec succès.");
    }

    /**
     * Suspendre un livreur
     */
    public function suspend(Request $request, DeliveryDriver $driver)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $driver->update([
            'is_suspended' => true,
            'suspension_reason' => $request->reason,
            'suspended_at' => now(),
        ]);

        // Notifier le livreur
        try {
            if ($driver->user) {
                $driver->user->notify(new \App\Notifications\DriverSuspendedNotification($driver));
            }
        } catch (\Exception $e) {
            Log::warning('Notification DriverSuspended non envoyée: ' . $e->getMessage());
        }

        return back()->with('success', "Livreur {$driver->first_name} {$driver->last_name} suspendu.");
    }

    /**
     * Réactiver un livreur suspendu
     */
    public function reactivate(DeliveryDriver $driver)
    {
        $driver->update([
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);

        return back()->with('success', "Livreur {$driver->first_name} {$driver->last_name} réactivé.");
    }

    /**
     * Supprimer un livreur
     */
    public function destroy(DeliveryDriver $driver)
    {
        // Vérifier pas de commandes en cours
        $activeOrders = $driver->foodOrders()
            ->whereNotIn('delivery_status', ['delivered', 'failed', 'cancelled'])
            ->count();

        if ($activeOrders > 0) {
            return back()->with('error', "Impossible de supprimer : {$activeOrders} commande(s) en cours.");
        }

        $name = $driver->first_name . ' ' . $driver->last_name;
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', "Livreur {$name} supprimé.");
    }

    private function emptyStats(): array
    {
        return ['total' => 0, 'active' => 0, 'suspended' => 0, 'pending' => 0, 'online' => 0];
    }
}
