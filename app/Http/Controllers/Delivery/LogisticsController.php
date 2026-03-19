<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\DeliveryDriver;
use App\Models\DeliveryZone;
use App\Models\DeliveryTracking;
use App\Models\DeliveryProvider;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\TableExistenceCache;
use Carbon\Carbon;

class LogisticsController extends Controller
{
    // ============================================================================
    // DASHBOARD & OVERVIEW
    // ============================================================================

    /**
     * Main logistics dashboard for prestataire
     */
    public function dashboard()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Vérifier si la table delivery_orders existe
        if (!TableExistenceCache::has('delivery_orders')) {
            return view('prestataire.delivery.dashboard', [
                'stats' => [
                    'total' => 0, 'pending' => 0, 'preparing' => 0, 'in_transit' => 0,
                    'delivered_today' => 0, 'delivered_this_month' => 0, 'delivered_last_month' => 0,
                    'failed' => 0, 'success_rate' => 0, 'avg_delivery_time' => 0, 'total_revenue' => 0,
                ],
                'todayDeliveries' => collect(),
                'pendingDeliveries' => collect(),
                'activeDeliveries' => collect(),
                'performanceData' => [],
                'tableNotExists' => true,
            ]);
        }

        try {
            // Get delivery statistics
            $stats = $this->getDeliveryStats($prestataire->id);

            // Get today's deliveries
            $todayDeliveries = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->whereDate('scheduled_delivery_at', today())
            ->with(['driver', 'booking.client'])
            ->orderBy('scheduled_delivery_at')
            ->get();

            // Get pending deliveries requiring action
            $pendingDeliveries = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->where('status', DeliveryOrder::STATUS_PENDING)
            ->with(['booking.client', 'booking.service'])
            ->latest()
            ->take(10)
            ->get();

            // Get active deliveries in transit
            $activeDeliveries = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->whereIn('status', [
                DeliveryOrder::STATUS_PICKED_UP,
                DeliveryOrder::STATUS_IN_TRANSIT,
                DeliveryOrder::STATUS_OUT_FOR_DELIVERY
            ])
            ->with(['driver', 'booking.client'])
            ->get();

            // Get delivery performance data for chart
            $performanceData = $this->getPerformanceData($prestataire->id);

            return view('prestataire.delivery.dashboard', compact(
                'stats',
                'todayDeliveries',
                'pendingDeliveries',
                'activeDeliveries',
                'performanceData'
            ));
        } catch (\Exception $e) {
            return view('prestataire.delivery.dashboard', [
                'stats' => [
                    'total' => 0, 'pending' => 0, 'preparing' => 0, 'in_transit' => 0,
                    'delivered_today' => 0, 'delivered_this_month' => 0, 'delivered_last_month' => 0,
                    'failed' => 0, 'success_rate' => 0, 'avg_delivery_time' => 0, 'total_revenue' => 0,
                ],
                'todayDeliveries' => collect(),
                'pendingDeliveries' => collect(),
                'activeDeliveries' => collect(),
                'performanceData' => [],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Get delivery statistics
     */
    private function getDeliveryStats(int $prestataireId): array
    {
        // Vérifier si la table existe
        if (!TableExistenceCache::has('delivery_orders')) {
            return [
                'total' => 0, 'pending' => 0, 'preparing' => 0, 'in_transit' => 0,
                'delivered_today' => 0, 'delivered_this_month' => 0, 'delivered_last_month' => 0,
                'failed' => 0, 'success_rate' => 0, 'avg_delivery_time' => 0, 'total_revenue' => 0,
            ];
        }

        try {
            $baseQuery = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataireId) {
                $q->where('prestataire_id', $prestataireId);
            });

            $thisMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();

            return [
                'total' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->where('status', DeliveryOrder::STATUS_PENDING)->count(),
                'preparing' => (clone $baseQuery)->where('status', DeliveryOrder::STATUS_PREPARING)->count(),
                'in_transit' => (clone $baseQuery)->whereIn('status', [
                    DeliveryOrder::STATUS_PICKED_UP,
                    DeliveryOrder::STATUS_IN_TRANSIT,
                    DeliveryOrder::STATUS_OUT_FOR_DELIVERY
                ])->count(),
                'delivered_today' => (clone $baseQuery)->where('status', DeliveryOrder::STATUS_DELIVERED)
                    ->whereDate('delivered_at', today())->count(),
                'delivered_this_month' => (clone $baseQuery)->where('status', DeliveryOrder::STATUS_DELIVERED)
                    ->where('delivered_at', '>=', $thisMonth)->count(),
                'delivered_last_month' => (clone $baseQuery)->where('status', DeliveryOrder::STATUS_DELIVERED)
                    ->whereBetween('delivered_at', [$lastMonth, $thisMonth])->count(),
                'failed' => (clone $baseQuery)->where('status', DeliveryOrder::STATUS_FAILED)->count(),
                'success_rate' => $this->calculateSuccessRate($prestataireId),
                'avg_delivery_time' => $this->calculateAvgDeliveryTime($prestataireId),
                'total_revenue' => (clone $baseQuery)->where('status', DeliveryOrder::STATUS_DELIVERED)
                    ->where('delivered_at', '>=', $thisMonth)->sum('shipping_cost'),
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0, 'pending' => 0, 'preparing' => 0, 'in_transit' => 0,
                'delivered_today' => 0, 'delivered_this_month' => 0, 'delivered_last_month' => 0,
                'failed' => 0, 'success_rate' => 0, 'avg_delivery_time' => 0, 'total_revenue' => 0,
            ];
        }
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(int $prestataireId): float
    {
        if (!TableExistenceCache::has('delivery_orders')) {
            return 0;
        }

        try {
            $total = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataireId) {
                $q->where('prestataire_id', $prestataireId);
            })->whereIn('status', [
                DeliveryOrder::STATUS_DELIVERED,
                DeliveryOrder::STATUS_FAILED,
                DeliveryOrder::STATUS_RETURNED
            ])->count();

            if ($total === 0) return 100;

            $successful = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataireId) {
                $q->where('prestataire_id', $prestataireId);
            })->where('status', DeliveryOrder::STATUS_DELIVERED)->count();

            return round(($successful / $total) * 100, 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate average delivery time
     */
    private function calculateAvgDeliveryTime(int $prestataireId): int
    {
        if (!TableExistenceCache::has('delivery_orders')) {
            return 0;
        }

        try {
            $avg = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataireId) {
                $q->where('prestataire_id', $prestataireId);
            })
            ->where('status', DeliveryOrder::STATUS_DELIVERED)
            ->whereNotNull('picked_up_at')
            ->whereNotNull('delivered_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, picked_up_at, delivered_at)) as avg_time')
            ->value('avg_time');

            return (int) ($avg ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get performance data for charts
     */
    private function getPerformanceData(int $prestataireId): array
    {
        if (!TableExistenceCache::has('delivery_orders')) {
            return [];
        }

        try {
            $days = collect();
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $delivered = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataireId) {
                    $q->where('prestataire_id', $prestataireId);
                })
                ->where('status', DeliveryOrder::STATUS_DELIVERED)
                ->whereDate('delivered_at', $date)
                ->count();

                $days->push([
                    'date' => $date->format('d/m'),
                    'day' => $date->isoFormat('ddd'),
                    'count' => $delivered
                ]);
            }

            return $days->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // ============================================================================
    // DELIVERY MANAGEMENT
    // ============================================================================

    /**
     * List all deliveries with filters
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Vérifier si la table existe
        if (!TableExistenceCache::has('delivery_orders')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('prestataire.delivery.index', [
                'deliveries' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }

        try {
            $query = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })->with(['booking.client', 'booking.service', 'driver', 'provider']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('tracking_number', 'like', "%{$search}%")
                      ->orWhere('reference_number', 'like', "%{$search}%")
                      ->orWhere('delivery_contact_name', 'like', "%{$search}%");
                });
            }

            $deliveries = $query->latest()->paginate(20);

            // Get counts for quick filters
            $statusCounts = [
                'all' => DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->count(),
                'pending' => DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->where('status', DeliveryOrder::STATUS_PENDING)->count(),
                'in_transit' => DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->whereIn('status', [DeliveryOrder::STATUS_IN_TRANSIT, DeliveryOrder::STATUS_OUT_FOR_DELIVERY])->count(),
                'delivered' => DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })->where('status', DeliveryOrder::STATUS_DELIVERED)->count(),
            ];

            return view('prestataire.delivery.index', compact('deliveries', 'statusCounts'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('prestataire.delivery.index', [
                'deliveries' => $emptyPaginator,
                'statusCounts' => ['all' => 0, 'pending' => 0, 'in_transit' => 0, 'delivered' => 0],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Show delivery details
     */
    public function show(DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);

        $delivery->load([
            'booking.client.user',
            'booking.service',
            'driver',
            'provider',
            'zone',
            'trackingEvents' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ]);

        return view('prestataire.delivery.show', compact('delivery'));
    }

    /**
     * Create new delivery form
     */
    public function create(Request $request)
    {
        // Check if delivery tables exist
        if (!TableExistenceCache::has('delivery_orders') || !TableExistenceCache::has('delivery_providers') || !TableExistenceCache::has('delivery_zones')) {
            return view('prestataire.delivery.create', [
                'bookings' => collect(),
                'providers' => collect(),
                'zones' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $user = Auth::user();
            $prestataire = $user->prestataire;

            // Get bookings eligible for delivery
            $bookings = Booking::whereHas('service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->whereIn('status', ['confirmed', 'completed', 'ready_for_delivery'])
            ->whereDoesntHave('deliveryOrder')
            ->with(['client.user', 'service'])
            ->latest()
            ->get();

            $providers = DeliveryProvider::where('is_active', true)->get();
            $zones = DeliveryZone::active()->ordered()->get();

            return view('prestataire.delivery.create', compact('bookings', 'providers', 'zones'));
        } catch (\Exception $e) {
            return view('prestataire.delivery.create', [
                'bookings' => collect(),
                'providers' => collect(),
                'zones' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Store new delivery
     */
    public function store(Request $request)
    {
        // Check if delivery tables exist
        if (!TableExistenceCache::has('delivery_orders')) {
            return back()->with('error', 'Le système de livraison n\'est pas encore configuré.');
        }

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'provider_id' => 'nullable|exists:delivery_providers,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'shipping_type' => 'required|in:standard,express,same_day,overnight',
            
            // Pickup info
            'pickup_address' => 'required|string|max:500',
            'pickup_city' => 'required|string|max:100',
            'pickup_postal_code' => 'required|string|max:20',
            'pickup_contact_name' => 'required|string|max:100',
            'pickup_contact_phone' => 'required|string|max:20',
            'pickup_instructions' => 'nullable|string|max:500',
            'scheduled_pickup_at' => 'nullable|date|after:now',
            
            // Delivery info
            'delivery_address' => 'required|string|max:500',
            'delivery_city' => 'required|string|max:100',
            'delivery_postal_code' => 'required|string|max:20',
            'delivery_contact_name' => 'required|string|max:100',
            'delivery_contact_phone' => 'required|string|max:20',
            'delivery_instructions' => 'nullable|string|max:500',
            'scheduled_delivery_at' => 'nullable|date|after:scheduled_pickup_at',
            
            // Package info
            'weight' => 'nullable|numeric|min:0.1|max:1000',
            'package_count' => 'nullable|integer|min:1|max:100',
            'fragile' => 'nullable|boolean',
            'requires_signature' => 'nullable|boolean',
            
            // Notes
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Verify booking belongs to prestataire
        $booking = Booking::whereHas('service', function($q) use ($prestataire) {
            $q->where('prestataire_id', $prestataire->id);
        })->findOrFail($validated['booking_id']);

        // Calculate shipping cost
        $shippingCost = $this->calculateShippingCost($validated);

        // Find zone
        $zone = DeliveryZone::findByPostalCode($validated['delivery_postal_code']);

        // Calculate estimated delivery
        $estimatedDelivery = $this->calculateEstimatedDelivery(
            $validated['shipping_type'],
            $validated['scheduled_pickup_at'] ?? now()
        );

        DB::beginTransaction();
        try {
            $delivery = DeliveryOrder::create([
                'booking_id' => $booking->id,
                'delivery_provider_id' => $validated['provider_id'] ?? null,
                'zone_id' => $zone?->id,
                'priority' => $validated['priority'],
                'shipping_type' => $validated['shipping_type'],
                
                'pickup_address' => $validated['pickup_address'],
                'pickup_city' => $validated['pickup_city'],
                'pickup_postal_code' => $validated['pickup_postal_code'],
                'pickup_contact_name' => $validated['pickup_contact_name'],
                'pickup_contact_phone' => $validated['pickup_contact_phone'],
                'pickup_instructions' => $validated['pickup_instructions'] ?? null,
                'scheduled_pickup_at' => $validated['scheduled_pickup_at'] ?? null,
                
                'delivery_address' => $validated['delivery_address'],
                'delivery_city' => $validated['delivery_city'],
                'delivery_postal_code' => $validated['delivery_postal_code'],
                'delivery_contact_name' => $validated['delivery_contact_name'],
                'delivery_contact_phone' => $validated['delivery_contact_phone'],
                'delivery_instructions' => $validated['delivery_instructions'] ?? null,
                'scheduled_delivery_at' => $validated['scheduled_delivery_at'] ?? null,
                
                'weight' => $validated['weight'] ?? 1.0,
                'package_count' => $validated['package_count'] ?? 1,
                'fragile' => $validated['fragile'] ?? false,
                'requires_signature' => $validated['requires_signature'] ?? false,
                
                'shipping_cost' => $shippingCost,
                'total_cost' => $shippingCost,
                'estimated_delivery' => $estimatedDelivery,
                
                'notes' => $validated['notes'] ?? null,
                'status' => DeliveryOrder::STATUS_PENDING,
            ]);

            // Update booking status
            $booking->update(['status' => 'ready_for_delivery']);

            DB::commit();

            return redirect()
                ->route('prestataire.logistics.show', $delivery)
                ->with('success', "Livraison créée ! N° de suivi: {$delivery->tracking_number}");

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['error' => 'Erreur lors de la création de la livraison.'])
                ->withInput();
        }
    }

    /**
     * Calculate shipping cost
     */
    private function calculateShippingCost(array $data): float
    {
        $baseCost = match($data['shipping_type']) {
            'same_day' => 15.00,
            'express' => 10.00,
            'overnight' => 12.00,
            default => 5.00
        };

        $weightCost = ($data['weight'] ?? 1) * 0.50;
        $packageCost = (($data['package_count'] ?? 1) - 1) * 2.00;
        $fragileSurcharge = ($data['fragile'] ?? false) ? 3.00 : 0;
        $prioritySurcharge = match($data['priority']) {
            'urgent' => 10.00,
            'high' => 5.00,
            default => 0
        };

        return $baseCost + $weightCost + $packageCost + $fragileSurcharge + $prioritySurcharge;
    }

    /**
     * Calculate estimated delivery date
     */
    private function calculateEstimatedDelivery(string $shippingType, $startDate): Carbon
    {
        $start = Carbon::parse($startDate);

        return match($shippingType) {
            'same_day' => $start->copy()->endOfDay(),
            'express' => $start->copy()->addDay(),
            'overnight' => $start->copy()->addDay()->setHour(10),
            default => $start->copy()->addDays(3)
        };
    }

    // ============================================================================
    // STATUS UPDATES
    // ============================================================================

    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);

        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:200',
        ]);

        $location = $validated['location'] ? ['address' => $validated['location']] : null;
        $delivery->updateStatus($validated['status'], $validated['notes'] ?? null, $location);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $delivery->status,
                'status_label' => $delivery->status_label
            ]);
        }

        return back()->with('success', 'Statut mis à jour');
    }

    /**
     * Mark delivery as ready for pickup
     */
    public function markReadyForPickup(DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);
        $delivery->markReadyForPickup();
        return back()->with('success', 'Colis marqué prêt pour enlèvement');
    }

    /**
     * Mark delivery as picked up
     */
    public function markPickedUp(Request $request, DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);
        $location = $request->location ? ['address' => $request->location] : null;
        $delivery->markAsPickedUp($location);
        return back()->with('success', 'Colis marqué comme récupéré');
    }

    /**
     * Mark delivery as delivered
     */
    public function markDelivered(Request $request, DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);

        $validated = $request->validate([
            'recipient_name' => 'nullable|string|max:100',
            'signature' => 'nullable|string', // Base64 signature
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('delivery-photos', 'public');
        }

        $delivery->markAsDelivered(
            $validated['signature'] ?? null,
            $photoPath,
            $validated['recipient_name'] ?? null
        );

        return back()->with('success', '🎉 Livraison confirmée !');
    }

    /**
     * Mark delivery as failed
     */
    public function markFailed(Request $request, DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'reschedule' => 'nullable|boolean',
            'next_attempt' => 'nullable|date|after:now',
        ]);

        $delivery->markAsFailed($validated['reason']);

        if ($validated['reschedule'] ?? false) {
            $nextAttempt = $validated['next_attempt'] ?? now()->addDay();
            $delivery->scheduleRetry(Carbon::parse($nextAttempt));
        }

        return back()->with('success', 'Échec de livraison enregistré');
    }

    /**
     * Cancel delivery
     */
    public function cancel(Request $request, DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $delivery->cancel($validated['reason'] ?? 'Annulé par le prestataire');

        return back()->with('success', 'Livraison annulée');
    }

    // ============================================================================
    // DRIVER MANAGEMENT
    // ============================================================================

    /**
     * Assign driver to delivery
     */
    public function assignDriver(Request $request, DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);

        $validated = $request->validate([
            'driver_id' => 'required|exists:delivery_drivers,id',
        ]);

        $driver = DeliveryDriver::findOrFail($validated['driver_id']);
        $delivery->assignDriver($driver);

        return back()->with('success', "Livreur {$driver->full_name} assigné");
    }

    /**
     * Auto-assign best driver
     */
    public function autoAssignDriver(DeliveryOrder $delivery)
    {
        $this->authorizeDelivery($delivery);

        $driver = DeliveryDriver::findBestForDelivery(
            $delivery->pickup_lat ?? 48.8566,
            $delivery->pickup_lng ?? 2.3522,
            $delivery->zone_id
        );

        if (!$driver) {
            return back()->with('error', 'Aucun livreur disponible');
        }

        $delivery->assignDriver($driver);
        return back()->with('success', "Livreur {$driver->full_name} auto-assigné");
    }

    // ============================================================================
    // TRACKING
    // ============================================================================

    /**
     * Public tracking page
     */
    public function track(Request $request)
    {
        $trackingNumber = $request->tracking_number;
        $delivery = null;
        $events = collect();

        if ($trackingNumber && TableExistenceCache::has('delivery_orders')) {
            try {
                $delivery = DeliveryOrder::where('tracking_number', $trackingNumber)
                    ->with(['trackingEvents', 'driver', 'provider'])
                    ->first();

                if ($delivery) {
                    $events = $delivery->trackingEvents;
                }
            } catch (\Exception $e) {
                // Table doesn't exist
            }
        }

        return view('delivery.track', compact('delivery', 'events', 'trackingNumber'));
    }

    /**
     * Get tracking info as JSON
     */
    public function trackingInfo(DeliveryOrder $delivery)
    {
        return response()->json([
            'tracking_number' => $delivery->tracking_number,
            'status' => $delivery->status,
            'status_label' => $delivery->status_label,
            'progress' => $delivery->progress_percentage,
            'estimated_delivery' => $delivery->estimated_delivery?->format('d/m/Y H:i'),
            'driver' => $delivery->driver ? [
                'name' => $delivery->driver->full_name,
                'phone' => $delivery->driver->phone,
                'vehicle' => $delivery->driver->vehicle_icon,
            ] : null,
            'events' => $delivery->trackingEvents->map(function($event) {
                return [
                    'status' => $event->status,
                    'title' => $event->title,
                    'description' => $event->description,
                    'location' => $event->location,
                    'date' => $event->created_at->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    // ============================================================================
    // ZONES MANAGEMENT
    // ============================================================================

    /**
     * List delivery zones
     */
    public function zones()
    {
        if (!TableExistenceCache::has('delivery_zones')) {
            return view('prestataire.delivery.zones', [
                'zones' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $zones = DeliveryZone::withCount('drivers')
                ->ordered()
                ->get();

            return view('prestataire.delivery.zones', compact('zones'));
        } catch (\Exception $e) {
            return view('prestataire.delivery.zones', [
                'zones' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // DRIVERS MANAGEMENT
    // ============================================================================

    /**
     * List drivers
     */
    public function drivers()
    {
        if (!TableExistenceCache::has('delivery_drivers')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('prestataire.delivery.drivers', [
                'drivers' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }

        try {
            $user = Auth::user();
            
            $drivers = DeliveryDriver::with('zone')
                ->withCount(['deliveries as active_deliveries_count' => function($q) {
                    $q->whereIn('status', [
                        DeliveryOrder::STATUS_DRIVER_ASSIGNED,
                        DeliveryOrder::STATUS_PICKED_UP,
                        DeliveryOrder::STATUS_IN_TRANSIT,
                        DeliveryOrder::STATUS_OUT_FOR_DELIVERY
                    ]);
                }])
                ->orderBy('is_active', 'desc')
                ->orderBy('rating', 'desc')
                ->paginate(20);

            return view('prestataire.delivery.drivers', compact('drivers'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('prestataire.delivery.drivers', [
                'drivers' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // REPORTS
    // ============================================================================

    /**
     * Delivery reports
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $period = $request->period ?? 'month';
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        if (!TableExistenceCache::has('delivery_orders')) {
            return view('prestataire.delivery.reports', [
                'stats' => ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'delivered' => 0, 'success_rate' => 0, 'avg_delivery_time' => 0],
                'byStatus' => collect(),
                'byShippingType' => collect(),
                'dailyRevenue' => collect(),
                'period' => $period,
                'tableNotExists' => true,
            ]);
        }

        try {
            $stats = $this->getDeliveryStats($prestataire->id);

            // Deliveries by status
            $byStatus = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

            // Deliveries by shipping type
            $byShippingType = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->where('created_at', '>=', $startDate)
            ->selectRaw('shipping_type, COUNT(*) as count')
            ->groupBy('shipping_type')
            ->pluck('count', 'shipping_type');

            // Revenue by day
            $dailyRevenue = DeliveryOrder::whereHas('booking.service', function($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->where('status', DeliveryOrder::STATUS_DELIVERED)
            ->where('delivered_at', '>=', $startDate)
            ->selectRaw('DATE(delivered_at) as date, SUM(shipping_cost) as revenue, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            return view('prestataire.delivery.reports', compact(
                'stats',
                'byStatus',
                'byShippingType',
                'dailyRevenue',
                'period'
            ));
        } catch (\Exception $e) {
            return view('prestataire.delivery.reports', [
                'stats' => ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'delivered' => 0, 'success_rate' => 0, 'avg_delivery_time' => 0],
                'byStatus' => collect(),
                'byShippingType' => collect(),
                'dailyRevenue' => collect(),
                'period' => $period,
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // HELPERS
    // ============================================================================

    /**
     * Authorize access to delivery
     */
    private function authorizeDelivery(DeliveryOrder $delivery): void
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $belongs = $delivery->booking && 
                   $delivery->booking->service && 
                   $delivery->booking->service->prestataire_id === $prestataire->id;

        if (!$belongs) {
            abort(403, 'Accès non autorisé à cette livraison');
        }
    }
}
