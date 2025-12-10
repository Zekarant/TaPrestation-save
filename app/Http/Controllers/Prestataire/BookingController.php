<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\EquipmentRentalRequest;
use App\Models\UrgentSale;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:prestataire');
    }

    /**
     * Display a listing of bookings for the prestataire
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        if (!$prestataire) {
            abort(403, 'Accès non autorisé.');
        }

        $type = $request->get('type', 'all');
        $status = $request->get('status');
        $dateRange = $request->get('date_range');
        $serviceId = $request->get('service_id');

        // Déterminer quelles sections afficher
        $showServices = in_array($type, ['all', 'service']);
        $showEquipments = in_array($type, ['all', 'equipment']);
        $showUrgentSales = in_array($type, ['all', 'urgent_sale']);

        $serviceBookings = collect();
        $equipmentRentalRequests = collect();
        $urgentSales = collect();

        // Récupérer les réservations de services
        if ($showServices) {
            $query = $prestataire->bookings()->with(['client.user', 'service']);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($serviceId) {
                $query->where('service_id', $serviceId);
            }
            
            if ($dateRange) {
                switch ($dateRange) {
                    case 'today':
                        $query->whereDate('start_datetime', today());
                        break;
                    case 'week':
                        $query->whereBetween('start_datetime', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('start_datetime', now()->month)
                              ->whereYear('start_datetime', now()->year);
                        break;
                }
            }
            
            // Order by created_at to ensure proper mixing with other request types
            $allServiceBookings = $query->orderBy('created_at', 'desc')->get();
            
            // Group bookings by session for display purposes
            $serviceBookings = $this->groupBookingsBySessions($allServiceBookings);
        }

        // Récupérer les demandes de location d'équipements
        if ($showEquipments) {
            $query = $prestataire->equipmentRentalRequests()->with(['client.user', 'equipment']);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($dateRange) {
                switch ($dateRange) {
                    case 'today':
                        $query->whereDate('start_date', today());
                        break;
                    case 'week':
                        $query->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('start_date', now()->month)
                              ->whereYear('start_date', now()->year);
                        break;
                }
            }
            
            $equipmentRentalRequests = $query->orderBy('created_at', 'desc')->get();
        }

        // Récupérer les annonces
        if ($showUrgentSales) {
            $query = $prestataire->urgentSales()->with(['contacts.user.client']);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($dateRange) {
                switch ($dateRange) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
                        break;
                }
            }
            
            $urgentSales = $query->orderBy('created_at', 'desc')->get();
        }

        // Récupérer les services du prestataire pour le filtre
        $services = $prestataire->services()->get();

        // Créer une collection unifiée de toutes les demandes avec tri chronologique
        $allRequests = collect();
        
        // Ajouter les réservations de services
        foreach ($serviceBookings as $booking) {
            $booking->request_type = 'service';
            $allRequests->push($booking);
        }
        
        // Ajouter les demandes d'équipement
        foreach ($equipmentRentalRequests as $request) {
            $request->request_type = 'equipment';
            $allRequests->push($request);
        }
        
        // Ajouter les annonces
        foreach ($urgentSales as $sale) {
            $sale->request_type = 'urgent_sale';
            $allRequests->push($sale);
        }
        
        // Trier par date de création selon le paramètre de tri
        $sortOrder = $request->get('sort', 'desc'); // Par défaut : du plus récent au plus ancien
        if ($sortOrder === 'asc') {
            $allRequests = $allRequests->sortBy('created_at');
        } else {
            $allRequests = $allRequests->sortByDesc('created_at');
        }

        return view('prestataire.bookings.index', compact(
            'serviceBookings',
            'equipmentRentalRequests', 
            'urgentSales',
            'allRequests',
            'services',
            'showServices',
            'showEquipments',
            'showUrgentSales',
            'type',
            'status',
            'dateRange',
            'serviceId'
        ));
    }

    /**
     * Display the specified booking
     */
    public function show(Request $request, $id)
    {
        // Find the booking or return 404
        $booking = Booking::find($id);
        
        if (!$booking) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "La demande avec l'ID {$id} n'existe pas ou a été supprimée"
                ], 404);
            }
            
            abort(404, "La demande avec l'ID {$id} n'existe pas ou a été supprimée");
        }
        
        $user = Auth::user();
        
        // Vérifier que la réservation appartient au prestataire connecté
        if ($booking->prestataire_id !== $user->prestataire->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            
            abort(403, 'Accès non autorisé');
        }
        
        $booking->load(['service.category', 'client.user', 'timeSlot']);
        
        // For AJAX requests, return JSON data
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $booking->id,
                'title' => $booking->service->name ?? 'Service',
                'client_name' => ($booking->client && $booking->client->user) ? $booking->client->user->name : 'Client',
                'date' => $booking->start_datetime->format('d/m/Y à H:i'),
                'duration' => $booking->start_datetime->diffInHours($booking->end_datetime) . ' heures',
                'price' => number_format($booking->total_price, 2, ',', ' ') . ' €',
                'description' => $booking->client_notes ?? 'Aucune description',
                'status' => $booking->status,
                'status_label' => $this->getStatusLabel($booking->status)
            ]);
        }
        
        // Extract session ID from notes if it exists
        $sessionId = null;
        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
            $sessionId = $matches[1];
        }
        
        $relatedBookings = collect();
        
        if ($sessionId) {
            // Find all bookings with the same session ID
            $relatedBookings = Booking::where('client_id', $booking->client_id)
                ->where('prestataire_id', $booking->prestataire_id)
                ->where('service_id', $booking->service_id)
                ->where('id', '!=', $booking->id)
                ->where('client_notes', 'LIKE', '%[SESSION:' . $sessionId . ']%')
                ->with(['client.user', 'prestataire.user', 'service'])
                ->orderBy('start_datetime')
                ->get();
        }
        
        // Only consider it a multi-slot session if there are actually related bookings
        $isMultiSlotSession = $relatedBookings->count() > 0;
        
        if ($isMultiSlotSession) {
            // Combine all bookings (current + related) and sort by datetime
            $allBookings = collect([$booking])->concat($relatedBookings)
                ->sortBy('start_datetime')
                ->values();
                
            // Calculate total price for the booking session
            $totalSessionPrice = $allBookings->sum('total_price');
        } else {
            // Single booking - no session
            $allBookings = collect([$booking]);
            $totalSessionPrice = $booking->total_price;
        }
        
        return view('prestataire.bookings.show', compact('booking', 'relatedBookings', 'allBookings', 'totalSessionPrice', 'isMultiSlotSession'));
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmé',
            'rejected' => 'Refusé',
            'cancelled' => 'Annulé',
            'completed' => 'Terminé'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Accept a booking or entire session
     */
    public function accept(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        if ($booking->prestataire_id !== $user->prestataire->id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }
        
        if ($booking->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Cette réservation ne peut pas être acceptée.'], 400);
        }
        
        // Check if this is part of a multi-slot session
        $sessionId = null;
        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
            $sessionId = $matches[1];
        }
        
        $bookingsToUpdate = collect([$booking]);
        
        if ($sessionId) {
            // Find all bookings in the same session
            $sessionBookings = Booking::where('client_id', $booking->client_id)
                ->where('prestataire_id', $booking->prestataire_id)
                ->where('service_id', $booking->service_id)
                ->where('client_notes', 'LIKE', '%[SESSION:' . $sessionId . ']%')
                ->where('status', 'pending')
                ->get();
            
            $bookingsToUpdate = $sessionBookings;
        }
        
        // Update all bookings in the session
        $updatedCount = 0;
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            $bookingToUpdate->update(['status' => 'confirmed']);
            $updatedCount++;
        }
        
        // Send notification to client (only once for the session)
        $booking->load('client.user');
        Notification::send($booking->client->user, new BookingConfirmedNotification($booking));
        
        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux acceptée avec succès."
            : 'Réservation acceptée avec succès.';
            
        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Reject a booking or entire session
     */
    public function reject(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        if ($booking->prestataire_id !== $user->prestataire->id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }
        
        if ($booking->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Cette réservation ne peut pas être refusée.'], 400);
        }
        
        $rejectionReason = $request->get('rejection_reason');
        
        // Check if this is part of a multi-slot session
        $sessionId = null;
        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
            $sessionId = $matches[1];
        }
        
        $bookingsToUpdate = collect([$booking]);
        
        if ($sessionId) {
            // Find all bookings in the same session
            $sessionBookings = Booking::where('client_id', $booking->client_id)
                ->where('prestataire_id', $booking->prestataire_id)
                ->where('service_id', $booking->service_id)
                ->where('client_notes', 'LIKE', '%[SESSION:' . $sessionId . ']%')
                ->where('status', 'pending')
                ->get();
            
            $bookingsToUpdate = $sessionBookings;
        }
        
        // Update all bookings in the session
        $updatedCount = 0;
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            $bookingToUpdate->update([
                'status' => 'rejected',
                'rejection_reason' => $rejectionReason
            ]);
            $updatedCount++;
        }
        
        // Send notification to client (only once for the session)
        $booking->load('client.user');
        Notification::send($booking->client->user, new BookingRejectedNotification($booking, $rejectionReason));
        
        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux refusée."
            : 'Réservation refusée.';
            
        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Complete a booking or entire session
     */
    public function complete(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        if ($booking->prestataire_id !== $user->prestataire->id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }
        
        if ($booking->status !== 'confirmed') {
            return response()->json(['success' => false, 'message' => 'Seules les réservations confirmées peuvent être marquées comme terminées.'], 400);
        }
        
        // Check if this is part of a multi-slot session
        $sessionId = null;
        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
            $sessionId = $matches[1];
        }
        
        $bookingsToUpdate = collect([$booking]);
        
        if ($sessionId) {
            // Find all bookings in the same session
            $sessionBookings = Booking::where('client_id', $booking->client_id)
                ->where('prestataire_id', $booking->prestataire_id)
                ->where('service_id', $booking->service_id)
                ->where('client_notes', 'LIKE', '%[SESSION:' . $sessionId . ']%')
                ->where('status', 'confirmed')
                ->get();
            
            $bookingsToUpdate = $sessionBookings;
        }
        
        // Update all bookings in the session
        $updatedCount = 0;
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            $bookingToUpdate->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $updatedCount++;
        }
        
        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux marquée comme terminée!"
            : 'Réservation marquée comme terminée!';
            
        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Group bookings by session for display purposes
     */
    private function groupBookingsBySessions($bookings)
    {
        $grouped = collect();
        $processedSessions = [];
        
        foreach ($bookings as $booking) {
            // Extract session ID from notes if it exists
            $sessionId = null;
            if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
                $sessionId = $matches[1];
            }
            
            if ($sessionId && !in_array($sessionId, $processedSessions)) {
                // Find all bookings in this session
                $sessionBookings = $bookings->filter(function($b) use ($sessionId) {
                    return $b->client_notes && str_contains($b->client_notes, '[SESSION:' . $sessionId . ']');
                })->sortBy('start_datetime');
                
                if ($sessionBookings->count() > 1) {
                    // Create a grouped booking object
                    $firstBooking = $sessionBookings->first();
                    $firstBooking->is_multi_slot = true;
                    $firstBooking->session_bookings = $sessionBookings;
                    $firstBooking->session_id = $sessionId;
                    $firstBooking->total_slots = $sessionBookings->count();
                    $firstBooking->total_session_price = $sessionBookings->sum('total_price');
                    $firstBooking->session_duration = $sessionBookings->sum(function($b) {
                        return $b->start_datetime->diffInMinutes($b->end_datetime);
                    });
                    
                    $grouped->push($firstBooking);
                    $processedSessions[] = $sessionId;
                } else {
                    // Single booking in session (shouldn't happen, but handle gracefully)
                    $booking->is_multi_slot = false;
                    $grouped->push($booking);
                }
            } else if (!$sessionId) {
                // Single booking without session
                $booking->is_multi_slot = false;
                $grouped->push($booking);
            }
            // Skip bookings that are part of already processed sessions
        }
        
        return $grouped->sortByDesc('start_datetime');
    }
}