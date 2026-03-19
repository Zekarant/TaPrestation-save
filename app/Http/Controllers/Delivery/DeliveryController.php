<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\DeliveryService;
use Illuminate\Http\Request;

use App\Support\TableExistenceCache;
class DeliveryController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * Get available delivery providers
     */
    public function getAvailableProviders(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $providers = $this->deliveryService->getAvailableProviders(
            $validated['latitude'],
            $validated['longitude']
        );

        return response()->json(['providers' => $providers]);
    }

    /**
     * Calculate shipping cost
     */
    public function calculateShipping(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:delivery_providers,id',
            'weight' => 'required|numeric|min:0.1',
            'distance' => 'required|numeric|min:0',
            'shipping_type' => 'required|in:standard,express,overnight',
        ]);

        $provider = \App\Models\DeliveryProvider::find($validated['provider_id']);

        $cost = $this->deliveryService->calculateShippingCost(
            $provider,
            $validated['weight'],
            $validated['distance'],
            $validated['shipping_type']
        );

        $estimatedDelivery = $this->deliveryService->getEstimatedDelivery(
            $provider,
            $validated['distance']
        );

        return response()->json([
            'cost' => $cost,
            'estimated_delivery' => $estimatedDelivery->format('Y-m-d H:i'),
        ]);
    }

    /**
     * Track delivery
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string',
        ]);

        $trackingInfo = $this->deliveryService->trackDelivery(
            $validated['tracking_number']
        );

        return response()->json($trackingInfo);
    }

    /**
     * Setup delivery for a booking
     */
    public function setupDelivery(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        $validated = $request->validate([
            'provider_id' => 'required|exists:delivery_providers,id',
            'shipping_type' => 'required|in:standard,express,overnight',
            'recipient_address' => 'required|string',
        ]);

        try {
            $deliveryOrder = $this->deliveryService->createDeliveryOrder(
                $validated['provider_id'],
                [
                    'booking_id' => $booking->id,
                    'recipient_address' => $validated['recipient_address'],
                    'shipping_type' => $validated['shipping_type'],
                    'recipient_name' => $booking->client->name ?? 'Client',
                ]
            );

            $booking->update([
                'status' => 'ready_for_delivery',
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'tracking_number' => $deliveryOrder->tracking_number,
                ]);
            }

            return redirect()->route('prestataire.delivery.index')
                ->with('success', 'Expédition créée avec succès. N° de suivi : ' . $deliveryOrder->tracking_number);

        } catch (\Exception $e) {
            report($e);

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Création de l\'expédition impossible pour le moment.',
                ], 422);
            }

            return back()
                ->withErrors(['error' => 'Création de l\'expédition impossible pour le moment.'])
                ->withInput();
        }
    }

    // ============================================================================
    // CLIENT METHODS
    // ============================================================================

    /**
     * Client delivery index
     */
    public function clientIndex()
    {
        $user = auth()->user();
        $client = $user->client;

        // Vérifier si la table delivery_orders existe
        if (!TableExistenceCache::has('delivery_orders')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('client.delivery.index', [
                'deliveries' => $emptyPaginator,
                'stats' => ['pending' => 0, 'in_transit' => 0, 'delivered' => 0],
                'tableNotExists' => true,
            ]);
        }

        try {
            $deliveries = \App\Models\DeliveryOrder::whereHas('booking', function($q) use ($client) {
                $q->where('client_id', $client?->id);
            })->with(['booking.service', 'provider'])->latest()->paginate(15);

            $stats = [
                'pending' => \App\Models\DeliveryOrder::whereHas('booking', function($q) use ($client) {
                    $q->where('client_id', $client?->id);
                })->where('status', 'pending')->count(),
                'in_transit' => \App\Models\DeliveryOrder::whereHas('booking', function($q) use ($client) {
                    $q->where('client_id', $client?->id);
                })->where('status', 'in_transit')->count(),
                'delivered' => \App\Models\DeliveryOrder::whereHas('booking', function($q) use ($client) {
                    $q->where('client_id', $client?->id);
                })->where('status', 'delivered')->count(),
            ];

            return view('client.delivery.index', compact('deliveries', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('client.delivery.index', [
                'deliveries' => $emptyPaginator,
                'stats' => ['pending' => 0, 'in_transit' => 0, 'delivered' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Alias for client delivery orders (used by routes "delivery.orders")
     */
    public function myOrders()
    {
        return $this->clientIndex();
    }

    // ============================================================================
    // PRESTATAIRE METHODS
    // ============================================================================

    /**
     * Prestataire delivery index
     */
    public function prestataireIndex()
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;

        // Vérifier si la table delivery_orders existe
        if (!TableExistenceCache::has('delivery_orders')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('prestataire.delivery.index', [
                'deliveries' => $emptyPaginator,
                'stats' => ['to_ship' => 0, 'shipped' => 0, 'completed' => 0],
                'tableNotExists' => true,
            ]);
        }

        try {
            $deliveries = \App\Models\DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })->with(['booking.service', 'booking.client', 'provider'])->latest()->paginate(15);

            $stats = [
                'to_ship' => \App\Models\DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->where('status', 'pending')->count(),
                'shipped' => \App\Models\DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->where('status', 'in_transit')->count(),
                'completed' => \App\Models\DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->where('status', 'delivered')->count(),
            ];

            return view('prestataire.delivery.index', compact('deliveries', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 15, 1, ['path' => request()->url()]
            );
            return view('prestataire.delivery.index', [
                'deliveries' => $emptyPaginator,
                'stats' => ['to_ship' => 0, 'shipped' => 0, 'completed' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Show form to create a new delivery (select booking)
     */
    public function create()
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;

        // Get bookings that are confirmed but don't have a delivery order yet
        // And are for services that require delivery (assuming all services here do, or we filter)
        $bookings = Booking::whereHas('service', function($q) use ($prestataire) {
            $q->where('prestataire_id', $prestataire->id);
        })
        ->where('status', 'confirmed')
        ->whereDoesntHave('deliveryOrder')
        ->with(['client', 'service'])
        ->latest()
        ->get();

        return view('prestataire.delivery.create', compact('bookings'));
    }

    // ============================================================================
    // ADMIN METHODS
    // ============================================================================

    /**
     * Admin all orders
     */
    public function adminAllOrders()
    {
        // Vérifier si la table delivery_orders existe
        if (!TableExistenceCache::has('delivery_orders')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.delivery.index', [
                'orders' => $emptyPaginator,
                'stats' => ['total_orders' => 0, 'pending' => 0, 'in_transit' => 0, 'delivered' => 0],
                'tableNotExists' => true,
            ]);
        }

        try {
            $orders = \App\Models\DeliveryOrder::with(['booking.service.prestataire', 'booking.client', 'provider'])
                ->latest()
                ->paginate(30);

            $stats = [
                'total_orders' => \App\Models\DeliveryOrder::count(),
                'pending' => \App\Models\DeliveryOrder::where('status', 'pending')->count(),
                'in_transit' => \App\Models\DeliveryOrder::where('status', 'in_transit')->count(),
                'delivered' => \App\Models\DeliveryOrder::where('status', 'delivered')->count(),
            ];

            return view('admin.delivery.index', compact('orders', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 30, 1, ['path' => request()->url()]
            );
            return view('admin.delivery.index', [
                'orders' => $emptyPaginator,
                'stats' => ['total_orders' => 0, 'pending' => 0, 'in_transit' => 0, 'delivered' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Admin providers list
     */
    public function adminProviders()
    {
        // Vérifier si la table delivery_providers existe
        if (!TableExistenceCache::has('delivery_providers')) {
            return view('admin.delivery.providers', [
                'providers' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $providers = \App\Models\DeliveryProvider::all();
            return view('admin.delivery.providers', compact('providers'));
        } catch (\Exception $e) {
            return view('admin.delivery.providers', [
                'providers' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Store new provider
     */
    public function adminStoreProvider(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|url',
            'is_active' => 'boolean',
            'price_per_km' => 'numeric|min:0',
        ]);

        \App\Models\DeliveryProvider::create($validated);
        return back()->with('success', 'Fournisseur ajouté avec succès');
    }

    /**
     * Update provider
     */
    public function adminUpdateProvider(Request $request, \App\Models\DeliveryProvider $provider)
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|url',
            'is_active' => 'boolean',
            'price_per_km' => 'numeric|min:0',
        ]);

        $provider->update($validated);
        return back()->with('success', 'Fournisseur mis à jour');
    }

    /**
     * Delete provider
     */
    public function adminDestroyProvider(\App\Models\DeliveryProvider $provider)
    {
        $provider->delete();
        return back()->with('success', 'Fournisseur supprimé');
    }
}
