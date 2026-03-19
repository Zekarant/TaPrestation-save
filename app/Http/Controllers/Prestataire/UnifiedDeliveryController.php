<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\FoodOrder;
use App\Models\UrgentSaleReservation;
use App\Models\Booking;
use App\Models\EquipmentRental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\TableExistenceCache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class UnifiedDeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard centralisé des livraisons - TOUTES sources confondues
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $prestataire = $user->prestataire;

            if (!$prestataire) {
                return redirect()->route('prestataire.register')
                    ->with('error', 'Vous devez d\'abord créer votre profil prestataire.');
            }

            $typeFilter = $request->get('type', 'all');
            $statusFilter = $request->get('status', 'pending');

            // ============================================================================
            // 1. COMMANDES FOOD - Livraison interne (prestataire livre lui-même)
            // ============================================================================
            $foodOrdersInternal = collect([]);
            if (in_array($typeFilter, ['all', 'food_internal']) && TableExistenceCache::has('food_orders')) {
                try {
                    $foodOrdersInternal = FoodOrder::where('prestataire_id', $prestataire->id)
                        ->whereNull('driver_id')
                        ->where('delivery_type', 'delivery')
                        ->when($statusFilter === 'pending', fn($q) => $q->whereIn('delivery_status', ['pending', 'accepted', 'preparing', 'ready']))
                        ->when($statusFilter === 'in_progress', fn($q) => $q->whereIn('delivery_status', ['picked_up', 'in_transit']))
                        ->when($statusFilter === 'completed', fn($q) => $q->where('delivery_status', 'delivered'))
                        ->with(['client', 'items.foodItem'])
                        ->latest()
                        ->get()
                        ->map(function($order) {
                            return $this->normalizeDeliveryItem($order, 'food_internal');
                        });
                } catch (\Exception $e) {
                    Log::warning('UnifiedDelivery: Food internal error - ' . $e->getMessage());
                    $foodOrdersInternal = collect([]);
                }
            }

            // ============================================================================
            // 2. COMMANDES FOOD - Livraison externe (via drivers partenaires)
            // ============================================================================
            $foodOrdersExternal = collect([]);
            if (in_array($typeFilter, ['all', 'food_external']) && TableExistenceCache::has('food_orders')) {
                try {
                    $foodOrdersExternal = FoodOrder::where('prestataire_id', $prestataire->id)
                        ->whereNotNull('driver_id')
                        ->where('delivery_type', 'delivery')
                        ->when($statusFilter === 'pending', fn($q) => $q->whereIn('delivery_status', ['pending', 'accepted', 'preparing', 'ready']))
                        ->when($statusFilter === 'in_progress', fn($q) => $q->whereIn('delivery_status', ['assigned', 'picked_up', 'in_transit']))
                        ->when($statusFilter === 'completed', fn($q) => $q->where('delivery_status', 'delivered'))
                        ->with(['client', 'driver', 'items.foodItem'])
                        ->latest()
                        ->get()
                        ->map(function($order) {
                            return $this->normalizeDeliveryItem($order, 'food_external');
                        });
                } catch (\Exception $e) {
                    Log::warning('UnifiedDelivery: Food external error - ' . $e->getMessage());
                    $foodOrdersExternal = collect([]);
                }
            }

            // ============================================================================
            // 3. VENTES URGENTES - Réservations confirmées à livrer
            // ============================================================================
            $urgentSales = collect([]);
            if (in_array($typeFilter, ['all', 'urgent_sale']) && TableExistenceCache::has('urgent_sale_reservations')) {
                try {
                    $urgentSales = UrgentSaleReservation::whereHas('urgentSale', function($q) use ($prestataire) {
                            $q->where('prestataire_id', $prestataire->id);
                        })
                        ->when($statusFilter === 'pending', fn($q) => $q->where('status', 'confirmed'))
                        ->when($statusFilter === 'completed', fn($q) => $q->where('status', 'completed'))
                        ->with(['urgentSale', 'client'])
                        ->latest()
                        ->get()
                        ->map(function($reservation) {
                            return $this->normalizeDeliveryItem($reservation, 'urgent_sale');
                        });
                } catch (\Exception $e) {
                    Log::warning('UnifiedDelivery: Urgent sale error - ' . $e->getMessage());
                    $urgentSales = collect([]);
                }
            }

            // ============================================================================
            // 4. SERVICES (Bookings) - Qui nécessitent une livraison/déplacement
            // ============================================================================
            $bookings = collect([]);
            if (in_array($typeFilter, ['all', 'service']) && TableExistenceCache::has('bookings')) {
                try {
                    $bookings = Booking::where('prestataire_id', $prestataire->id)
                        ->when($statusFilter === 'pending', fn($q) => $q->where('status', 'confirmed'))
                        ->when($statusFilter === 'completed', fn($q) => $q->where('status', 'completed'))
                        ->with(['client', 'service'])
                        ->latest()
                        ->get()
                        ->map(function($booking) {
                            return $this->normalizeDeliveryItem($booking, 'service');
                        });
                } catch (\Exception $e) {
                    Log::warning('UnifiedDelivery: Booking error - ' . $e->getMessage());
                    $bookings = collect([]);
                }
            }

            // ============================================================================
            // 5. ÉQUIPEMENTS - Locations à livrer
            // ============================================================================
            $equipmentRentals = collect([]);
            if (in_array($typeFilter, ['all', 'equipment']) && TableExistenceCache::has('equipment_rentals')) {
                try {
                    $equipmentRentals = EquipmentRental::where('prestataire_id', $prestataire->id)
                        ->when($statusFilter === 'pending', fn($q) => $q->whereIn('status', ['confirmed', 'pending_pickup']))
                        ->when($statusFilter === 'in_progress', fn($q) => $q->where('status', 'active'))
                        ->when($statusFilter === 'completed', fn($q) => $q->where('status', 'completed'))
                        ->with(['equipment', 'client'])
                        ->latest()
                        ->get()
                        ->map(function($rental) {
                            return $this->normalizeDeliveryItem($rental, 'equipment');
                        });
                } catch (\Exception $e) {
                    Log::warning('UnifiedDelivery: Equipment error - ' . $e->getMessage());
                    $equipmentRentals = collect([]);
                }
            }

            // Fusionner et trier par date
            $allDeliveries = $foodOrdersInternal
                ->concat($foodOrdersExternal)
                ->concat($urgentSales)
                ->concat($bookings)
                ->concat($equipmentRentals)
                ->sortByDesc('created_at')
                ->values();

            // Stats
            $stats = $this->calculateStats($prestataire);

            return view('prestataire.delivery.unified-index', compact(
                'allDeliveries',
                'stats',
                'typeFilter',
                'statusFilter'
            ));

        } catch (\Exception $e) {
            Log::error('UnifiedDelivery: Global error - ' . $e->getMessage());
            
            // Retourner une vue vide en cas d'erreur
            return view('prestataire.delivery.unified-index', [
                'allDeliveries' => collect([]),
                'stats' => [
                    'total_pending' => 0,
                    'total_in_progress' => 0,
                    'total_completed' => 0,
                    'food_pending' => 0,
                    'food_in_progress' => 0,
                    'food_completed' => 0,
                    'urgent_pending' => 0,
                    'urgent_completed' => 0,
                    'bookings_pending' => 0,
                    'bookings_completed' => 0,
                    'equipment_pending' => 0,
                    'equipment_completed' => 0,
                ],
                'typeFilter' => 'all',
                'statusFilter' => 'pending',
                'error' => 'Une erreur est survenue lors du chargement des données.'
            ]);
        }
    }

    /**
     * Normaliser les différents types en un format unifié
     */
    private function normalizeDeliveryItem($item, string $type): array
    {
        try {
            switch ($type) {
                case 'food_internal':
                case 'food_external':
                    $client = $item->client;
                    $driver = $item->driver ?? null;
                    $items = $item->items ?? collect([]);
                    return [
                        'id' => $item->id,
                        'type' => $type,
                        'type_label' => $type === 'food_internal' ? '🍔 Food (Vous)' : '🍔 Food (Livreur)',
                        'type_color' => 'orange',
                        'reference' => $item->order_number ?? 'N/A',
                        'client_name' => ($client ? $client->name : null) ?? $item->delivery_contact_name ?? 'Client',
                        'client_phone' => ($client ? $client->phone : null) ?? $item->delivery_phone ?? null,
                        'address' => $item->delivery_address ?? null,
                        'city' => null,
                        'amount' => $item->total ?? 0,
                        'status' => $item->delivery_status ?? 'pending',
                        'status_label' => $this->getFoodStatusLabel($item->delivery_status ?? 'pending'),
                        'status_color' => $this->getFoodStatusColor($item->delivery_status ?? 'pending'),
                        'driver_name' => $type === 'food_external' ? ($driver ? ($driver->full_name ?? 'Livreur') : 'En attente') : null,
                        'items_count' => $items->sum('quantity') ?? 0,
                        'items_preview' => $items->take(2)->map(fn($i) => ($i->foodItem ? $i->foodItem->name : null) ?? $i->name ?? 'Article')->implode(', '),
                        'created_at' => $item->created_at,
                        'show_route' => route('prestataire.food-orders.show', $item->id),
                        'model' => $item,
                    ];

                case 'urgent_sale':
                    $client = $item->client;
                    $urgentSale = $item->urgentSale;
                    return [
                        'id' => $item->id,
                        'type' => $type,
                        'type_label' => '🏷️ Vente Urgente',
                        'type_color' => 'red',
                        'reference' => 'VU-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                        'client_name' => ($client ? $client->name : null) ?? 'Client',
                        'client_phone' => ($client ? $client->phone : null) ?? null,
                        'address' => null,
                        'city' => $urgentSale ? ($urgentSale->location ?? null) : null,
                        'amount' => ($urgentSale ? $urgentSale->price : 0) * ($item->quantity ?? 1),
                        'status' => $item->status ?? 'pending',
                        'status_label' => $this->getUrgentSaleStatusLabel($item->status ?? 'pending'),
                        'status_color' => $this->getUrgentSaleStatusColor($item->status ?? 'pending'),
                        'driver_name' => null,
                        'items_count' => $item->quantity ?? 1,
                        'items_preview' => $urgentSale ? ($urgentSale->title ?? 'Article') : 'Article',
                        'created_at' => $item->created_at,
                        'show_route' => route('prestataire.reservations.index'),
                        'model' => $item,
                    ];

                case 'service':
                    $client = $item->client;
                    $service = $item->service;
                    return [
                        'id' => $item->id,
                        'type' => $type,
                        'type_label' => '🛠️ Service',
                        'type_color' => 'blue',
                        'reference' => $item->booking_number ?? 'N/A',
                        'client_name' => ($client ? $client->name : null) ?? 'Client',
                        'client_phone' => ($client ? $client->phone : null) ?? null,
                        'address' => $item->client_notes ?? null,
                        'city' => null,
                        'amount' => $item->total_price ?? 0,
                        'status' => $item->status ?? 'pending',
                        'status_label' => $this->getBookingStatusLabel($item->status ?? 'pending'),
                        'status_color' => $this->getBookingStatusColor($item->status ?? 'pending'),
                        'driver_name' => null,
                        'items_count' => 1,
                        'items_preview' => $service ? ($service->name ?? 'Service') : 'Service',
                        'created_at' => $item->created_at,
                        'show_route' => route('prestataire.bookings.show', $item->id),
                        'model' => $item,
                    ];

                case 'equipment':
                    $client = $item->client;
                    $equipment = $item->equipment;
                    return [
                        'id' => $item->id,
                        'type' => $type,
                        'type_label' => '🔧 Équipement',
                        'type_color' => 'purple',
                        'reference' => $item->rental_number ?? 'N/A',
                        'client_name' => ($client ? $client->name : null) ?? 'Client',
                        'client_phone' => ($client ? $client->phone : null) ?? null,
                        'address' => $item->pickup_address ?? null,
                        'city' => null,
                        'amount' => $item->total_amount ?? 0,
                        'status' => $item->status ?? 'pending',
                        'status_label' => $this->getEquipmentStatusLabel($item->status ?? 'pending'),
                        'status_color' => $this->getEquipmentStatusColor($item->status ?? 'pending'),
                        'driver_name' => null,
                        'items_count' => 1,
                        'items_preview' => $equipment ? ($equipment->name ?? 'Équipement') : 'Équipement',
                        'created_at' => $item->created_at,
                        'show_route' => route('prestataire.equipment-rentals.show', $item->id),
                        'model' => $item,
                    ];

                default:
                    return [];
            }
        } catch (\Exception $e) {
            Log::warning('UnifiedDelivery: normalizeDeliveryItem error - ' . $e->getMessage());
            return [
                'id' => $item->id ?? 0,
                'type' => $type,
                'type_label' => 'Erreur',
                'type_color' => 'gray',
                'reference' => 'N/A',
                'client_name' => 'Erreur',
                'client_phone' => null,
                'address' => null,
                'city' => null,
                'amount' => 0,
                'status' => 'error',
                'status_label' => 'Erreur',
                'status_color' => 'gray',
                'driver_name' => null,
                'items_count' => 0,
                'items_preview' => 'Erreur de chargement',
                'created_at' => now(),
                'show_route' => '#',
                'model' => null,
            ];
        }
    }

    /**
     * Calculer les statistiques globales
     */
    private function calculateStats($prestataire): array
    {
        $stats = [
            'total_pending' => 0,
            'total_in_progress' => 0,
            'total_completed' => 0,
            'food_pending' => 0,
            'food_in_progress' => 0,
            'food_completed' => 0,
            'urgent_pending' => 0,
            'urgent_completed' => 0,
            'bookings_pending' => 0,
            'bookings_completed' => 0,
            'equipment_pending' => 0,
            'equipment_completed' => 0,
        ];

        try {
            // Food orders
            if (TableExistenceCache::has('food_orders')) {
                $stats['food_pending'] = FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('delivery_type', 'delivery')
                    ->whereIn('delivery_status', ['pending', 'accepted', 'preparing', 'ready', 'assigned'])
                    ->count();
                
                $stats['food_in_progress'] = FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('delivery_type', 'delivery')
                    ->whereIn('delivery_status', ['picked_up', 'in_transit'])
                    ->count();
                
                $stats['food_completed'] = FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('delivery_type', 'delivery')
                    ->where('delivery_status', 'delivered')
                    ->count();
            }

            // Urgent sales
            if (TableExistenceCache::has('urgent_sale_reservations')) {
                $stats['urgent_pending'] = UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))
                    ->where('status', 'confirmed')
                    ->count();
                
                $stats['urgent_completed'] = UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))
                    ->where('status', 'completed')
                    ->count();
            }

            // Bookings
            if (TableExistenceCache::has('bookings')) {
                $stats['bookings_pending'] = Booking::where('prestataire_id', $prestataire->id)
                    ->where('status', 'confirmed')
                    ->count();
                
                $stats['bookings_completed'] = Booking::where('prestataire_id', $prestataire->id)
                    ->where('status', 'completed')
                    ->count();
            }

            // Equipment
            if (TableExistenceCache::has('equipment_rentals')) {
                $stats['equipment_pending'] = EquipmentRental::where('prestataire_id', $prestataire->id)
                    ->whereIn('status', ['confirmed', 'pending_pickup'])
                    ->count();
                
                $stats['equipment_completed'] = EquipmentRental::where('prestataire_id', $prestataire->id)
                    ->where('status', 'completed')
                    ->count();
            }

            // Totaux
            $stats['total_pending'] = $stats['food_pending'] + $stats['urgent_pending'] + $stats['bookings_pending'] + $stats['equipment_pending'];
            $stats['total_in_progress'] = $stats['food_in_progress'];
            $stats['total_completed'] = $stats['food_completed'] + $stats['urgent_completed'] + $stats['bookings_completed'] + $stats['equipment_completed'];

        } catch (\Exception $e) {
            Log::warning('UnifiedDelivery: calculateStats error - ' . $e->getMessage());
        }

        return $stats;
    }

    // Status helpers
    private function getFoodStatusLabel($status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'accepted' => 'Acceptée',
            'preparing' => 'Préparation',
            'ready' => 'Prête',
            'assigned' => 'Livreur assigné',
            'picked_up' => 'Récupérée',
            'in_transit' => 'En livraison',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            default => ucfirst($status),
        };
    }

    private function getFoodStatusColor($status): string
    {
        return match($status) {
            'pending' => 'yellow',
            'accepted', 'preparing' => 'blue',
            'ready', 'assigned' => 'cyan',
            'picked_up', 'in_transit' => 'indigo',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    private function getUrgentSaleStatusLabel($status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'confirmed' => 'À livrer',
            'completed' => 'Livrée',
            'cancelled' => 'Annulée',
            default => ucfirst($status),
        };
    }

    private function getUrgentSaleStatusColor($status): string
    {
        return match($status) {
            'pending' => 'yellow',
            'confirmed' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    private function getBookingStatusLabel($status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmé',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => ucfirst($status),
        };
    }

    private function getBookingStatusColor($status): string
    {
        return match($status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'in_progress' => 'indigo',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    private function getEquipmentStatusLabel($status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'pending_pickup' => 'À récupérer',
            'active' => 'En location',
            'completed' => 'Retournée',
            'cancelled' => 'Annulée',
            default => ucfirst($status),
        };
    }

    private function getEquipmentStatusColor($status): string
    {
        return match($status) {
            'pending' => 'yellow',
            'confirmed', 'pending_pickup' => 'orange',
            'active' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Marquer une livraison comme en cours (départ)
     */
    public function startDelivery(Request $request, $type, $id)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        switch ($type) {
            case 'food_internal':
                $order = FoodOrder::where('prestataire_id', $prestataire->id)->findOrFail($id);
                $order->update([
                    'delivery_status' => 'in_transit',
                    'picked_up_at' => now(),
                ]);
                return back()->with('success', 'Livraison démarrée !');

            case 'urgent_sale':
                // Pas de statut intermédiaire pour le moment
                return back()->with('info', 'Livrez puis marquez comme complétée.');

            default:
                return back()->with('error', 'Type non supporté.');
        }
    }

    /**
     * Marquer une livraison comme complétée
     */
    public function completeDelivery(Request $request, $type, $id)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        switch ($type) {
            case 'food_internal':
                $order = FoodOrder::where('prestataire_id', $prestataire->id)->findOrFail($id);
                $order->update([
                    'delivery_status' => 'delivered',
                    'delivered_at' => now(),
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                return back()->with('success', 'Livraison Food terminée !');

            case 'urgent_sale':
                $reservation = UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))
                    ->findOrFail($id);
                $reservation->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                // Mettre à jour le stock
                $reservation->urgentSale->increment('sold_quantity', $reservation->quantity);
                $reservation->urgentSale->decrement('reserved_quantity', $reservation->quantity);
                return back()->with('success', 'Vente urgente livrée !');

            case 'service':
                $booking = Booking::where('prestataire_id', $prestataire->id)->findOrFail($id);
                $booking->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                return back()->with('success', 'Service terminé !');

            case 'equipment':
                // La complétion d'équipement est plus complexe (retour), à gérer séparément
                return back()->with('info', 'Utilisez la page de location pour gérer le retour.');

            default:
                return back()->with('error', 'Type non supporté.');
        }
    }
}
