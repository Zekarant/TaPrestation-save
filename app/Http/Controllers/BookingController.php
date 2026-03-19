<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\EquipmentRental;
use App\Models\PaymentTransaction;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\UrgentSale;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        try {
            $user = Auth::user();
            
            if ($user->role === 'client') {
                if (!$user->client) {
                    return redirect()->route('client.dashboard')->with('error', 'Votre profil client n\'est pas configuré correctement. Veuillez contacter le support.');
                }
                $allBookings = Booking::where('client_id', $user->client->id)
                    ->with(['prestataire.user', 'service', 'timeSlot'])
                    ->orderBy('start_datetime', 'desc')
                    ->get();
            } elseif ($user->role === 'prestataire') {
                if (!$user->prestataire) {
                    return redirect()->route('prestataire.dashboard')->with('error', 'Votre profil prestataire n\'est pas configuré correctement. Veuillez contacter le support.');
                }
                $allBookings = Booking::where('prestataire_id', $user->prestataire->id)
                    ->with(['client.user', 'service', 'timeSlot'])
                    ->orderBy('start_datetime', 'desc')
                    ->get();
            } else {
                abort(403, 'Accès non autorisé. Vous n\'avez pas la permission d\'accéder à cette page.');
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
        } catch (\Throwable $e) {
            Log::error('Bookings index erreur: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Une erreur est survenue lors du chargement des réservations.');
        }
    }

    /**
     * Show the form for creating a new booking
     */
    public function create(Service $service)
    {
        $prestataire = $service->prestataire;
        
        if (!$prestataire || !$prestataire->user) {
            return redirect()->route('services.index')
                ->with('error', 'Ce service n\'est plus disponible.');
        }

        // Bloquer l'auto-réservation pour les prestataires
        $user = Auth::user();
        if ($user->role === 'prestataire' && $user->prestataire && $user->prestataire->id === $prestataire->id) {
            return redirect()->route('services.show', $service)
                ->with('error', 'Vous ne pouvez pas réserver vos propres services. Vous pouvez voir votre annonce, mais pas la réserver.');
        }
        
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        try {
            // Check if service has a specific duration (new fields or legacy quantity)
            $hasSpecificDuration = ($service->estimated_duration && $service->duration_unit) || 
                                   ($service->price_type === 'heure' && $service->quantity);
            
            // Use specialized function for daily services (multi-day)
            if ($service->duration_unit === 'days' || ($service->price_type === 'jour' && $service->quantity)) {
                $availableSlots = $this->generateDailySlots($service, $startDate, $endDate);
            }
            // Use specialized function for services with specific duration (hours/minutes)
            elseif ($hasSpecificDuration) {
                $availableSlots = generate_time_slots_for_service($service, $startDate, $endDate);
            } 
            // Default: standard slots
            else {
                $availableSlots = generate_time_slots($prestataire, $startDate, $endDate);
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération créneaux pour service #' . $service->id . ': ' . $e->getMessage());
            $availableSlots = [];
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
        // Consider pending + confirmed bookings as blocking to avoid overlap
        $blockingBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();
        // Get all bookings (including pending) for display purposes
        $allBookings = $prestataire->bookings()->whereIn('status', ['confirmed', 'pending'])->where('start_datetime', '<=', $endDate->endOfDay())->where('end_datetime', '>=', $startDate->startOfDay())->get();

        // For daily services: use estimated_duration if unit is 'days', otherwise fallback to quantity
        $serviceDurationDays = 1; // Default to 1 day
        if ($service->duration_unit === 'days' && $service->estimated_duration) {
            $serviceDurationDays = $service->estimated_duration;
        } elseif ($service->quantity) {
            $serviceDurationDays = $service->quantity;
        }

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
                    
                    // Check for blocking bookings that overlap with this day
                    $dayBooked = $blockingBookings->first(function ($booking) use ($checkStart, $checkEnd) {
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
     * If payment is required (deposit or full), redirects to payment first
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

        try {
            $user = Auth::user();
            
            // Les prestataires en mode client peuvent réserver, mais pas chez eux-mêmes
            if ($user->role === 'prestataire') {
                $prestataire = Prestataire::findOrFail($request->prestataire_id);
                if ($user->prestataire && $user->prestataire->id === $prestataire->id) {
                    return redirect()->back()->with('error', 'Vous ne pouvez pas effectuer une réservation auprès de vous-même.');
                }
            } elseif ($user->role !== 'client') {
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
                // Determine service duration - use new fields first, fallback to legacy
                if ($service->estimated_duration && $service->duration_unit) {
                    // New duration fields
                    switch ($service->duration_unit) {
                        case 'days':
                            $end_datetime = $start_datetime->copy()->addDays($service->estimated_duration);
                            break;
                        case 'hours':
                            $end_datetime = $start_datetime->copy()->addHours($service->estimated_duration);
                            break;
                        case 'minutes':
                            $end_datetime = $start_datetime->copy()->addMinutes($service->estimated_duration);
                            break;
                        default:
                            $end_datetime = $start_datetime->copy()->addHours(1);
                    }
                } elseif ($service->price_type === 'jour' && $service->quantity) {
                    // Legacy: For daily services, the duration is in days
                    $serviceDurationDays = $service->quantity;
                    $end_datetime = $start_datetime->copy()->addDays($serviceDurationDays);
                } elseif ($service->price_type === 'heure' && $service->quantity) {
                    // Legacy: For hourly services, the duration is in minutes based on the service quantity
                    $serviceDurationMinutes = $service->quantity * 60;
                    $end_datetime = $start_datetime->copy()->addMinutes($serviceDurationMinutes);
                } else {
                    // Default to 60 minutes
                    $serviceDuration = $service->duration ?? 60;
                    $end_datetime = $start_datetime->copy()->addMinutes($serviceDuration);
                }
            
                // Block slot if pending or confirmed booking overlaps
                $isBooked = Booking::where('prestataire_id', $prestataire->id)
                    ->whereIn('status', ['pending', 'confirmed'])
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

            // Check if payment is required before creating the booking
            $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                ? normalize_payment_requirement_for_mode($service->payment_requirement ?? 'none')
                : ($service->payment_requirement ?? 'none');
            
            if (in_array($paymentRequirement, ['deposit', 'full'])) {
                // Vérifier que le prestataire a configuré Stripe avant d'exiger un paiement en ligne
                if (empty($prestataire->stripe_account_id)) {
                    // Pas de Stripe configuré → créer la réservation sans paiement (espèces)
                    return $this->createBookingsFromRequest($request, $service, $prestataire, $selectedSlots, $sessionId);
                }

                // Calculate the amount to pay
                $totalPrice = $service->price * count($selectedSlots);
                if (($service->price_type === 'heure' || $service->price_type === 'jour') && $service->quantity) {
                    $totalPrice = $service->price * $service->quantity * count($selectedSlots);
                }
                
                $depositPercentage = (float) ($service->deposit_percentage ?? 0);
                $amountToPay = ($paymentRequirement === 'deposit' && $depositPercentage > 0)
                    ? round(($totalPrice * $depositPercentage) / 100, 2)
                    : $totalPrice;
                
                // Store booking data in session for after payment
                session([
                    'pending_booking' => [
                        'service_id' => $service->id,
                        'prestataire_id' => $prestataire->id,
                        'selected_slots' => $selectedSlots->map(fn($s) => $s->toIso8601String())->toArray(),
                        'client_notes' => $request->client_notes,
                        'session_id' => $sessionId,
                        'total_price' => $totalPrice,
                        'amount_to_pay' => $amountToPay,
                        'payment_type' => $paymentRequirement === 'deposit' ? 'deposit' : 'full',
                        'created_at' => now()->toIso8601String(),
                    ],
                ]);
                
                // Redirect to the pre-booking payment page
                return redirect()->route('bookings.prepayment')
                    ->with('info', 'Paiement requis pour confirmer votre réservation.');
            }

            // No payment required - create bookings directly
            return $this->createBookingsFromRequest($request, $service, $prestataire, $selectedSlots, $sessionId);
        } catch (\Throwable $e) {
            Log::error('Booking store error', [
                'user_id' => Auth::id(),
                'service_id' => $request->service_id,
                'prestataire_id' => $request->prestataire_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'La réservation a rencontré une erreur technique. Veuillez réessayer.');
        }
    }

    /**
     * Show pre-payment page for bookings that require upfront payment
     */
    public function showPrepayment()
    {
        $pendingBooking = session('pending_booking');
        
        if (!$pendingBooking) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Aucune réservation en attente de paiement.');
        }

        if (function_exists('booking_online_payment_enabled') && !booking_online_payment_enabled()) {
            session()->forget('pending_booking');
            return redirect()->route('client.dashboard')
                ->with('info', 'Le paiement en ligne des réservations est désactivé.');
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            session()->forget('pending_booking');
            return redirect()->route('client.dashboard')
                ->with('info', 'Le paiement en ligne est désactivé. Les réservations se règlent en espèces.');
        }
        
        // Check if session expired (30 minutes)
        $createdAt = Carbon::parse($pendingBooking['created_at']);
        if ($createdAt->diffInMinutes(now()) > 30) {
            session()->forget('pending_booking');
            return redirect()->route('client.dashboard')
                ->with('error', 'La session de réservation a expiré. Veuillez recommencer.');
        }
        
        $service = Service::with('prestataire.user', 'category')->findOrFail($pendingBooking['service_id']);
        $selectedSlots = collect($pendingBooking['selected_slots'])->map(fn($s) => Carbon::parse($s));

        // Vérifier que le prestataire a Stripe configuré
        if (empty($service->prestataire?->stripe_account_id)) {
            session()->forget('pending_booking');
            return redirect()->route('client.dashboard')
                ->with('error', 'Le paiement en ligne n\'est pas disponible pour ce prestataire.');
        }
        
        return view('bookings.prepayment', [
            'service' => $service,
            'prestataire' => $service->prestataire,
            'selectedSlots' => $pendingBooking['selected_slots'],
            'totalPrice' => $pendingBooking['total_price'],
            'amount' => $pendingBooking['amount_to_pay'],
            'paymentType' => $pendingBooking['payment_type'],
            'clientNotes' => $pendingBooking['client_notes'],
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    /**
     * Create a payment intent for pre-payment
     */
    public function createPrepaymentIntent(Request $request)
    {
        $pendingBooking = session('pending_booking');
        
        if (!$pendingBooking) {
            return response()->json(['error' => 'Aucune réservation en attente.'], 400);
        }

        if (function_exists('booking_online_payment_enabled') && !booking_online_payment_enabled()) {
            session()->forget('pending_booking');
            return response()->json(['error' => 'Le paiement en ligne des réservations est désactivé.'], 422);
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            session()->forget('pending_booking');
            return response()->json(['error' => 'Le paiement en ligne est désactivé.'], 422);
        }
        
        // Check if session expired
        $createdAt = Carbon::parse($pendingBooking['created_at']);
        if ($createdAt->diffInMinutes(now()) > 30) {
            session()->forget('pending_booking');
            return response()->json(['error' => 'La session a expiré. Veuillez recommencer.'], 400);
        }
        
        try {
            $service = Service::with('prestataire')->findOrFail($pendingBooking['service_id']);
            $amount = $pendingBooking['amount_to_pay'];
            $paymentType = $pendingBooking['payment_type'];
            
            $description = $paymentType === 'deposit' 
                ? "Acompte pour réservation - {$service->title}"
                : "Paiement complet pour réservation - {$service->title}";
            
            $metadata = [
                'type' => 'booking_prepayment',
                'service_id' => $service->id,
                'prestataire_id' => $service->prestataire_id,
                'payment_type' => $paymentType,
                'slots_count' => count($pendingBooking['selected_slots']),
            ];
            
            // Use StripePaymentService to create payment intent
            // IMPORTANT: Pour les prépaiements, on crée le Payment Intent sur le compte PLATEFORME
            // Les fonds seront transférés au prestataire après confirmation de la réservation
            $stripeService = app(\App\Services\StripePaymentService::class);
            
            // Vérifier que le prestataire a Stripe configuré
            $connectedAccountId = $service->prestataire?->stripe_account_id;
            if (empty($connectedAccountId)) {
                return response()->json(['error' => 'Le prestataire n\'a pas configuré le paiement en ligne.'], 422);
            }
            $metadata['connected_account_id'] = $connectedAccountId;
            
            $paymentIntent = $stripeService->createPaymentIntent(
                auth()->user(),
                $amount,
                $description,
                $metadata,
                null  // Pas de compte connecté - créé sur la plateforme
            );
            
            // Store the payment intent ID in session for verification later
            session(['pending_booking.payment_intent_id' => $paymentIntent->id]);
            
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $amount,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Booking prepayment intent creation failed', [
                'user_id' => Auth::id(),
                'pending_service_id' => $pendingBooking['service_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Impossible d\'initialiser le paiement pour le moment.',
            ], 500);
        }
    }

    /**
     * Process pre-payment and create bookings
     * Called after successful Stripe payment
     */
    public function processPrepayment(Request $request)
    {
        $pendingBooking = session('pending_booking');
        
        if (!$pendingBooking) {
            return response()->json(['error' => 'Aucune réservation en attente.'], 400);
        }

        if (function_exists('booking_online_payment_enabled') && !booking_online_payment_enabled()) {
            session()->forget('pending_booking');
            return response()->json(['error' => 'Le paiement en ligne des réservations est désactivé.'], 422);
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            session()->forget('pending_booking');
            return response()->json(['error' => 'Le paiement en ligne est désactivé.'], 422);
        }
        
        // Verify the payment intent
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);
        
        $expectedPaymentIntentId = $pendingBooking['payment_intent_id'] ?? null;
        if (!$expectedPaymentIntentId || $expectedPaymentIntentId !== $request->payment_intent_id) {
            return response()->json(['error' => 'ID de paiement invalide.'], 400);
        }
        
        try {
            // Verify payment with Stripe
            $stripeService = app(\App\Services\StripePaymentService::class);
            $service = Service::findOrFail($pendingBooking['service_id']);
            $prestataire = Prestataire::findOrFail($pendingBooking['prestataire_id']);
            $connectedAccountId = $prestataire?->stripe_account_id;

            // Les prépaiements sont créés sur le compte plateforme (escrow),
            // avec fallback legacy sur compte connecté si nécessaire.
            try {
                $paymentIntent = $stripeService->retrievePaymentIntent($request->payment_intent_id, null);
            } catch (\Throwable $platformError) {
                if (empty($connectedAccountId)) {
                    throw $platformError;
                }
                $paymentIntent = $stripeService->retrievePaymentIntent($request->payment_intent_id, $connectedAccountId);
            }
            
            if ($paymentIntent->status !== 'succeeded') {
                return response()->json(['error' => 'Le paiement n\'a pas été confirmé.'], 400);
            }
            
            // $service and $prestataire already loaded above
            $selectedSlots = collect($pendingBooking['selected_slots'])->map(fn($s) => Carbon::parse($s));
            $sessionId = $pendingBooking['session_id'];
            
            // Create a fake request object with the stored data
            $fakeRequest = new Request([
                'service_id' => $pendingBooking['service_id'],
                'prestataire_id' => $pendingBooking['prestataire_id'],
                'client_notes' => $pendingBooking['client_notes'],
            ]);
            
            $paymentStatus = $pendingBooking['payment_type'] === 'full' ? 'paid' : 'deposit_paid';
            
            // Create the bookings
            $bookingsResult = $this->createBookingsFromRequestWithTransaction(
                $fakeRequest, 
                $service, 
                $prestataire, 
                $selectedSlots, 
                $sessionId,
                $paymentStatus,
                $pendingBooking['amount_to_pay'],
                $request->payment_intent_id,
                $pendingBooking['payment_type'],
                $paymentIntent
            );
            
            // Clear the pending booking from session
            session()->forget('pending_booking');
            
            return response()->json([
                'success' => true,
                'redirect' => route('bookings.show', $bookingsResult['first_booking']),
                'message' => $bookingsResult['message'],
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Prepayment processing failed', [
                'user_id' => Auth::id(),
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erreur lors du traitement du paiement.',
            ], 500);
        }
    }
    
    /**
     * Create bookings with associated payment transaction
     */
    private function createBookingsFromRequestWithTransaction(
        Request $request, 
        Service $service, 
        Prestataire $prestataire, 
        $selectedSlots, 
        ?string $sessionId,
        string $paymentStatus,
        float $amountPaid,
        string $paymentIntentId,
        string $paymentType,
        $paymentIntent = null
    ) {
        $user = Auth::user();
        $createdBookings = [];
        $creationTime = now();
        
        // Si l'utilisateur n'a pas de profil client, en créer un automatiquement
        // Cela permet aux prestataires de faire des réservations en mode client
        if (!$user->client) {
            $client = \App\Models\Client::create([
                'user_id' => $user->id,
                'phone' => $user->phone ?? null,
                'address' => $user->address ?? null,
            ]);
            // Recharger la relation
            $user->load('client');
        }

        foreach ($selectedSlots as $start_datetime) {
            // Determine service duration
            if ($service->estimated_duration && $service->duration_unit) {
                switch ($service->duration_unit) {
                    case 'days':
                        $end_datetime = $start_datetime->copy()->addDays($service->estimated_duration);
                        break;
                    case 'hours':
                        $end_datetime = $start_datetime->copy()->addHours($service->estimated_duration);
                        break;
                    case 'minutes':
                        $end_datetime = $start_datetime->copy()->addMinutes($service->estimated_duration);
                        break;
                    default:
                        $end_datetime = $start_datetime->copy()->addHours(1);
                }
            } elseif ($service->price_type === 'jour' && $service->quantity) {
                $serviceDurationDays = $service->quantity;
                $end_datetime = $start_datetime->copy()->addDays($serviceDurationDays);
            } elseif ($service->price_type === 'heure' && $service->quantity) {
                $serviceDurationMinutes = $service->quantity * 60;
                $end_datetime = $start_datetime->copy()->addMinutes($serviceDurationMinutes);
            } else {
                $serviceDuration = $service->duration ?? 60;
                $end_datetime = $start_datetime->copy()->addMinutes($serviceDuration);
            }
        
            // Calculate total price
            $totalPrice = $service->price;
            if (($service->price_type === 'heure' || $service->price_type === 'jour') && $service->quantity) {
                $totalPrice = $service->price * $service->quantity;
            }

            // Calculate deposit amount
            $depositAmount = 0;
            if ($paymentType === 'deposit') {
                $depositPct = (float) ($service->deposit_percentage ?? 0);
                if ($depositPct > 0 && $totalPrice > 0) {
                    $depositAmount = round(($totalPrice * $depositPct) / 100, 2);
                }
            }
        
            $notes = $request->client_notes;
            if ($sessionId) {
                $notes = ($notes ? $notes . ' ' : '') . '[SESSION:' . $sessionId . ']';
            }
        
            $bookingData = [
                'client_id' => $user->client->id,
                'prestataire_id' => $prestataire->id,
                'service_id' => $service->id,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'status' => 'pending',
                'total_price' => $totalPrice,
                'client_notes' => $notes,
            ];

            if (Schema::hasColumn('bookings', 'deposit_amount')) {
                $bookingData['deposit_amount'] = $depositAmount;
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $bookingData['payment_status'] = $paymentStatus;
            }

            $booking = Booking::create($bookingData);

            $booking->load(['client.user', 'prestataire.user', 'service']);
            $createdBookings[] = $booking;
        }
        
        // Create payment transaction + escrow entry on the first booking (legacy prepayment flow).
        $firstBooking = $createdBookings[0];
        $transactionType = $paymentType === 'deposit' ? 'deposit' : 'payment';

        try {
            $stripeService = app(\App\Services\StripePaymentService::class);

            if (!$paymentIntent) {
                try {
                    $paymentIntent = $stripeService->retrievePaymentIntent($paymentIntentId, null);
                } catch (\Throwable $platformError) {
                    if (empty($prestataire->stripe_account_id)) {
                        throw $platformError;
                    }
                    $paymentIntent = $stripeService->retrievePaymentIntent($paymentIntentId, $prestataire->stripe_account_id);
                }
            }

            $transaction = $stripeService->recordPayment($paymentIntent, $firstBooking->id, null, $transactionType);

            $existingMeta = is_array($transaction->metadata) ? $transaction->metadata : [];
            $transaction->metadata = array_merge($existingMeta, [
                'payment_intent_id' => $paymentIntentId,
                'prepayment' => true,
                'slots_count' => count($createdBookings),
                'session_id' => $sessionId,
            ]);
            $transaction->transaction_id = $transaction->transaction_id ?: $paymentIntentId;
            $transaction->description = $transaction->description ?: (
                $paymentType === 'deposit'
                    ? "Acompte pour réservation #{$firstBooking->id}"
                    : "Paiement complet pour réservation #{$firstBooking->id}"
            );
            $transaction->save();

            if (in_array($paymentStatus, ['deposit_paid', 'paid'], true)) {
                $existingEscrow = DB::table('escrow_transactions')
                    ->where('escrowable_type', Booking::class)
                    ->where('escrowable_id', $firstBooking->id)
                    ->whereIn('status', ['pending', 'held'])
                    ->first();

                if (!$existingEscrow) {
                    $escrowService = app(\App\Services\EscrowService::class);
                    $escrowService->createEscrow(
                        $firstBooking,
                        auth()->id(),
                        (int) $prestataire->id,
                        (float) ($transaction->amount ?? $amountPaid),
                        0,
                        $paymentIntentId,
                        null,
                        [
                            'payment_type' => $paymentType,
                            'transaction_id' => $transaction->id,
                            'booking_number' => $firstBooking->booking_number,
                            'prepayment' => true,
                            'session_id' => $sessionId,
                            'slots_count' => count($createdBookings),
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error('PaymentTransaction create failed after booking prepayment', [
                'booking_id' => $firstBooking->id ?? null,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
        }

        // Notify the prestataire about the new booking(s)
        foreach ($createdBookings as $booking) {
            try {
                $prestataireUser = $booking->prestataire?->user;
                if ($prestataireUser) {
                    $prestataireUser->notify(new \App\Notifications\NewBookingNotification($booking));
                } else {
                    Log::warning('New booking notification skipped: missing prestataire user', [
                        'booking_id' => $booking->id,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('New booking notify failed (database/mail notification)', [
                    'booking_id' => $booking->id,
                    'message' => $e->getMessage(),
                ]);
            }

            try {
                NotificationHelper::sendNewBookingEmail($booking);
            } catch (\Throwable $e) {
                Log::error('New booking email helper failed', [
                    'booking_id' => $booking->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $message = count($createdBookings) === 1 
            ? 'Votre réservation a été créée et payée avec succès!' 
            : 'Vos ' . count($createdBookings) . ' réservations ont été créées et payées avec succès!';

        return [
            'first_booking' => $firstBooking,
            'bookings' => $createdBookings,
            'message' => $message,
        ];
    }

    /**
     * Create bookings from request data
     * Extracted to be reusable for both direct and post-payment booking creation
     */
    private function createBookingsFromRequest(
        Request $request, 
        Service $service, 
        Prestataire $prestataire, 
        $selectedSlots, 
        ?string $sessionId,
        string $paymentStatus = 'pending'
    ) {
        $user = Auth::user();
        $createdBookings = [];
        $creationTime = now(); // Use the same timestamp for all bookings in the session
        
        // Si l'utilisateur n'a pas de profil client, en créer un automatiquement
        // Cela permet aux prestataires de faire des réservations en mode client
        if (!$user->client) {
            $client = \App\Models\Client::create([
                'user_id' => $user->id,
                'phone' => $user->phone ?? null,
                'address' => $user->address ?? null,
            ]);
            // Recharger la relation
            $user->load('client');
        }

        foreach ($selectedSlots as $start_datetime) {
            // Determine service duration - use new fields first, fallback to legacy
            if ($service->estimated_duration && $service->duration_unit) {
                // New duration fields
                switch ($service->duration_unit) {
                    case 'days':
                        $end_datetime = $start_datetime->copy()->addDays($service->estimated_duration);
                        break;
                    case 'hours':
                        $end_datetime = $start_datetime->copy()->addHours($service->estimated_duration);
                        break;
                    case 'minutes':
                        $end_datetime = $start_datetime->copy()->addMinutes($service->estimated_duration);
                        break;
                    default:
                        $end_datetime = $start_datetime->copy()->addHours(1);
                }
            } elseif ($service->price_type === 'jour' && $service->quantity) {
                // Legacy: For daily services, the duration is in days
                $serviceDurationDays = $service->quantity;
                $end_datetime = $start_datetime->copy()->addDays($serviceDurationDays);
            } elseif ($service->price_type === 'heure' && $service->quantity) {
                // Legacy: For hourly services, the duration is in minutes based on the service quantity
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

            // Calculate deposit amount (sync with prestataire payment criteria)
            $depositAmount = 0;
            $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                ? normalize_payment_requirement_for_mode($service->payment_requirement ?? 'none')
                : ($service->payment_requirement ?? 'none');

            if ($paymentRequirement === 'deposit') {
                $depositPct = (float) ($service->deposit_percentage ?? 0);
                if ($depositPct > 0 && $totalPrice > 0) {
                    $depositAmount = round(($totalPrice * $depositPct) / 100, 2);
                }
            }
        
            // Create notes with session identifier for multi-slot bookings
            $notes = $request->client_notes;
            if ($sessionId) {
                $notes = ($notes ? $notes . ' ' : '') . '[SESSION:' . $sessionId . ']';
            }
        
            $bookingData = [
                'client_id' => $user->client->id,
                'prestataire_id' => $prestataire->id,
                'service_id' => $service->id,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'status' => 'pending',
                'total_price' => $totalPrice,
                'client_notes' => $notes,
            ];

            if (Schema::hasColumn('bookings', 'deposit_amount')) {
                $bookingData['deposit_amount'] = $depositAmount;
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $bookingData['payment_status'] = $paymentStatus;
            }
        
            $booking = Booking::create($bookingData);

            // Load necessary relationships for notification
            $booking->load(['client.user', 'prestataire.user', 'service']);
            $createdBookings[] = $booking;
        }

        // Notify the prestataire about the new booking(s)
        foreach ($createdBookings as $booking) {
            try {
                $prestataireUser = $booking->prestataire?->user;
                if ($prestataireUser) {
                    $prestataireUser->notify(new \App\Notifications\NewBookingNotification($booking));
                } else {
                    Log::warning('New booking notification skipped: missing prestataire user', [
                        'booking_id' => $booking->id,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('New booking notify failed (database/mail notification)', [
                    'booking_id' => $booking->id,
                    'message' => $e->getMessage(),
                ]);
            }

            try {
                NotificationHelper::sendNewBookingEmail($booking);
            } catch (\Throwable $e) {
                Log::error('New booking email helper failed', [
                    'booking_id' => $booking->id,
                    'message' => $e->getMessage(),
                ]);
            }
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
        if ($user->role === 'client') {
            if (!$user->client || $booking->client_id !== $user->client->id) {
                abort(403);
            }
        }
        if ($user->role === 'prestataire') {
            if (!$user->prestataire || $booking->prestataire_id !== $user->prestataire->id) {
                abort(403);
            }
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
        
        if ($user->role !== 'prestataire' || !$user->prestataire || $booking->prestataire_id !== $user->prestataire->id) {
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

        // Notification base de données
        $booking->client->user->notify(new \App\Notifications\BookingConfirmedNotification($booking));
        // Email direct
        NotificationHelper::sendBookingConfirmedEmail($booking);

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
        
        if ($user->role !== 'prestataire' || !$user->prestataire || $booking->prestataire_id !== $user->prestataire->id) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Cette réservation ne peut pas être refusée.');
        }

        $validated = $request->validate([
            'refusal_reason' => 'nullable|string|max:500',
        ]);
        $rejectionReason = trim((string) ($validated['refusal_reason'] ?? ''));
        if ($rejectionReason === '') {
            $rejectionReason = 'Refusée par le prestataire';
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

        $refundSummary = [
            'ok' => true,
            'refund_total' => 0.0,
            'partial_failure' => false,
        ];
        $paymentStatusByBooking = [];

        foreach ($bookingsToUpdate as $bookingToRefund) {
            $result = $this->processBookingCancellationRefund($bookingToRefund, 'prestataire', $rejectionReason);
            if (!($result['ok'] ?? false)) {
                return redirect()->back()->with(
                    'error',
                    'Refus impossible pour le moment. Le remboursement n\'a pas pu être finalisé. Réessayez.'
                );
            }

            $refundSummary['refund_total'] += max(0, (float) ($result['refund_total'] ?? 0));
            $refundSummary['partial_failure'] = $refundSummary['partial_failure'] || (bool) ($result['partial_failure'] ?? false);
            if (array_key_exists('payment_status', $result)) {
                $paymentStatusByBooking[$bookingToRefund->id] = $result['payment_status'];
            }
        }
        
        // Update all bookings in the session
        $hasPaymentStatusColumn = Schema::hasColumn('bookings', 'payment_status');
        $updatedCount = 0;
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            $updates = [
                'status' => 'refused',
                'cancellation_reason' => $rejectionReason,
                'cancelled_at' => now(),
            ];
            if ($hasPaymentStatusColumn && isset($paymentStatusByBooking[$bookingToUpdate->id])) {
                $updates['payment_status'] = $paymentStatusByBooking[$bookingToUpdate->id];
            }
            $bookingToUpdate->update($updates);
            $updatedCount++;
        }

        // Notification base de données
        $booking->client->user->notify(new \App\Notifications\BookingRefusedNotification($booking));
        // Email direct
        NotificationHelper::sendBookingRefusedEmail($booking, $rejectionReason);

        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux refusée."
            : 'Réservation refusée.';

        if ($refundSummary['refund_total'] > 0) {
            $message .= ' Remboursement déclenché: ' . number_format((float) $refundSummary['refund_total'], 2) . ' €.';
        }

        if ($refundSummary['partial_failure']) {
            $message .= ' Attention: une partie du remboursement est en attente de reprise automatique.';
        }
             
        return redirect()->back()->with('success', $message);
    }

    /**
     * Cancel a booking or entire session
     */
    public function cancel(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'client') {
            if (!$user->client || $booking->client_id !== $user->client->id) {
                abort(403);
            }
        }
        if ($user->role === 'prestataire') {
            if (!$user->prestataire || $booking->prestataire_id !== $user->prestataire->id) {
                abort(403);
            }
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->back()->with('error', 'Cette réservation ne peut pas être annulée.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
            'cancel_single' => 'nullable|boolean',
        ]);

        // Check if user wants to cancel only this single slot
        $cancelSingleOnly = $request->boolean('cancel_single', false);
        $cancellationReason = trim((string) ($validated['cancellation_reason'] ?? ''));
        if ($cancellationReason === '') {
            $cancellationReason = $user->role === 'prestataire'
                ? 'Annulée par le prestataire'
                : 'Annulée par le client';
        }

        // Check if this is part of a multi-slot session
        $sessionId = null;
        if ($booking->client_notes && preg_match('/\[SESSION:([^\]]+)\]/', $booking->client_notes, $matches)) {
            $sessionId = $matches[1];
        }
        
        $bookingsToUpdate = collect([$booking]);
        
        // Only find related bookings if not cancelling single slot
        if ($sessionId && !$cancelSingleOnly) {
            // Find all bookings in the same session that can be cancelled
            $sessionBookings = Booking::where('client_id', $booking->client_id)
                ->where('prestataire_id', $booking->prestataire_id)
                ->where('service_id', $booking->service_id)
                ->where('client_notes', 'LIKE', '%[SESSION:' . $sessionId . ']%')
                ->whereIn('status', ['pending', 'confirmed'])
                ->get();
            
            $bookingsToUpdate = $sessionBookings;
        }

        $cancelledBy = $user->role === 'prestataire' ? 'prestataire' : 'client';
        $refundSummary = [
            'ok' => true,
            'refund_total' => 0.0,
            'partial_failure' => false,
        ];
        $paymentStatusByBooking = [];

        foreach ($bookingsToUpdate as $bookingToRefund) {
            $result = $this->processBookingCancellationRefund($bookingToRefund, $cancelledBy, $cancellationReason);
            if (!($result['ok'] ?? false)) {
                return redirect()->back()->with(
                    'error',
                    'Annulation impossible pour le moment. Le remboursement n\'a pas pu être finalisé. Réessayez.'
                );
            }

            $refundSummary['refund_total'] += max(0, (float) ($result['refund_total'] ?? 0));
            $refundSummary['partial_failure'] = $refundSummary['partial_failure'] || (bool) ($result['partial_failure'] ?? false);

            if (array_key_exists('payment_status', $result)) {
                $paymentStatusByBooking[$bookingToRefund->id] = $result['payment_status'];
            }
        }
        
        // Update all bookings in the session
        $hasPaymentStatusColumn = Schema::hasColumn('bookings', 'payment_status');
        $updatedCount = 0;
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            $updates = [
                'status' => 'cancelled',
                'cancellation_reason' => $cancellationReason,
                'cancelled_at' => now(),
            ];

            if ($hasPaymentStatusColumn && isset($paymentStatusByBooking[$bookingToUpdate->id])) {
                $updates['payment_status'] = $paymentStatusByBooking[$bookingToUpdate->id];
            }

            $bookingToUpdate->update($updates);
            
            // Release the time slot
            if ($bookingToUpdate->timeSlot) {
                $bookingToUpdate->timeSlot->releaseLock();
            }
            
            $updatedCount++;
        }

        // Log for debugging
        \Log::info('BookingController@cancel: Sending cancellation notification', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'is_client_role' => $user->role === 'client',
        ]);

        // Send notification to the other party (only once for the session)
        if ($user->role === 'client') {
            // Send notification to prestataire
            try {
                if ($booking->prestataire && $booking->prestataire->user) {
                    $booking->prestataire->user->notify(new \App\Notifications\BookingCancelledNotification($booking));
                    NotificationHelper::sendBookingCancelledEmail($booking, $booking->prestataire->user, 'le client');
                    // Note: Push notifications are handled by SendOneSignalPush listener
                } else {
                    \Log::error('BookingController@cancel: Cannot notify prestataire - missing prestataire or user', [
                        'booking_id' => $booking->id,
                        'prestataire_id' => $booking->prestataire_id,
                        'has_prestataire' => (bool) $booking->prestataire,
                        'has_user' => $booking->prestataire ? (bool) $booking->prestataire->user : false,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('BookingController@cancel: Failed to notify prestataire', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            // Send notification to client
            try {
                if ($booking->client && $booking->client->user) {
                    $booking->client->user->notify(new \App\Notifications\BookingCancelledNotification($booking));
                    NotificationHelper::sendBookingCancelledEmail($booking, $booking->client->user, 'le prestataire');
                    // Note: Push notifications are handled by SendOneSignalPush listener
                } else {
                    \Log::error('BookingController@cancel: Cannot notify client - missing client or user', [
                        'booking_id' => $booking->id,
                        'client_id' => $booking->client_id,
                        'has_client' => (bool) $booking->client,
                        'has_user' => $booking->client ? (bool) $booking->client->user : false,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('BookingController@cancel: Failed to notify client', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($cancelSingleOnly) {
            $message = 'Créneau annulé avec succès.';
        } elseif ($sessionId && $updatedCount > 1) {
            $message = "Session de {$updatedCount} créneaux annulée avec succès.";
        } else {
            $message = 'Réservation annulée avec succès.';
        }

        if ($refundSummary['refund_total'] > 0) {
            $message .= ' Remboursement déclenché: ' . number_format((float) $refundSummary['refund_total'], 2) . ' €.';
        }

        if ($refundSummary['partial_failure']) {
            $message .= ' Attention: une partie du remboursement est en attente de reprise automatique.';
        }
            
        return redirect()->back()->with('success', $message);
    }

    private function findRefundableEscrowsForBooking(Booking $booking)
    {
        if (!DB::getSchemaBuilder()->hasTable('escrow_transactions')) {
            return collect();
        }

        return DB::table('escrow_transactions')
            ->where('escrowable_type', Booking::class)
            ->where('escrowable_id', (int) $booking->id)
            ->whereIn('status', ['pending', 'held'])
            ->orderBy('id')
            ->get();
    }

    private function markBookingTransactionsRefunded(Booking $booking, float $refundAmount, string $reason, string $cancelledBy): void
    {
        if ($refundAmount <= 0) {
            return;
        }

        $transactions = PaymentTransaction::query()
            ->where('booking_id', (int) $booking->id)
            ->whereIn('status', ['paid', 'held', 'released', 'completed', 'partially_refunded'])
            ->whereIn('type', ['payment', 'deposit', 'balance'])
            ->orderByDesc('id')
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        $totalCaptured = (float) $transactions->sum(function ($tx) {
            return (float) ($tx->amount ?? 0);
        });
        $targetStatus = ($refundAmount + 0.01 >= $totalCaptured) ? 'refunded' : 'partially_refunded';

        foreach ($transactions as $transaction) {
            try {
                $metaRaw = $transaction->metadata;
                if (is_array($metaRaw)) {
                    $meta = $metaRaw;
                } elseif (is_string($metaRaw)) {
                    $decoded = json_decode($metaRaw, true);
                    $meta = is_array($decoded) ? $decoded : [];
                } elseif (is_object($metaRaw)) {
                    $meta = (array) $metaRaw;
                } else {
                    $meta = [];
                }

                $meta['cancelled_by'] = $cancelledBy;
                $meta['cancellation_refund_amount'] = round($refundAmount, 2);
                $meta['cancellation_refund_reason'] = $reason;
                $meta['cancellation_refunded_at'] = now()->toIso8601String();

                $transaction->status = $targetStatus;
                $transaction->refunded_at = now();
                $transaction->refund_reason = $reason;
                $transaction->metadata = $meta;
                $transaction->save();
            } catch (\Throwable $e) {
                Log::warning('Unable to update booking payment transaction refund status after cancellation', [
                    'booking_id' => $booking->id,
                    'transaction_id' => $transaction->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function processBookingCancellationRefund(Booking $booking, string $cancelledBy, string $reason): array
    {
        $summary = [
            'ok' => true,
            'refund_total' => 0.0,
            'partial_failure' => false,
            'payment_status' => $booking->payment_status ?? null,
        ];

        $currentPaymentStatus = (string) ($booking->payment_status ?? 'pending');
        if (!in_array($currentPaymentStatus, ['deposit_paid', 'paid'], true)) {
            return $summary;
        }

        $escrows = $this->findRefundableEscrowsForBooking($booking);
        if ($escrows->isNotEmpty()) {
            $successCount = 0;
            $failureCount = 0;

            foreach ($escrows as $escrow) {
                try {
                    $result = app(\App\Services\EscrowService::class)->cancelWithRefund((int) $escrow->id, $cancelledBy);
                    if (!($result['success'] ?? false)) {
                        $failureCount++;
                        Log::warning('Booking cancellation refund failed on escrow', [
                            'booking_id' => $booking->id,
                            'escrow_id' => $escrow->id ?? null,
                            'result' => $result,
                        ]);
                        continue;
                    }

                    $successCount++;
                    $summary['refund_total'] += max(0, (float) ($result['refund_amount'] ?? 0));
                } catch (\Throwable $e) {
                    $failureCount++;
                    Log::warning('Booking cancellation refund exception', [
                        'booking_id' => $booking->id,
                        'escrow_id' => $escrow->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($successCount === 0 && $failureCount > 0) {
                $summary['ok'] = false;
                return $summary;
            }

            $summary['partial_failure'] = $failureCount > 0;
            if ($summary['refund_total'] > 0) {
                $this->markBookingTransactionsRefunded($booking, (float) $summary['refund_total'], $reason, $cancelledBy);
                $summary['payment_status'] = 'refunded';
            }

            return $summary;
        }

        $transactions = PaymentTransaction::query()
            ->where('booking_id', (int) $booking->id)
            ->whereIn('status', ['paid', 'held', 'released', 'completed', 'partially_refunded'])
            ->whereIn('type', ['payment', 'deposit', 'balance'])
            ->orderByDesc('id')
            ->get();

        if ($transactions->isEmpty()) {
            return $summary;
        }

        $stripeService = app(\App\Services\StripePaymentService::class);
        $successCount = 0;
        $failureCount = 0;

        foreach ($transactions as $transaction) {
            $provider = strtolower(trim((string) ($transaction->provider ?? 'stripe')));
            if ($provider !== '' && $provider !== 'stripe') {
                continue;
            }

            $hasChargeOrPi = trim((string) ($transaction->stripe_charge_id ?? '')) !== ''
                || trim((string) ($transaction->stripe_payment_intent_id ?? '')) !== '';

            if (!$hasChargeOrPi) {
                $failureCount++;
                Log::warning('Booking cancellation refund skipped: no Stripe identifiers on transaction', [
                    'booking_id' => $booking->id,
                    'transaction_id' => $transaction->id,
                ]);
                continue;
            }

            try {
                $stripeService->refundPayment($transaction, null, $reason);
                $summary['refund_total'] += max(0, (float) ($transaction->amount ?? 0));
                $successCount++;
            } catch (\Throwable $e) {
                $failureCount++;
                Log::warning('Booking cancellation direct refund failed', [
                    'booking_id' => $booking->id,
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($successCount === 0 && $failureCount > 0) {
            $summary['ok'] = false;
            return $summary;
        }

        $summary['partial_failure'] = $failureCount > 0;
        if ($summary['refund_total'] > 0) {
            $this->markBookingTransactionsRefunded($booking, (float) $summary['refund_total'], $reason, $cancelledBy);
            $summary['payment_status'] = 'refunded';
        }

        return $summary;
    }

    /**
     * Mark booking or entire session as completed (prestataire only)
     */
    public function complete(Booking $booking)
    {
        $user = Auth::user();
        
        if ($user->role !== 'prestataire' || !$user->prestataire || $booking->prestataire_id !== $user->prestataire->id) {
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
        
        // Autoriser les clients ET les prestataires (en mode client)
        if (!in_array($user->role, ['client', 'prestataire'])) {
            abort(403, 'Accès non autorisé.');
        }

        // Pour les prestataires en mode client, ils n'ont pas de client->id
        // On cherche les bookings où ils ont réservé en tant que user
        $clientId = $user->client ? $user->client->id : null;
        
        // Si pas de client_id (prestataire sans profil client), afficher une page vide paginée
        if (!$clientId) {
            $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            return view('client.bookings.index', compact('bookings'))
                ->with('info', 'Vous n\'avez pas encore effectué de réservations en tant que client.');
        }

        $query = Booking::where('client_id', $clientId);
        
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

    /**
     * Client booking history with advanced filtering and stats
     */
    public function clientHistory(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'client') {
            abort(403, 'Accès non autorisé. Cette page est réservée aux clients.');
        }

        // Ensure client record exists
        if (!$user->client) {
            return redirect()->route('client.dashboard')->with('error', 'Votre profil client n\'est pas configuré correctement. Veuillez contacter le support.');
        }

        $query = Booking::where('client_id', $user->client->id)
            ->with(['prestataire.user', 'service', 'paymentTransaction']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by period
        if ($request->filled('period')) {
            switch ($request->period) {
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
                case 'year':
                    $query->whereYear('start_datetime', now()->year);
                    break;
            }
        }

        // Sort
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_high':
                    $query->orderBy('total_price', 'desc');
                    break;
                case 'price_low':
                    $query->orderBy('total_price', 'asc');
                    break;
                case 'oldest':
                    $query->orderBy('start_datetime', 'asc');
                    break;
                case 'recent':
                default:
                    $query->orderBy('start_datetime', 'desc');
                    break;
            }
        } else {
            $query->orderBy('start_datetime', 'desc');
        }

        $bookings = $query->paginate(15);

        // Calculate stats
        $stats = [
            'total_bookings' => Booking::where('client_id', $user->client->id)->count(),
            'total_spent' => Booking::where('client_id', $user->client->id)->sum('total_price'),
            'completed' => Booking::where('client_id', $user->client->id)->where('status', 'completed')->count(),
            'avg_price' => Booking::where('client_id', $user->client->id)->avg('total_price') ?? 0,
        ];

        return view('client.bookings.history', compact('bookings', 'stats'));
    }

    /**
     * Prestataire booking history with advanced filtering and stats
     */
    public function prestataireHistory(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'prestataire') {
            abort(403, 'Accès non autorisé. Cette page est réservée aux prestataires.');
        }

        $prestataire = $user->prestataire;
        $query = Booking::where('prestataire_id', $prestataire->id)
            ->with(['client.user', 'service', 'paymentTransaction']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by period
        if ($request->filled('period')) {
            switch ($request->period) {
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
                case 'year':
                    $query->whereYear('start_datetime', now()->year);
                    break;
            }
        }

        // Sort
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_high':
                    $query->orderBy('total_price', 'desc');
                    break;
                case 'price_low':
                    $query->orderBy('total_price', 'asc');
                    break;
                case 'oldest':
                    $query->orderBy('start_datetime', 'asc');
                    break;
                case 'recent':
                default:
                    $query->orderBy('start_datetime', 'desc');
                    break;
            }
        } else {
            $query->orderBy('start_datetime', 'desc');
        }

        $bookings = $query->paginate(15);

        // Calculate stats
        $stats = [
            'total_bookings' => Booking::where('prestataire_id', $prestataire->id)->count(),
            'total_earned' => Booking::where('prestataire_id', $prestataire->id)->sum('total_price'),
            'completed' => Booking::where('prestataire_id', $prestataire->id)->where('status', 'completed')->count(),
            'avg_price' => Booking::where('prestataire_id', $prestataire->id)->avg('total_price') ?? 0,
        ];

        return view('prestataire.bookings.history', compact('bookings', 'stats'));
    }
}
