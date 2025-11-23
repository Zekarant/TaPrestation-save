<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use Carbon\Carbon;

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
        
        // Statistiques
        $stats = [
            'total' => $prestataire->bookings()->count(),
            'confirmed' => $prestataire->bookings()->where('status', 'confirmed')->count(),
            'pending' => $prestataire->bookings()->where('status', 'pending')->count(),
            'completed' => $prestataire->bookings()->where('status', 'completed')->count(),
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
                    'can_accept' => $booking->status === 'pending' && $booking->service, // Check if service exists
                    'can_reject' => $booking->status === 'pending' && $booking->service, // Check if service exists
                    'url' => route('prestataire.bookings.show', $booking->id),
                    'type' => 'service'
                ];
            });
        
        // Demandes de location d'équipements récentes
        $recentRentalRequests = EquipmentRentalRequest::where('prestataire_id', $prestataire->id)
            ->with(['equipment', 'client.user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($request) {
                // Check if equipment is already rented during the requested period
                $isEquipmentAvailable = true;
                $availabilityMessage = null;
                
                if ($request->equipment && $request->status === 'pending') {
                    // Check if the equipment is available for the requested period
                    $isEquipmentAvailable = $request->equipment->isAvailableForPeriod(
                        $request->start_date, 
                        $request->end_date
                    );
                    
                    if (!$isEquipmentAvailable) {
                        // Find overlapping rentals to provide more detailed information
                        $overlappingRentals = $request->equipment->rentals()
                            ->whereIn('status', ['confirmed', 'in_use', 'delivered'])
                            ->where(function ($query) use ($request) {
                                $start = Carbon::parse($request->start_date)->startOfDay();
                                $end = Carbon::parse($request->end_date)->startOfDay();
                                
                                // Period starts during an existing rental
                                $query->whereBetween('start_date', [$start, $end])
                                      // Period ends during an existing rental
                                      ->orWhereBetween('end_date', [$start, $end])
                                      // Period completely encompasses an existing rental
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
                    'can_accept' => $request->status === 'pending' && $request->equipment && $isEquipmentAvailable, // Check if equipment exists and is available
                    'can_reject' => $request->status === 'pending' && $request->equipment, // Check if equipment exists
                    'availability_message' => $availabilityMessage, // Message if equipment is not available
                    'url' => route('prestataire.agenda.equipment-request.show', $request->id),
                    'type' => 'equipment'
                ];
            });
            
        // Combiner les deux types de demandes et trier par date de création (du plus récent au plus ancien)
        $recentDemands = $recentServiceBookings->concat($recentRentalRequests)
            ->sortByDesc('start_date')
            ->values() // Re-index the collection
            ->take(31);
        
        // Réservations pour la vue liste (avec filtres)
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
        
        return view('prestataire.agenda.index', compact(
            'view', 'search', 'serviceFilter', 'statusFilter', 'services', 
            'stats', 'recentDemands', 'bookings'
        ));
    }
    
    /**
     * API pour récupérer les événements du calendrier
     */
    public function events(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        // Log the request parameters for debugging
        \Log::info('Agenda events request', [
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'filter' => $request->get('filter', 'all')
        ]);
        
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));
        $filter = $request->get('filter', 'all'); // Get the filter parameter
        
        // Log the parsed dates
        \Log::info('Parsed dates', [
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString()
        ]);
        
        // Récupérer les réservations de services
        $bookingsQuery = Booking::where('prestataire_id', $prestataire->id)
            ->whereBetween('start_datetime', [$start, $end])
            ->with(['service', 'client.user']);
            
        // Récupérer les demandes de location d'équipements (seulement les acceptées)
        $equipmentRentalRequestsQuery = EquipmentRentalRequest::where('prestataire_id', $prestataire->id)
            ->where('status', 'accepted') // Only show accepted equipment rental requests
            ->whereBetween('start_date', [$start, $end])
            ->with(['equipment', 'client.user']);
        
        // Apply filtering based on the filter parameter
        if ($filter === 'service') {
            // Only show service events, hide equipment events
            $equipmentRentalRequestsQuery->whereRaw('1=0'); // This will return no equipment rental requests
        } elseif ($filter === 'equipment') {
            // Only show equipment events, hide service events
            $bookingsQuery->whereRaw('1=0'); // This will return no bookings
        }
        // For 'all' filter, both queries remain unchanged to show all events
        
        $bookings = $bookingsQuery->get();
        $equipmentRentalRequests = $equipmentRentalRequestsQuery->get();
        
        // Log the number of records found
        \Log::info('Records found', [
            'bookings' => $bookings->count(),
            'equipment_rental_requests' => $equipmentRentalRequests->count()
        ]);
        
        // Format the event data for FullCalendar
        $formattedEvents = $bookings->map(function ($booking) {
            // Extract session ID from notes if it exists
            $sessionId = null;
            if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
                $sessionId = $matches[1];
            }
            
            // Toujours utiliser le bleu pour les services, peu importe le statut
            $color = '#3b82f6'; // Bleu pour services
            
            return [
                'id' => $booking->id,
                'title' => ($booking->service ? $booking->service->name : 'Service') . ' - ' . ($booking->client ? $booking->client->user->name : 'Client'),
                'start' => $booking->start_datetime->toIso8601String(),
                'end' => $booking->end_datetime->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#000000', // Texte noir
                'extendedProps' => [
                    'id' => $booking->id, // Add this line to ensure service events are clickable
                    'type' => 'service',
                    'status' => $booking->status,
                    'clientName' => $booking->client ? $booking->client->user->name : 'Client',
                    'serviceName' => $booking->service ? $booking->service->name : 'Service',
                    'sessionId' => $sessionId,
                    'url' => $booking->id ? url('/prestataire/bookings/' . $booking->id) : '#',
                ]
            ];
        });
        
        // Mapper les demandes de location d'équipements
        $equipmentEvents = $equipmentRentalRequests->map(function ($rentalRequest) {
            // Toujours utiliser le vert pour les équipements, peu importe le statut
            $color = '#10b981'; // Vert pour équipements
            
            // Format dates properly for calendar display
            $startDate = Carbon::parse($rentalRequest->start_date)->startOfDay();
            $endDate = Carbon::parse($rentalRequest->end_date)->endOfDay();
            
            return [
                'id' => 'equipment_' . $rentalRequest->id,
                'title' => ($rentalRequest->equipment->name ?? 'Location équipement'),
                'start' => $startDate->toISOString(),
                'end' => $endDate->toISOString(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#000000', // Texte noir
                'allDay' => true, // Equipment rental requests are typically all-day events
                'extendedProps' => [
                    'clientName' => $rentalRequest->client->user->name ?? 'N/A',
                    'equipmentName' => $rentalRequest->equipment->name ?? 'N/A',
                    'status' => ucfirst($rentalRequest->status),
                    'rentalUrl' => route('prestataire.agenda.equipment-request.show', $rentalRequest->id),
                    'startDate' => Carbon::parse($rentalRequest->start_date)->format('d/m/Y'),
                    'endDate' => Carbon::parse($rentalRequest->end_date)->format('d/m/Y'),
                    'type' => 'equipment',
                    'itemType' => 'equipment_rental_request'
                ]
            ];
        });
        
        // Combiner tous les événements
        $allEvents = $formattedEvents->concat($equipmentEvents);
        
        // Log the final events count
        \Log::info('Final events count', [
            'total_events' => $allEvents->count()
        ]);
        
        return response()->json($allEvents);
    }
    
    /**
     * Affiche les détails d'une réservation
     */
    public function show(Booking $booking)
    {
        $user = Auth::user();
        
        // Vérifier que la réservation appartient au prestataire connecté
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
        
        // Vérifier que la location appartient au prestataire connecté
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
        
        // Vérifier que la demande appartient au prestataire connecté
        if ($rentalRequest->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $rentalRequest->load(['equipment', 'client.user']);
        
        return response()->json([
            'rentalRequest' => $rentalRequest,
            'canAccept' => $rentalRequest->status === 'pending',
            'canReject' => $rentalRequest->status === 'pending'
        ]);
    }
    
    /**
     * Met à jour le statut d'une réservation
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        // Vérifier que la réservation appartient au prestataire connecté
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
                    return response()->json(['success' => true, 'message' => 'Réservation marquée comme terminée']);
                }
                break;
        }
        
        return response()->json(['success' => false, 'message' => 'Action non autorisée'], 400);
    }
    

    
    /**
     * Retourne la couleur selon le statut
     */
    public function recentBookings(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $bookings = Booking::where('prestataire_id', $prestataire->id)
            ->with(['service', 'client.user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($bookings);
    }

    /**
     * Accepte une demande de location d'équipement
     */
    public function acceptEquipmentRequest(Request $request, EquipmentRentalRequest $rentalRequest)
    {
        $user = Auth::user();
        
        // Vérifier que la demande appartient au prestataire connecté
        if ($rentalRequest->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }
        
        $response = $request->get('response');
        
        try {
            $rentalRequest->accept($response);
            return response()->json(['success' => true, 'message' => 'Demande de location acceptée']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Rejette une demande de location d'équipement
     */
    public function rejectEquipmentRequest(Request $request, EquipmentRentalRequest $rentalRequest)
    {
        $user = Auth::user();
        
        // Vérifier que la demande appartient au prestataire connecté
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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Retourne la couleur selon le statut
     */
    private function getStatusColor($status)
    {
        $colors = [
            'pending' => '#ff9f43',
            'confirmed' => '#28c76f',
            'cancelled' => '#82868b',
            'completed' => '#1e90ff',
            'refused' => '#dc3545', // Red
            'rejected' => '#dc3545',
            'accepted' => '#28c76f'
        ];
        
        return $colors[$status] ?? '#82868b';
    }
}