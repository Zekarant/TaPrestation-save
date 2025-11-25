<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\EquipmentRental;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\UrgentSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of bookings for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'client') {
            $allBookings = Booking::where('client_id', $user->client->id)
                ->with(['prestataire.user', 'service', 'timeSlot'])
                ->orderBy('start_datetime', 'desc')
                ->get();
        } elseif ($user->role === 'prestataire') {
            $allBookings = Booking::where('prestataire_id', $user->prestataire->id)
                ->with(['client.user', 'service', 'timeSlot'])
                ->orderBy('start_datetime', 'desc')
                ->get();
        } else {
            abort(403, 'Unauthorized');
        }

        // Group bookings by session
        $bookings = $this->groupBookingsBySessions($allBookings);
        
        // Paginate the grouped results
        $currentPage = request()->get('page', 1);
        $perPage = 10;
        $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
            $bookings->forPage($currentPage, $perPage),
            $bookings->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking
     */
    public function create(Service $service)
    {
        $prestataire = $service->prestataire;
        
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        
        // Use the specialized function for hourly services with duration
        if ($service->price_type === 'heure' && $service->quantity) {
            $availableSlots = generate_time_slots_for_service($service, $startDate, $endDate);
        } 
        // Use the specialized function for daily services with duration
        elseif ($service->price_type === 'jour' && $service->quantity) {
            $availableSlots = $this->generateDailySlots($service, $startDate, $endDate);
        } else {
            $availableSlots = generate_time_slots($prestataire, $startDate, $endDate);
        }

        return view('bookings.create', compact('service', 'prestataire', 'availableSlots'));
    }

    /**
     * Generate daily slots for services with daily pricing
     */
    private function generateDailySlots(Service $service, Carbon $startDate, Carbon $endDate)
    {
        $prestataire = $service->prestataire;
        $slots = [];
        $availabilities = $prestataire->availabilities()->where('is_active', true)->get();
        // Only consider confirmed bookings as "booked" - pending bookings should still allow new reservations
        $confirmedBookings = $prestataire->bookings()->where('status', 'confirmed')->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
        // Get all bookings (including pending) for display purposes
        $allBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();

        // For daily services with specific duration (number of days)
        $serviceDurationDays = $service->quantity ?? 1; // Default to 1 day if not set

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Utiliser dayOfWeek (0=dimanche, 1=lundi, etc.) au lieu de dayOfWeekIso
            $dayOfWeek = $date->dayOfWeek;
            $availability = $availabilities->firstWhere('day_of_week', $dayOfWeek);

            if ($availability) {
                $sTime = Carbon::parse($availability->start_time);
                $eTime = Carbon::parse($availability->end_time);
                $startTime = $date->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                $endTime = $date->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                
                // Calculate the end date for the service duration
                $serviceEndDate = $date->copy()->addDays($serviceDurationDays - 1);
                
                // Check if the service duration would extend beyond the end date
                if ($serviceEndDate->gt($endDate)) {
                    continue; // Skip this slot as it would extend beyond our date range
                }
                
                // Check if all days in the service duration are available
                $allDaysAvailable = true;
                $serviceDaysBooked = [];
                
                for ($i = 0; $i < $serviceDurationDays; $i++) {
                    $checkDate = $date->copy()->addDays($i);
                    $checkDayOfWeek = $checkDate->dayOfWeek;
                    $checkAvailability = $availabilities->firstWhere('day_of_week', $checkDayOfWeek);
                    
                    if (!$checkAvailability) {
                        $allDaysAvailable = false;
                        break;
                    }
                }
                
                if (!$allDaysAvailable) {
                    continue; // Skip this slot as not all days are available
                }
                
                // Check if any of the days in the service duration are already booked
                $isBooked = false;
                for ($i = 0; $i < $serviceDurationDays; $i++) {
                    $checkDate = $date->copy()->addDays($i);
                    $checkStart = $checkDate->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                    $checkEnd = $checkDate->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                    
                    // Check for confirmed bookings that overlap with this day
                    $dayBooked = $confirmedBookings->first(function ($booking) use ($checkStart, $checkEnd) {
                        return ($booking->start_datetime < $checkEnd) && ($booking->end_datetime > $checkStart);
                    });
                    
                    if ($dayBooked) {
                        $isBooked = true;
                        break;
                    }
                }
                
                // Check if any of the days have pending bookings (for display info)
                $hasPending = false;
                for ($i = 0; $i < $serviceDurationDays; $i++) {
                    $checkDate = $date->copy()->addDays($i);
                    $checkStart = $checkDate->copy()->hour($sTime->hour)->minute($sTime->minute)->second($sTime->second);
                    $checkEnd = $checkDate->copy()->hour($eTime->hour)->minute($eTime->minute)->second($eTime->second);
                    
                    // Check for any bookings that overlap with this day
                    $dayBooking = $allBookings->first(function ($booking) use ($checkStart, $checkEnd) {
                        return ($booking->start_datetime < $checkEnd) && ($booking->end_datetime > $checkStart);
                    });
                    
                    if ($dayBooking && $dayBooking->status === 'pending') {
                        $hasPending = true;
                        break;
                    }
                }
                
                // Include slot with its status
                $slots[] = [
                    'datetime' => $startTime->copy(),
                    'end_datetime' => $startTime->copy()->addDays($serviceDurationDays),
                    'duration' => $serviceDurationDays * 24 * 60, // Duration in minutes
                    'is_booked' => $isBooked,
                    'has_pending' => $hasPending,
                    'booking_status' => null, // We'll determine this when needed
                    'booking_id' => null, // We'll determine this when needed
                    'break_start_time' => $availability->break_start_time,
                    'break_end_time' => $availability->break_end_time,
                    'availability_start' => $availability->start_time,
                    'availability_end' => $availability->end_time,
                    'service_duration_days' => $serviceDurationDays
                ];
            }
        }

        return $slots;
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'prestataire_id' => 'required|exists:prestataires,id',
            'selected_slots' => 'required|array|min:1',
            'selected_slots.*' => 'required|date',
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        if ($user->role !== 'client') {
            abort(403, 'Seuls les clients peuvent créer des réservations.');
        }

        $service = Service::findOrFail($request->service_id);
        $prestataire = Prestataire::findOrFail($request->prestataire_id);
        $selectedSlots = collect($request->selected_slots)->map(fn($slot) => Carbon::parse($slot));

        // Sort slots by datetime to ensure proper validation
        $selectedSlots = $selectedSlots->sort();
        
        // Generate a unique session identifier for multi-slot bookings
        $sessionId = count($selectedSlots) > 1 ? uniqid('session_', true) : null;

        // Validate all selected slots
        $conflictingSlots = [];
        foreach ($selectedSlots as $start_datetime) {
            // Determine service duration based on price type
            if ($service->price_type === 'jour' && $service->quantity) {
                // For daily services, the duration is in days
                $serviceDurationDays = $service->quantity;
                $end_datetime = $start_datetime->copy()->addDays($serviceDurationDays);
            } elseif ($service->price_type === 'heure' && $service->quantity) {
                // For hourly services, the duration is in minutes based on the service quantity
                $serviceDurationMinutes = $service->quantity * 60;
                $end_datetime = $start_datetime->copy()->addMinutes($serviceDurationMinutes);
            } else {
                // Default to 60 minutes
                $serviceDuration = $service->duration ?? 60;
                $end_datetime = $start_datetime->copy()->addMinutes($serviceDuration);
            }
        
            // Check for confirmed bookings only - pending bookings don't block new reservations
            $isBooked = Booking::where('prestataire_id', $prestataire->id)
                ->where('status', 'confirmed') // Only confirmed bookings block new reservations
                ->where(function ($query) use ($start_datetime, $end_datetime) {
                    $query->where('start_datetime', '<', $end_datetime)
                          ->where('end_datetime', '>', $start_datetime);
                })->exists();

            if ($isBooked) {
                $conflictingSlots[] = $start_datetime->format('d/m/Y à H:i');
            }
        }

        if (!empty($conflictingSlots)) {
            $message = 'Les créneaux suivants sont déjà réservés : ' . implode(', ', $conflictingSlots);
            return redirect()->back()->with('error', $message);
        }

        // Create bookings for all selected slots
        $createdBookings = [];
        $creationTime = now(); // Use the same timestamp for all bookings in the session

        foreach ($selectedSlots as $start_datetime) {
            // Determine service duration based on price type
            if ($service->price_type === 'jour' && $service->quantity) {
                // For daily services, the duration is in days
                $serviceDurationDays = $service->quantity;
                $end_datetime = $start_datetime->copy()->addDays($serviceDurationDays);
            } elseif ($service->price_type === 'heure' && $service->quantity) {
                // For hourly services, the duration is in minutes based on the service quantity
                $serviceDurationMinutes = $service->quantity * 60;
                $end_datetime = $start_datetime->copy()->addMinutes($serviceDurationMinutes);
            } else {
                // Default to 60 minutes
                $serviceDuration = $service->duration ?? 60;
                $end_datetime = $start_datetime->copy()->addMinutes($serviceDuration);
            }
        
            // Calculate total price based on service type and quantity
            $totalPrice = $service->price;
            if (($service->price_type === 'heure' || $service->price_type === 'jour') && $service->quantity) {
                $totalPrice = $service->price * $service->quantity;
            }
        
            // Create notes with session identifier for multi-slot bookings
            $notes = $request->client_notes;
            if ($sessionId) {
                $notes = ($notes ? $notes . ' ' : '') . '[SESSION:' . $sessionId . ']';
            }
        
            $booking = Booking::create([
                'client_id' => $user->client->id,
                'prestataire_id' => $request->prestataire_id,
                'service_id' => $request->service_id,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'status' => 'pending',
                'total_price' => $totalPrice, // Corrected price calculation
                'client_notes' => $notes,
                'created_at' => $creationTime, // Same timestamp for all bookings
                'updated_at' => $creationTime,
            ]);

            // Load necessary relationships for notification
            $booking->load(['client.user', 'prestataire.user', 'service']);
            $createdBookings[] = $booking;
        }

        // Notify the prestataire about the new booking(s)
        foreach ($createdBookings as $booking) {
            $booking->prestataire->user->notify(new \App\Notifications\NewBookingNotification($booking));
        }

        // Redirect to the first booking's show page with success message
        $message = count($createdBookings) === 1 
            ? 'Votre réservation a été créée avec succès!' 
            : 'Vos ' . count($createdBookings) . ' réservations ont été créées avec succès!';

        return redirect()->route('bookings.show', $createdBookings[0])
            ->with('success', $message);
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

    /**
     * Display the specified booking
     */
    public function show(Booking $booking)
    {
        $user = Auth::user();
        
        // Check if user can view this booking
        if ($user->role === 'client' && $booking->client_id !== $user->client->id) {
            abort(403);
        }
        if ($user->role === 'prestataire' && $booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }

        $booking->load(['client.user', 'prestataire.user', 'service', 'timeSlot']);
        
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
        
        return view('bookings.show', compact('booking', 'relatedBookings', 'allBookings', 'totalSessionPrice', 'isMultiSlotSession'));
    }

    /**
     * Confirm a booking or entire session (prestataire only)
     */
    public function confirm(Booking $booking)
    {
        $user = Auth::user();
        
        if ($user->role !== 'prestataire' || $booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Cette réservation ne peut pas être confirmée.');
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
            $bookingToUpdate->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
            $updatedCount++;
        }

        $booking->client->user->notify(new \App\Notifications\BookingConfirmedNotification($booking));

        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux confirmée avec succès!"
            : 'Réservation confirmée avec succès!';
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Refuse a booking or entire session (prestataire only)
     */
    public function refuse(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        if ($user->role !== 'prestataire' || $booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Cette réservation ne peut pas être refusée.');
        }

        $request->validate([
            'refusal_reason' => 'nullable|string|max:500',
        ]);

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
                'status' => 'refused',
                'cancellation_reason' => $request->refusal_reason,
                'cancelled_at' => now(),
            ]);
            $updatedCount++;
        }

        $booking->client->user->notify(new \App\Notifications\BookingRefusedNotification($booking));

        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux refusée."
            : 'Réservation refusée.';
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Cancel a booking or entire session
     */
    public function cancel(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'client' && $booking->client_id !== $user->client->id) {
            abort(403);
        }
        if ($user->role === 'prestataire' && $booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->back()->with('error', 'Cette réservation ne peut pas être annulée.');
        }

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Check if this is part of a multi-slot session
        $sessionId = null;
        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
            $sessionId = $matches[1];
        }
        
        $bookingsToUpdate = collect([$booking]);
        
        if ($sessionId) {
            // Find all bookings in the same session that can be cancelled
            $sessionBookings = Booking::where('client_id', $booking->client_id)
                ->where('prestataire_id', $booking->prestataire_id)
                ->where('service_id', $booking->service_id)
                ->where('client_notes', 'LIKE', '%[SESSION:' . $sessionId . ']%')
                ->whereIn('status', ['pending', 'confirmed'])
                ->get();
            
            $bookingsToUpdate = $sessionBookings;
        }
        
        // Update all bookings in the session
        $updatedCount = 0;
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            $bookingToUpdate->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
            ]);
            
            // Release the time slot
            if ($bookingToUpdate->timeSlot) {
                $bookingToUpdate->timeSlot->releaseLock();
            }
            
            $updatedCount++;
        }

        // Send notification to the other party (only once for the session)
        if ($user->role === 'client') {
            // Send notification to prestataire
            $booking->prestataire->user->notify(new \App\Notifications\BookingCancelledNotification($booking));
        } else {
            // Send notification to client
            $booking->client->user->notify(new \App\Notifications\BookingCancelledNotification($booking));
        }

        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux annulée avec succès."
            : 'Réservation annulée avec succès.';
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Mark booking or entire session as completed (prestataire only)
     */
    public function complete(Booking $booking)
    {
        $user = Auth::user();
        
        if ($user->role !== 'prestataire' || $booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }

        if ($booking->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Seules les réservations confirmées peuvent être marquées comme terminées.');
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
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Display bookings for clients with filtering options
     */
    public function clientBookings(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'client') {
            abort(403, 'Accès non autorisé.');
        }

        $query = Booking::where('client_id', $user->client->id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'past':
                    $query->past();
                    break;
                case 'last_month':
                    $query->where('start_datetime', '>=', now()->subMonth())
                          ->where('start_datetime', '<=', now());
                    break;
                case 'last_3_months':
                    $query->where('start_datetime', '>=', now()->subMonths(3))
                          ->where('start_datetime', '<=', now());
                    break;
            }
        }
        
        $allBookings = $query->with(['prestataire.user', 'service', 'timeSlot'])
            ->orderBy('start_datetime', 'desc')
            ->get();

        // Group bookings by session
        $bookings = $this->groupBookingsBySessions($allBookings);
        
        // Paginate the grouped results
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
            $bookings->forPage($currentPage, $perPage),
            $bookings->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(), 
                'pageName' => 'page'
            ]
        );
        $bookings->appends($request->query());

        return view('client.bookings.index', compact('bookings'));
    }

    /**
     * Display bookings for prestataires with filtering options
     */
    public function prestataireBookings(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'prestataire') {
            abort(403, 'Accès non autorisé.');
        }

        $prestataire = $user->prestataire;
        
        // Récupérer les réservations de services
        $bookingsQuery = $prestataire->bookings()->with(['client.user', 'service']);
        if ($request->filled('status') && (!$request->filled('type') || $request->type === 'bookings')) {
            $bookingsQuery->where('status', $request->status);
        }
        
        // Récupérer les locations d'équipements confirmées
        $equipmentRentalsQuery = $prestataire->equipmentRentals()->with(['client.user', 'equipment']);
        if ($request->filled('status') && (!$request->filled('type') || $request->type === 'equipment')) {
            $equipmentRentalsQuery->where('status', $request->status);
        }
        
        // Récupérer les demandes de location d'équipements
        $equipmentRentalRequestsQuery = $prestataire->equipmentRentalRequests()->with(['client.user', 'equipment']);
        if ($request->filled('status') && (!$request->filled('type') || $request->type === 'equipment')) {
            $equipmentRentalRequestsQuery->where('status', $request->status);
        }
        
        // Récupérer les annonces
        $urgentSalesQuery = $prestataire->urgentSales()->with(['contacts.user']);
        if ($request->filled('status') && (!$request->filled('type') || $request->type === 'urgent_sales')) {
            $urgentSalesQuery->where('status', $request->status);
        }
        
        // Filtrer par type si spécifié
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'bookings':
                    $bookings = $bookingsQuery->latest()->paginate(10);
                    $equipmentRentals = collect();
                    $equipmentRentalRequests = collect();
                    $urgentSales = collect();
                    break;
                case 'equipment':
                    $equipmentRentals = $equipmentRentalsQuery->latest()->paginate(10);
                    $equipmentRentalRequests = $equipmentRentalRequestsQuery->latest()->get();
                    $bookings = collect();
                    $urgentSales = collect();
                    break;
                case 'urgent_sales':
                    $urgentSales = $urgentSalesQuery->latest()->paginate(10);
                    $bookings = collect();
                    $equipmentRentals = collect();
                    $equipmentRentalRequests = collect();
                    break;
                default:
                    $bookings = $bookingsQuery->latest()->take(5)->get();
                    $equipmentRentals = $equipmentRentalsQuery->latest()->take(5)->get();
                    $equipmentRentalRequests = $equipmentRentalRequestsQuery->latest()->take(5)->get();
                    $urgentSales = $urgentSalesQuery->latest()->take(5)->get();
            }
        } else {
            // Afficher tous les types avec pagination limitée
            $bookings = $bookingsQuery->latest()->take(5)->get();
            $equipmentRentals = $equipmentRentalsQuery->latest()->take(5)->get();
            $equipmentRentalRequests = $equipmentRentalRequestsQuery->latest()->take(5)->get();
            $urgentSales = $urgentSalesQuery->latest()->take(5)->get();
            
            // Si on filtre par statut sans type spécifique, vider les collections qui n'ont pas d'éléments correspondants
            if ($request->filled('status')) {
                if ($bookings->isEmpty()) {
                    $bookings = collect();
                }
                if ($equipmentRentals->isEmpty()) {
                    $equipmentRentals = collect();
                }
                if ($equipmentRentalRequests->isEmpty()) {
                    $equipmentRentalRequests = collect();
                }
                if ($urgentSales->isEmpty()) {
                    $urgentSales = collect();
                }
            }
        }

        return view('prestataire.bookings.index', compact('bookings', 'equipmentRentals', 'equipmentRentalRequests', 'urgentSales'));
    }
}