<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Notifications\RefundProcessedNotification;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\EquipmentRentalRequest;
use App\Models\UrgentSale;

class BookingController extends Controller
{
    protected $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->middleware('auth');
        $this->middleware('role:prestataire');
        $this->stripeService = $stripeService;
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
            // Utiliser allBookings() pour inclure les bookings avec prestataire_id manquant mais liés via service
            $query = $prestataire->allBookings()->with(['client.user', 'service']);
            
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
     * If the booking was pre-paid (deposit or full), automatically refund the client
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
        
        // Process refunds for any pre-paid bookings
        $refundedAmount = 0;
        $refundErrors = [];
        $bookingPaymentStatuses = [];
        
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            // Check if this booking has any payments that need refunding
            if (in_array($bookingToUpdate->payment_status, ['deposit_paid', 'paid'])) {
                $refundResult = $this->processBookingRefund($bookingToUpdate, $rejectionReason);
                if ($refundResult['success']) {
                    $refundedAmount += $refundResult['amount'];
                    if (($refundResult['amount'] ?? 0) > 0) {
                        $bookingPaymentStatuses[$bookingToUpdate->id] = 'refunded';
                    }
                    if (!empty($refundResult['partial_failure'])) {
                        $refundErrors[] = 'Remboursement partiel sur la réservation #' . $bookingToUpdate->id;
                    }
                } else {
                    $refundErrors[] = $refundResult['error'];
                }
            }
        }
        
        // Update all bookings in the session
        $updatedCount = 0;
        foreach ($bookingsToUpdate as $bookingToUpdate) {
            $updates = [
                'status' => 'rejected',
                'rejection_reason' => $rejectionReason,
            ];

            if (array_key_exists($bookingToUpdate->id, $bookingPaymentStatuses)) {
                $updates['payment_status'] = $bookingPaymentStatuses[$bookingToUpdate->id];
            } elseif ($bookingToUpdate->payment_status === 'pending') {
                $updates['payment_status'] = 'pending';
            }

            $bookingToUpdate->update($updates);
            $updatedCount++;
        }
        
        // Send notification to client (only once for the session)
        $booking->load('client.user');
        Notification::send($booking->client->user, new BookingRejectedNotification($booking, $rejectionReason));
        
        // Send refund notification if applicable
        if ($refundedAmount > 0) {
            try {
                Notification::send($booking->client->user, new RefundProcessedNotification($booking, $refundedAmount, $rejectionReason));
            } catch (\Exception $e) {
                Log::warning('Could not send refund notification: ' . $e->getMessage());
            }
        }
        
        $message = $sessionId 
            ? "Session de {$updatedCount} créneaux refusée."
            : 'Réservation refusée.';
        
        if ($refundedAmount > 0) {
            $message .= " Remboursement de " . number_format($refundedAmount, 2) . " € effectué.";
        }
        
        if (!empty($refundErrors)) {
            $message .= " Attention: " . implode(', ', $refundErrors);
        }
            
        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'refunded_amount' => $refundedAmount,
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Process refund for a booking
     */
    private function processBookingRefund(Booking $booking, string $reason = null): array
    {
        try {
            // Priorité: annulation escrow (gère règles d'annulation + frais Stripe)
            $escrows = DB::table('escrow_transactions')
                ->where('escrowable_type', Booking::class)
                ->where('escrowable_id', (int) $booking->id)
                ->whereIn('status', ['pending', 'held'])
                ->orderBy('id')
                ->get();

            if ($escrows->isNotEmpty()) {
                $totalRefunded = 0.0;
                $successCount = 0;
                $failureCount = 0;

                foreach ($escrows as $escrow) {
                    try {
                        $result = app(\App\Services\EscrowService::class)->cancelWithRefund((int) $escrow->id, 'prestataire');
                        if (!($result['success'] ?? false)) {
                            $failureCount++;
                            Log::warning("Escrow refund failed for booking #{$booking->id}", [
                                'escrow_id' => $escrow->id ?? null,
                                'result' => $result,
                            ]);
                            continue;
                        }

                        $successCount++;
                        $totalRefunded += max(0, (float) ($result['refund_amount'] ?? 0));
                    } catch (\Throwable $e) {
                        $failureCount++;
                        Log::warning("Escrow refund exception for booking #{$booking->id}: " . $e->getMessage(), [
                            'escrow_id' => $escrow->id ?? null,
                        ]);
                    }
                }

                if ($successCount === 0 && $failureCount > 0) {
                    return ['success' => false, 'amount' => 0, 'error' => 'Erreur de remboursement escrow'];
                }

                return [
                    'success' => true,
                    'amount' => $totalRefunded,
                    'partial_failure' => $failureCount > 0,
                ];
            }

            // Find the payment transaction(s) for this booking
            $transactions = PaymentTransaction::where('booking_id', $booking->id)
                ->whereIn('status', ['completed', 'paid', 'held', 'released', 'partially_refunded'])
                ->whereIn('type', ['payment', 'deposit', 'balance'])
                ->get();
            
            if ($transactions->isEmpty()) {
                Log::info("No refundable transactions found for booking #{$booking->id}");
                return ['success' => true, 'amount' => 0];
            }
            
            $totalRefunded = 0;
            $successCount = 0;
            $failureCount = 0;
            
            foreach ($transactions as $transaction) {
                $provider = strtolower(trim((string) ($transaction->provider ?? 'stripe')));
                if ($provider !== '' && $provider !== 'stripe') {
                    continue;
                }

                if (!$transaction->stripe_payment_intent_id && !$transaction->stripe_charge_id) {
                    Log::warning("Transaction #{$transaction->id} has no Stripe identifiers, skipping refund");
                    $failureCount++;
                    continue;
                }
                
                try {
                    $refundReason = $reason ?? 'Réservation refusée par le prestataire';
                    $this->stripeService->refundPayment($transaction, null, $refundReason);
                    $totalRefunded += $transaction->amount;
                    $successCount++;
                    
                    Log::info("Refunded transaction #{$transaction->id} for booking #{$booking->id}", [
                        'amount' => $transaction->amount,
                        'stripe_pi' => $transaction->stripe_payment_intent_id,
                    ]);
                } catch (\Exception $e) {
                    $failureCount++;
                    Log::error("Failed to refund transaction #{$transaction->id}: " . $e->getMessage());
                }
            }

            if ($successCount === 0 && $failureCount > 0) {
                return ['success' => false, 'amount' => $totalRefunded, 'error' => 'Erreur de remboursement Stripe'];
            }
            
            return [
                'success' => true,
                'amount' => $totalRefunded,
                'partial_failure' => $failureCount > 0,
            ];
            
        } catch (\Exception $e) {
            Log::error("processBookingRefund error: " . $e->getMessage());
            return ['success' => false, 'amount' => 0, 'error' => $e->getMessage()];
        }
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

            // Libérer l'escrow automatiquement à la complétion
            try {
                $escrow = \Illuminate\Support\Facades\DB::table('escrow_transactions')
                    ->where('escrowable_type', \App\Models\Booking::class)
                    ->where('escrowable_id', $bookingToUpdate->id)
                    ->whereIn('status', ['pending', 'held'])
                    ->first();

                if ($escrow) {
                    $escrowService = app(\App\Services\EscrowService::class);
                    $escrowService->releaseToPrestataire($escrow->id);
                    Log::info("Escrow #{$escrow->id} libéré automatiquement à la complétion de la réservation #{$bookingToUpdate->id}");
                }
            } catch (\Exception $e) {
                Log::error("Erreur libération escrow pour booking #{$bookingToUpdate->id}: " . $e->getMessage());
            }

            // Générer les factures si elles n'existent pas encore
            try {
                $transaction = PaymentTransaction::where('booking_id', $bookingToUpdate->id)->first();
                if ($transaction) {
                    $invoiceService = app(\App\Services\InvoiceGenerationService::class);
                    $invoiceService->generateForBooking($bookingToUpdate, $transaction);
                }
            } catch (\Exception $e) {
                Log::warning("Erreur génération facture pour booking #{$bookingToUpdate->id}: " . $e->getMessage());
            }
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
     * Confirmer le paiement en espèces pour une réservation (côté prestataire)
     * S'applique quand le service a payment_requirement = 'none' (cash autorisé)
     */
    public function confirmCashPayment(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($booking->prestataire_id !== $user->prestataire->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
            }
            return back()->with('error', 'Accès non autorisé.');
        }

        // Vérifier que la réservation est en statut éligible (confirmed ou completed)
        if (!in_array($booking->status, ['confirmed', 'completed'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cette réservation n\'est pas dans un statut permettant la confirmation de paiement.'], 400);
            }
            return back()->with('error', 'Cette réservation n\'est pas dans un statut permettant la confirmation de paiement.');
        }

        // Vérifier que le paiement n'est pas déjà marqué comme payé
        if ($booking->payment_status === 'paid') {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cette réservation est déjà marquée comme payée.'], 400);
            }
            return back()->with('info', 'Cette réservation est déjà marquée comme payée.');
        }

        // Vérifier le mode de paiement du service
        $paymentRequirement = $booking->service?->payment_requirement ?? 'none';

        if ($paymentRequirement === 'full') {
            // Paiement intégral en ligne requis → pas de cash autorisé
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Ce service nécessite un paiement intégral en ligne.'], 400);
            }
            return back()->with('error', 'Ce service nécessite un paiement intégral en ligne. Le client doit payer via son espace.');
        }

        $totalPrice = (float) $booking->total_price;

        // Calculer le montant cash selon le mode
        if ($paymentRequirement === 'deposit') {
            // Acompte payé en ligne → le reste en espèces
            $depositAmount = (float) ($booking->deposit_amount ?? round($totalPrice * 0.30, 2));
            $cashAmount = round($totalPrice - $depositAmount, 2);
            $paymentMethod = 'mixed'; // acompte online + cash
            $description = "Paiement espèces (reste après acompte) réservation #{$booking->booking_number}";
        } else {
            // payment_requirement = 'none' → tout en cash
            $cashAmount = $totalPrice;
            $paymentMethod = 'cash';
            $description = "Paiement espèces réservation #{$booking->booking_number}";
        }

        // Mettre à jour la réservation
        $booking->update([
            'payment_status' => 'paid',
        ]);

        // Créer la transaction
        PaymentTransaction::systemCreate([
            'user_id' => $booking->client_id,
            'booking_id' => $booking->id,
            'amount' => $cashAmount,
            'type' => 'payment',
            'currency' => 'eur',
            'status' => 'paid',
            'provider' => 'cash',
            'transaction_id' => 'CASH-BK-' . $booking->booking_number,
            'payment_method' => $paymentMethod,
            'description' => $description,
            'metadata' => [
                'booking_id' => $booking->id,
                'confirmed_by' => $user->id,
                'prestataire_id' => $user->prestataire->id,
                'payment_requirement' => $paymentRequirement,
                'cash_amount' => $cashAmount,
                'total_price' => $totalPrice,
            ],
        ]);

        // Enregistrer dans le finance_ledger
        \Illuminate\Support\Facades\DB::table('finance_ledger')->insert([
            'type' => 'cash_payment',
            'reference_id' => $booking->id,
            'user_id' => $booking->client_id,
            'prestataire_id' => $user->prestataire->id,
            'amount' => $cashAmount,
            'notes' => $description,
            'meta' => json_encode([
                'booking_number' => $booking->booking_number,
                'payment_method' => $paymentMethod,
                'payment_requirement' => $paymentRequirement,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = $paymentRequirement === 'deposit'
            ? "Paiement espèces de " . number_format($cashAmount, 2) . " € confirmé (reste après acompte)."
            : "Paiement en espèces de " . number_format($cashAmount, 2) . " € confirmé !";

        Log::info("Cash payment confirmed for booking #{$booking->id} by prestataire #{$user->prestataire->id}: {$cashAmount}€");

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cash_amount' => $cashAmount,
            ]);
        }

        return back()->with('success', $message);
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
