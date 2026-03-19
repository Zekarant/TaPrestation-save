<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use App\Models\PrestataireEvent;
use Carbon\Carbon;

use App\Support\TableExistenceCache;
class AgendaController extends Controller
{
    /**
     * Affiche l'agenda du prestataire
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        // Paramètres de vue
        $view = $request->get('view', 'month');
        $search = $request->get('search', '');
        $serviceFilter = $request->get('service', '');
        $statusFilter = $request->get('status', '');
        
        // Récupérer les services du prestataire
        $services = $prestataire->services;
        
        // Statistiques — audit 4.6: 1 requête groupBy au lieu de 4 COUNT séparés
        $statusCounts = $prestataire->bookings()
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');
        $stats = [
            'total' => $statusCounts->sum(),
            'confirmed' => (int) ($statusCounts['confirmed'] ?? 0),
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'completed' => (int) ($statusCounts['completed'] ?? 0),
        ];
        
        // Réservations récentes pour la liste des demandes (services)
        $recentServiceBookings = $prestataire->bookings()
            ->with(['service', 'client.user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'title' => $booking->service->title ?? 'Service',
                    'client_name' => $booking->client->user->name ?? 'N/A',
                    'start_date' => $booking->start_datetime,
                    'status' => $booking->status,
                    'can_confirm' => $booking->canBeConfirmed(),
                    'can_cancel' => $booking->canBeCancelled(),
                    'can_complete' => $booking->canBeCompleted(),
                    'can_accept' => $booking->status === 'pending' && $booking->service,
                    'can_reject' => $booking->status === 'pending' && $booking->service,
                    'url' => route('prestataire.bookings.show', $booking->id),
                    'type' => 'service'
                ];
            });
        
        // Demandes de location d'équipements récentes
        $recentRentalRequests = collect();
        if (TableExistenceCache::has('equipment_rental_requests')) {
            try {
                $recentRentalRequests = EquipmentRentalRequest::where('prestataire_id', $prestataire->id)
                    ->with(['equipment', 'client.user'])
                    ->orderBy('created_at', 'desc')
                    ->take(20)
                    ->get()
                    ->map(function($request) {
                        $isEquipmentAvailable = true;
                        $availabilityMessage = null;
                        
                        if ($request->equipment && $request->status === 'pending') {
                            $isEquipmentAvailable = $request->equipment->isAvailableForPeriod(
                                $request->start_date, 
                                $request->end_date
                            );
                            
                            if (!$isEquipmentAvailable) {
                                $overlappingRentals = $request->equipment->rentals()
                                    ->whereIn('status', ['confirmed', 'in_use', 'delivered'])
                                    ->where(function ($query) use ($request) {
                                        $start = Carbon::parse($request->start_date)->startOfDay();
                                        $end = Carbon::parse($request->end_date)->startOfDay();
                                        $query->whereBetween('start_date', [$start, $end])
                                              ->orWhereBetween('end_date', [$start, $end])
                                              ->orWhere(function ($q) use ($start, $end) {
                                                  $q->where('start_date', '<=', $start)
                                                    ->where('end_date', '>=', $end);
                                              });
                                    })
                                    ->with(['client.user'])
                                    ->first();
                                    
                                if ($overlappingRentals) {
                                    $availabilityMessage = 'L\'équipement est en cours de location du ' . 
                                        Carbon::parse($overlappingRentals->start_date)->format('d/m/Y') . 
                                        ' au ' . 
                                        Carbon::parse($overlappingRentals->end_date)->format('d/m/Y') . 
                                        ' par ' . 
                                        ($overlappingRentals->client->user->name ?? 'un client');
                                } else {
                                    $availabilityMessage = 'L\'équipement est en cours de location pour cette période.';
                                }
                            }
                        }
                        
                        return [
                            'id' => $request->id,
                            'title' => $request->equipment->name ?? 'Location équipement',
                            'client_name' => $request->client->user->name ?? 'N/A',
                            'start_date' => $request->start_date,
                            'status' => $request->status,
                            'can_confirm' => $request->status === 'pending',
                            'can_cancel' => $request->status === 'pending',
                            'can_complete' => false,
                            'can_accept' => $request->status === 'pending' && $request->equipment && $isEquipmentAvailable,
                            'can_reject' => $request->status === 'pending' && $request->equipment,
                            'availability_message' => $availabilityMessage,
                            'url' => route('prestataire.agenda.equipment-request.show', $request->id),
                            'type' => 'equipment'
                        ];
                    });
            } catch (\Exception $e) {
                // Table not available
            }
        }
        
        // Commandes food ACCEPTÉES planifiées (avec requested_at dans le futur)
        // Flow: pending → vendeur accepte → va dans l'agenda (scheduled) → jour J passe → auto-completed
        $scheduledFoodOrders = collect();
        if (TableExistenceCache::has('food_orders')) {
            try {
                $today = now()->startOfDay();
                // Même filtre de paiement que la cuisine
                // NOTE: En prod on a des commandes acceptées avec payment_status='pending'
                // Si on ne l'inclut pas, elles n'apparaissent pas dans l'agenda.
                $visiblePaymentStatuses = ['paid', 'pending_capture', 'pending', 'partial'];
                
                // Debug log
                \Log::info('Agenda scheduledFoodOrders - Prestataire ID: ' . $prestataire->id . ', Today: ' . $today->toDateString());
                
                // Commandes food avec requested_at:
                // - scheduled/accepted: en attente
                // - completed/delivered: terminées (pour le filtre)
                $query = \App\Models\FoodOrder::where('prestataire_id', $prestataire->id)
                    ->whereIn('status', ['scheduled', 'accepted', 'completed', 'delivered'])
                    ->where(function ($q) use ($visiblePaymentStatuses) {
                        $q->where('payment_method', 'cash')
                          ->orWhereIn('payment_status', $visiblePaymentStatuses);
                    });
                
                // Log the count
                $count = $query->count();
                \Log::info('Agenda scheduledFoodOrders - Found: ' . $count);
                
                $scheduledFoodOrders = $query->with(['client', 'items'])
                    ->orderBy('requested_at', 'desc')
                    ->take(50)
                    ->get()
                    ->map(function($order) use ($today) {
                        $itemsCount = $order->items ? $order->items->count() : 0;
                        $itemsSummary = '';
                        if ($itemsCount > 0) {
                            $itemsSummary = $order->items->take(2)->pluck('name')->implode(', ');
                            if ($itemsCount > 2) {
                                $itemsSummary .= ' +' . ($itemsCount - 2);
                            }
                        }
                        
                        // client est directement un User
                        $clientName = $order->client->name ?? 'Client';
                        
                        // Déterminer le statut pour l'affichage
                        // scheduled ou accepted = en attente
                        // completed ou delivered = terminée
                        $displayStatus = $order->status;
                        if (in_array($order->status, ['scheduled', 'accepted'])) {
                            $displayStatus = 'scheduled'; // En attente
                        } elseif (in_array($order->status, ['completed', 'delivered'])) {
                            $displayStatus = 'completed'; // Terminée
                        }
                        
                        // Date à afficher: requested_at ou created_at
                        $displayDate = $order->requested_at ?? $order->created_at;
                        
                        return [
                            'id' => $order->id,
                            'title' => '🍽️ Commande #' . $order->id . ($itemsSummary ? ' - ' . $itemsSummary : ''),
                            'client_name' => $clientName,
                            'start_date' => $displayDate,
                            'requested_date' => $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d/m/Y H:i') : '',
                            'status' => $displayStatus,
                            'can_confirm' => false,
                            'can_cancel' => false,
                            'can_complete' => false,
                            'can_accept' => false,
                            'can_reject' => false,
                            'url' => route('prestataire.food-orders.show', $order->id),
                            'type' => 'food',
                            'total' => number_format($order->total ?? 0, 2) . ' €',
                            'is_scheduled' => $displayStatus === 'scheduled',
                        ];
                    });
                    
                \Log::info('Agenda scheduledFoodOrders - Mapped count: ' . $scheduledFoodOrders->count());
            } catch (\Exception $e) {
                \Log::error('Agenda scheduledFoodOrders ERROR: ' . $e->getMessage());
            }
        }
            
        // Combiner tous les types de demandes
        $recentDemands = $recentServiceBookings
            ->concat($recentRentalRequests)
            ->concat($scheduledFoodOrders)
            ->sortByDesc('start_date')
            ->values()
            ->take(40);
        
        // Réservations pour la vue liste
        $bookingsQuery = $prestataire->bookings()->with(['service', 'client.user']);
        
        if ($search) {
            $bookingsQuery->where(function($q) use ($search) {
                $q->whereHas('client.user', function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('service', function($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%');
                })
                ->orWhere('booking_number', 'like', '%' . $search . '%');
            });
        }
        
        if ($serviceFilter) {
            $bookingsQuery->where('service_id', $serviceFilter);
        }
        
        if ($statusFilter) {
            $bookingsQuery->where('status', $statusFilter);
        }
        
        $bookings = $bookingsQuery->orderBy('start_datetime', 'desc')->paginate(10);
        
        // Récupérer les événements pour le calendrier simple
        $currentDate = $request->get('date') ? Carbon::parse($request->get('date')) : now();
        $currentView = $request->get('view', 'month');
        
        if ($currentView === 'week') {
            $startOfPeriod = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
            $endOfPeriod = $currentDate->copy()->endOfWeek(Carbon::SUNDAY);
        } else {
            $startOfPeriod = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $endOfPeriod = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        }
        
        // Services bookings
        $serviceEvents = Booking::where('prestataire_id', $prestataire->id)
            ->whereBetween('start_datetime', [$startOfPeriod, $endOfPeriod])
            ->with(['service', 'client.user'])
            ->get()
            ->map(function ($booking) {
                $durationMinutes = 60;
                if ($booking->start_datetime && $booking->end_datetime) {
                    $durationMinutes = max(1, $booking->start_datetime->diffInMinutes($booking->end_datetime));
                }

                return [
                    'id' => $booking->id,
                    'title' => ($booking->service?->title ?? 'Service') . ' - ' . ($booking->client?->user?->name ?? 'Client'),
                    'start' => $booking->start_datetime->toIso8601String(),
                    'end' => $booking->end_datetime ? $booking->end_datetime->toIso8601String() : null,
                    'duration' => $durationMinutes,
                    'type' => 'service',
                    'status' => $booking->status,
                    'url' => route('prestataire.bookings.show', $booking->id),
                ];
            });
        
        // Equipment rentals
        $equipmentEvents = collect();
        if (TableExistenceCache::has('equipment_rental_requests')) {
            try {
                $equipmentEvents = EquipmentRentalRequest::where('prestataire_id', $prestataire->id)
                    ->whereBetween('start_date', [$startOfPeriod, $endOfPeriod])
                    ->with(['equipment', 'client.user'])
                    ->get()
                    ->map(function ($rental) {
                        return [
                            'id' => $rental->id,
                            'title' => ($rental->equipment?->name ?? 'Équipement') . ' - ' . ($rental->client?->user?->name ?? 'Client'),
                            'start' => Carbon::parse($rental->start_date)->toIso8601String(),
                            'type' => 'equipment',
                            'status' => $rental->status,
                            'url' => route('prestataire.agenda.equipment-request.show', $rental->id),
                        ];
                    });
            } catch (\Exception $e) {
                \Log::warning('Failed to load equipment rental events for agenda: ' . $e->getMessage());
            }
        }

        // Food orders
        $foodEvents = collect();
        if (TableExistenceCache::has('food_orders')) {
            try {
                // Afficher dans le calendrier les commandes food (scheduled, accepted, completed, delivered)
                // NOTE: client est directement un User (pas de relation client.user).
                $visiblePaymentStatuses = ['paid', 'pending_capture', 'pending', 'partial'];

                $foodEvents = \App\Models\FoodOrder::where('prestataire_id', $prestataire->id)
                    ->whereIn('status', ['scheduled', 'accepted', 'completed', 'delivered'])
                    ->where(function ($q) use ($startOfPeriod, $endOfPeriod) {
                        // Commandes avec requested_at dans la période OU created_at si pas de requested_at
                        $q->whereBetween('requested_at', [$startOfPeriod, $endOfPeriod])
                          ->orWhere(function ($q2) use ($startOfPeriod, $endOfPeriod) {
                              $q2->whereNull('requested_at')
                                 ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod]);
                          });
                    })
                    ->where(function ($q) use ($visiblePaymentStatuses) {
                        $q->where('payment_method', 'cash')
                          ->orWhereIn('payment_status', $visiblePaymentStatuses);
                    })
                    ->with(['client'])
                    ->get()
                    ->map(function ($order) {
                        $clientName = $order->client?->name ?? 'Client';
                        $displayDate = $order->requested_at ?? $order->created_at;
                        
                        // Normaliser le statut pour l'affichage
                        $displayStatus = $order->status;
                        if (in_array($order->status, ['scheduled', 'accepted'])) {
                            $displayStatus = 'scheduled';
                        } elseif (in_array($order->status, ['completed', 'delivered'])) {
                            $displayStatus = 'completed';
                        }
                        
                        return [
                            'id' => $order->id,
                            'title' => '🍽️ #' . $order->id . ' - ' . $clientName,
                            'start' => Carbon::parse($displayDate)->toIso8601String(),
                            'type' => 'food',
                            'status' => $displayStatus,
                            'url' => route('prestataire.food-orders.show', $order->id),
                        ];
                    });
            } catch (\Exception $e) {
                \Log::warning('Failed to load food order events for agenda: ' . $e->getMessage());
            }
        }

        // Manual events
        $manualEvents = collect();
        if (TableExistenceCache::has('prestataire_events')) {
            try {
                $manualEvents = PrestataireEvent::where('prestataire_id', $prestataire->id)
                    ->whereBetween('start_datetime', [$startOfPeriod, $endOfPeriod])
                    ->get()
                    ->map(function ($event) {
                        $durationMinutes = 60;
                        if (isset($event->start_datetime) && isset($event->end_datetime) && $event->end_datetime) {
                            $durationMinutes = max(1, $event->start_datetime->diffInMinutes($event->end_datetime));
                        }

                        return [
                            'id' => $event->id,
                            'title' => $event->title,
                            'start' => $event->start_datetime->toIso8601String(),
                            'end' => isset($event->end_datetime) && $event->end_datetime ? $event->end_datetime->toIso8601String() : null,
                            'duration' => $durationMinutes,
                            'type' => 'manual',
                            'status' => 'confirmed',
                            'url' => '#',
                            'color' => $event->color,
                        ];
                    });
            } catch (\Exception $e) {
                \Log::warning('Failed to load manual events for agenda: ' . $e->getMessage());
            }
        }

        $calendarEvents = $serviceEvents
            ->concat($equipmentEvents)
            ->concat($foodEvents)
            ->concat($manualEvents)
            ->toArray();
        
        return view('prestataire.agenda.index-simple', compact(
            'view', 'search', 'serviceFilter', 'statusFilter', 'services', 
            'stats', 'recentDemands', 'bookings', 'calendarEvents'
        ));
    }
    
    /**
     * API pour récupérer les événements du calendrier
     */
    public function events(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->prestataire) {
                return response()->json(['error' => 'Accès non autorisé. Veuillez vous connecter.'], 401);
            }
            $prestataire = $user->prestataire;
            
            $start = $request->get('start') ? Carbon::parse($request->get('start')) : now()->startOfMonth();
            $end = $request->get('end') ? Carbon::parse($request->get('end')) : now()->endOfMonth();
            $filter = $request->get('filter', 'all');
            
            $allEvents = collect();

            // 1. Récupérer les réservations de services (si filtre 'all' ou 'service')
            if ($filter === 'all' || $filter === 'service') {
                $bookings = Booking::where('prestataire_id', $prestataire->id)
                    ->whereBetween('start_datetime', [$start, $end])
                    ->with(['service', 'client.user'])
                    ->get()
                    ->map(function ($booking) {
                        $sessionId = null;
                        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
                            $sessionId = $matches[1];
                        }
                        
                        $serviceName = $booking->service?->name ?? 'Service';
                        $clientName = $booking->client?->user?->name ?? 'Client';

                        return [
                            'id' => $booking->id,
                            'title' => $serviceName . ' - ' . $clientName,
                            'start' => $booking->start_datetime->toIso8601String(),
                            'end' => $booking->end_datetime->toIso8601String(),
                            'backgroundColor' => '#3b82f6', // Bleu
                            'borderColor' => '#3b82f6',
                            'textColor' => '#ffffff',
                            'extendedProps' => [
                                'id' => $booking->id,
                                'type' => 'service',
                                'status' => $booking->status,
                                'clientName' => $clientName,
                                'serviceName' => $serviceName,
                                'sessionId' => $sessionId,
                                'url' => route('prestataire.bookings.show', $booking->id),
                            ]
                        ];
                    });
                $allEvents = $allEvents->concat($bookings);
            }

            // 2. Récupérer les demandes de location d'équipements (si filtre 'all' ou 'equipment')
            if (($filter === 'all' || $filter === 'equipment') && TableExistenceCache::has('equipment_rental_requests')) {
                try {
                    $equipmentRentals = EquipmentRentalRequest::where('prestataire_id', $prestataire->id)
                        ->where('status', 'accepted')
                        ->whereBetween('start_date', [$start, $end])
                        ->with(['equipment', 'client.user'])
                        ->get()
                        ->map(function ($rentalRequest) {
                            $startDate = Carbon::parse($rentalRequest->start_date)->startOfDay();
                            $endDate = Carbon::parse($rentalRequest->end_date)->endOfDay();
                            
                            $equipmentName = $rentalRequest->equipment?->name ?? 'Location équipement';
                            $clientName = $rentalRequest->client?->user?->name ?? 'N/A';

                            return [
                                'id' => 'equipment_' . $rentalRequest->id,
                                'title' => $equipmentName,
                                'start' => $startDate->toISOString(),
                                'end' => $endDate->toISOString(),
                                'backgroundColor' => '#10b981', // Vert
                                'borderColor' => '#10b981',
                                'textColor' => '#ffffff',
                                'allDay' => true,
                                'extendedProps' => [
                                    'clientName' => $clientName,
                                    'equipmentName' => $equipmentName,
                                    'status' => ucfirst($rentalRequest->status),
                                    'rentalUrl' => route('prestataire.agenda.equipment-request.show', $rentalRequest->id),
                                    'startDate' => Carbon::parse($rentalRequest->start_date)->format('d/m/Y'),
                                    'endDate' => Carbon::parse($rentalRequest->end_date)->format('d/m/Y'),
                                    'type' => 'equipment',
                                    'itemType' => 'equipment_rental_request'
                                ]
                            ];
                        });
                    $allEvents = $allEvents->concat($equipmentRentals);
                } catch (\Exception $e) {
                    // Table or data not available
                }
            }
            
            // 3. Ajouter les événements créés manuellement (toujours affichés ou filtrables si besoin)
            if ($filter === 'all' && TableExistenceCache::has('prestataire_events')) {
                try {
                    $prestataireEvents = PrestataireEvent::where('prestataire_id', $prestataire->id)
                        ->whereBetween('start_datetime', [$start, $end])
                        ->get()
                        ->map(function ($event) {
                            return [
                                'id' => 'event_' . $event->id,
                                'title' => $event->title,
                                'start' => $event->start_datetime->toIso8601String(),
                                'end' => $event->end_datetime->toIso8601String(),
                                'backgroundColor' => $event->color,
                                'borderColor' => $event->color,
                                'textColor' => '#ffffff',
                                'extendedProps' => [
                                    'type' => 'manual_event',
                                    'description' => $event->description,
                                    'eventType' => $event->type,
                                    'itemType' => 'manual_event',
                                    'eventId' => $event->id,
                                ]
                            ];
                        });
                    $allEvents = $allEvents->concat($prestataireEvents);
                } catch (\Exception $e) {
                    // Table not available
                }
            }
            
            return response()->json($allEvents);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Agenda events error: ' . $e->getMessage());
            return response()->json(['error' => 'Chargement de l\'agenda impossible pour le moment.'], 500);
        }
    }
    
    /**
     * Crée un nouvel événement manuel
     */
    public function storeEvent(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        $durationMode = $request->get('duration_mode', 'duration');
        
        // Mode période (start_date + end_date)
        if ($durationMode === 'period' || ($request->has('start_date') && $request->has('end_date'))) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'notes' => 'nullable|string',
                'color' => 'nullable|string',
            ]);
            
            $startDateTime = Carbon::parse($validated['start_date'])->startOfDay();
            $endDateTime = Carbon::parse($validated['end_date'])->endOfDay();
            $description = $validated['notes'] ?? null;
            $color = $validated['color'] ?? '#ec4899';
        }
        // Mode durée (date + time + duration)
        elseif ($request->has('date') && $request->has('time')) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'duration' => 'required|integer|min:1|max:24',
                'notes' => 'nullable|string',
                'color' => 'nullable|string',
            ]);
            
            $startDateTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
            $endDateTime = $startDateTime->copy()->addHours((int)$validated['duration']);
            $description = $validated['notes'] ?? null;
            $color = $validated['color'] ?? '#ec4899';
        } 
        // Format API JSON
        else {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_datetime' => 'required|date',
                'end_datetime' => 'required|date|after:start_datetime',
                'type' => 'nullable|in:unavailable,appointment,other',
                'color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            ]);
            
            $startDateTime = Carbon::parse($validated['start_datetime']);
            $endDateTime = Carbon::parse($validated['end_datetime']);
            $description = $validated['description'] ?? null;
            $color = $validated['color'] ?? '#3788d8';
        }
        
        try {
            $event = PrestataireEvent::create([
                'prestataire_id' => $prestataire->id,
                'title' => $validated['title'],
                'description' => $description,
                'start_datetime' => $startDateTime,
                'end_datetime' => $endDateTime,
                'type' => $validated['type'] ?? 'other',
                'color' => $color,
            ]);
            
            // Si requête AJAX/JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Événement créé avec succès',
                    'event' => [
                        'id' => 'event_' . $event->id,
                        'title' => $event->title,
                        'start' => $event->start_datetime->toIso8601String(),
                        'end' => $event->end_datetime->toIso8601String(),
                        'backgroundColor' => $event->color,
                        'borderColor' => $event->color,
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'manual_event',
                            'description' => $event->description,
                            'eventType' => $event->type,
                            'itemType' => 'manual_event',
                            'eventId' => $event->id,
                        ]
                    ]
                ]);
            }
            
            // Sinon redirection avec message flash
            return redirect()->route('prestataire.agenda.index', ['date' => $startDateTime->format('Y-m-d')])
                ->with('success', 'Événement "' . $event->title . '" créé avec succès !');
                
        } catch (\Exception $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'événement.'
                ], 500);
            }
            
            return back()->with('error', 'Erreur lors de la création de l\'événement.');
        }
    }
    
    /**
     * Supprime un événement manuel
     */
    public function destroyEvent(PrestataireEvent $event)
    {
        $user = Auth::user();
        
        if ($event->prestataire_id !== $user->prestataire->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }
        
        try {
            $event->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Événement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'événement'
            ], 500);
        }
    }
    
    /**
     * Affiche les détails d'une réservation
     */
    public function show(Booking $booking)
    {
        $user = Auth::user();
        
        if ($booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $booking->load(['service', 'client.user', 'timeSlot']);
        
        return response()->json([
            'booking' => $booking,
            'canConfirm' => $booking->canBeConfirmed(),
            'canCancel' => $booking->canBeCancelled(),
            'canComplete' => $booking->canBeCompleted()
        ]);
    }
    
    /**
     * Affiche les détails d'une location d'équipement
     */
    public function showEquipmentRental(EquipmentRental $rental)
    {
        $user = Auth::user();
        
        if ($rental->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $rental->load(['equipment', 'client.user']);
        
        return response()->json([
            'rental' => $rental,
            'canStart' => $rental->status === 'confirmed',
            'canComplete' => $rental->status === 'active'
        ]);
    }
    
    /**
     * Affiche les détails d'une demande de location d'équipement
     */
    public function showEquipmentRequest(EquipmentRentalRequest $rentalRequest)
    {
        $user = Auth::user();
        
        if ($rentalRequest->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $rentalRequest->load(['equipment', 'client.user', 'rental']);
        
        // Rediriger vers la page de détails des demandes de location
        return redirect()->route('prestataire.equipment-rental-requests.show', $rentalRequest);
    }
    
    /**
     * Met à jour le statut d'une réservation
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        if ($booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed',
            'reason' => 'nullable|string|max:500'
        ]);
        
        $status = $request->get('status');
        $reason = $request->get('reason');
        
        switch ($status) {
            case 'confirmed':
                if ($booking->confirm()) {
                    return response()->json(['success' => true, 'message' => 'Réservation confirmée']);
                }
                break;
                
            case 'cancelled':
                if ($booking->cancel($reason)) {
                    return response()->json(['success' => true, 'message' => 'Réservation annulée']);
                }
                break;
                
            case 'completed':
                if ($booking->canBeCompleted()) {
                    $booking->update([
                        'status' => 'completed',
                        'completed_at' => now()
                    ]);

                    // Libérer l'escrow automatiquement à la complétion
                    try {
                        $escrow = \Illuminate\Support\Facades\DB::table('escrow_transactions')
                            ->where('escrowable_type', \App\Models\Booking::class)
                            ->where('escrowable_id', $booking->id)
                            ->whereIn('status', ['pending', 'held'])
                            ->first();

                        if ($escrow) {
                            $escrowService = app(\App\Services\EscrowService::class);
                            $escrowService->releaseToPrestataire($escrow->id);
                        }
                    } catch (\Exception $e) {
                        \Log::error("Erreur libération escrow pour booking #{$booking->id}: " . $e->getMessage());
                    }

                    // Générer les factures si elles n'existent pas encore
                    try {
                        $transaction = \App\Models\PaymentTransaction::where('booking_id', $booking->id)->first();
                        if ($transaction) {
                            $invoiceService = app(\App\Services\InvoiceGenerationService::class);
                            $invoiceService->generateForBooking($booking, $transaction);
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Erreur génération facture pour booking #{$booking->id}: " . $e->getMessage());
                    }

                    return response()->json(['success' => true, 'message' => 'Réservation marquée comme terminée']);
                }
                break;
        }
        
        return response()->json(['success' => false, 'message' => 'Action non autorisée'], 400);
    }
    
    /**
     * Accepte une demande de location d'équipement
     */
    public function acceptEquipmentRequest(Request $request, EquipmentRentalRequest $rentalRequest)
    {
        $user = Auth::user();
        
        if ($rentalRequest->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $response = $request->get('response');
        
        try {
            $rentalRequest->accept($response);
            return response()->json(['success' => true, 'message' => 'Demande de location acceptée']);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'accepter cette demande pour le moment.',
            ], 400);
        }
    }
    
    /**
     * Rejette une demande de location d'équipement
     */
    public function rejectEquipmentRequest(Request $request, EquipmentRentalRequest $rentalRequest)
    {
        $user = Auth::user();
        
        if ($rentalRequest->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);
        
        $reason = $request->get('reason');
        
        try {
            $rentalRequest->reject($reason);
            return response()->json(['success' => true, 'message' => 'Demande de location refusée']);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de refuser cette demande pour le moment.',
            ], 400);
        }
    }
}
