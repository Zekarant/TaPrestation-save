<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentRental;
use App\Models\PaymentTransaction;
use App\Http\Requests\Prestataire\CancelEquipmentRequest;
use App\Notifications\SimpleEquipmentRentalAcceptedNotification;
use App\Notifications\SimpleEquipmentRentalRejectedNotification;
use App\Notifications\SimpleEquipmentRentalResponseNotification;
use App\Support\PaymentMetadataNormalizer;
use App\Services\StripePaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EquipmentRentalRequestController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:prestataire')->except(['show']);
    }
    
    /**
     * Affiche la liste des demandes de location
     */
    public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        $query = $prestataire->equipmentRentalRequests()
                            ->with(['equipment', 'client.user'])
                            ->latest();
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('equipment')) {
            $query->where('equipment_id', $request->equipment);
        }
        
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }
        
        $requests = $query->paginate(15);
        
        // Statistiques
        $stats = [
            'total' => $prestataire->equipmentRentalRequests()->count(),
            'pending' => $prestataire->equipmentRentalRequests()->pending()->count(),
            'accepted' => $prestataire->equipmentRentalRequests()->where('status', 'accepted')->count(),
            'rejected' => $prestataire->equipmentRentalRequests()->where('status', 'rejected')->count(),
            'expired' => $prestataire->equipmentRentalRequests()->where('status', 'expired')->count(),
        ];
        
        // Liste des équipements pour le filtre
        $equipments = $prestataire->equipments()->active()->get(['id', 'name']);
        
        return view('prestataire.equipment-rental-requests.index', compact('requests', 'stats', 'equipments'));
    }
    
    /**
     * Affiche les détails d'une demande
     */
    public function show(Request $httpRequest, $id)
    {
        // Find the rental request or return 404
        $request = EquipmentRentalRequest::find($id);
        
        if (!$request) {
            if ($httpRequest->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "La demande avec l'ID {$id} n'existe pas ou a été supprimée"
                ], 404);
            }
            
            abort(404, "La demande avec l'ID {$id} n'existe pas ou a été supprimée");
        }
        
        // Authorization check removed to allow access
        
        $request->load(['equipment', 'client.user', 'prestataire', 'rental']);
        $this->promotePendingRentalPaymentFromStripe($request);
        
        // For AJAX requests, return JSON data
        if ($httpRequest->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $request->id,
                'title' => $request->equipment->name ?? 'Équipement',
                'client_name' => $request->client->user->name ?? 'Client',
                'date' => $request->start_date->format('d/m/Y') . ' au ' . $request->end_date->format('d/m/Y'),
                'duration' => $request->duration_days . ' jours',
                'price' => number_format($request->total_amount, 2, ',', ' ') . ' €',
                'description' => $request->notes ?? 'Aucune description',
                'status' => $request->status,
                'status_label' => $this->getStatusLabel($request->status)
            ]);
        }
        
        // Vérifier les conflits de dates
        $conflicts = $this->checkDateConflicts($request);
        $paymentContext = $this->buildPaymentContext($request);
        
        return view('prestataire.equipment-rental-requests.show', compact('request', 'conflicts', 'paymentContext'));
    }
    
    /**
     * Get status label for display
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'accepted' => 'Acceptée',
            'rejected' => 'Refusée',
            'cancelled' => 'Annulée',
            'completed' => 'Terminée',
            'expired' => 'Expirée'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
    
    /**
     * Accepte une demande de location
     */
    public function accept(Request $request, $requestId)
    {
        // Récupérer explicitement la demande de location
        $equipmentRentalRequest = EquipmentRentalRequest::findOrFail($requestId);
        
        // $this->authorize('update', $rentalRequest);
        
        // Charger la relation equipment si elle n'est pas déjà chargée
        $equipmentRentalRequest->load('equipment');
        
        // Vérifier que l'équipement existe
        if (!$equipmentRentalRequest->equipment) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Équipement introuvable.'], 404);
            }
            
            return redirect()->route('prestataire.equipment-rental-requests.index')
                            ->with('error', 'Équipement introuvable.');
        }
        
        // Vérifier la disponibilité de l'équipement pour la période demandée
        // Check if the equipment is active (not set to inactive or maintenance by the prestataire)
        if (!$equipmentRentalRequest->equipment->isActive()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'L\'équipement est désactivé ou en maintenance. Veuillez mettre son statut à \'actif\' avant d\'accepter des demandes.'], 400);
            }
            
            return redirect()->route('prestataire.equipment-rental-requests.show', $equipmentRentalRequest->id)
                            ->with('error', 'L\'équipement est désactivé ou en maintenance. Veuillez mettre son statut à \'actif\' avant d\'accepter des demandes.');
        }
        
        // Check if the equipment is marked as available by the prestataire
        if (!$equipmentRentalRequest->equipment->is_available) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'L\'équipement est marqué comme indisponible. Veuillez le rendre disponible avant d\'accepter des demandes.'], 400);
            }
            
            return redirect()->route('prestataire.equipment-rental-requests.show', $equipmentRentalRequest->id)
                            ->with('error', 'L\'équipement est marqué comme indisponible. Veuillez le rendre disponible avant d\'accepter des demandes.');
        }
        
        // Vérifier s'il y a des conflits avec d'autres demandes ou locations
        $hasConflicts = $this->hasConflictingRequests($equipmentRentalRequest);
        
        if ($hasConflicts) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'L\'équipement est déjà réservé pour cette période. Veuillez vérifier les dates.'], 400);
            }
            
            return redirect()->route('prestataire.equipment-rental-requests.show', $equipmentRentalRequest->id)
                            ->with('error', 'L\'équipement est déjà réservé pour cette période. Veuillez vérifier les dates.');
        }
        
        // Double-check availability for the requested period
        if (!$equipmentRentalRequest->equipment->isAvailableForPeriod($equipmentRentalRequest->start_date, $equipmentRentalRequest->end_date)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'L\'équipement est déjà réservé pour cette période. Vérifiez l\'agenda des réservations.'], 400);
            }
            
            return redirect()->route('prestataire.equipment-rental-requests.show', $equipmentRentalRequest->id)
                            ->with('error', 'L\'équipement est déjà réservé pour cette période. Vérifiez l\'agenda des réservations.');
        }
        
        try {
            DB::transaction(function () use ($equipmentRentalRequest) {
                // Accepter la demande
                $equipmentRentalRequest->accept();
                
                // Créer la location
                $rental = EquipmentRental::create([
                'rental_number' => 'LOC-' . strtoupper(uniqid()),
                'rental_request_id' => $equipmentRentalRequest->id,
                'equipment_id' => $equipmentRentalRequest->equipment_id,
                'client_id' => $equipmentRentalRequest->client_id,
                'prestataire_id' => $equipmentRentalRequest->prestataire_id,
                'start_date' => $equipmentRentalRequest->start_date,
                'end_date' => $equipmentRentalRequest->end_date,
                'planned_duration_days' => $equipmentRentalRequest->duration_days ?? 1,
                'unit_price' => $equipmentRentalRequest->unit_price ?? 0,
                'base_amount' => $equipmentRentalRequest->total_amount ?? 0,
                'security_deposit' => $equipmentRentalRequest->security_deposit ?? 0,
                'total_amount' => $equipmentRentalRequest->total_amount ?? 0,
                'final_amount' => $equipmentRentalRequest->final_amount ?? $equipmentRentalRequest->total_amount,
                'pickup_address' => $equipmentRentalRequest->pickup_address,
                'status' => 'confirmed',
                'payment_status' => 'pending'
            ]);
            
                // We no longer change the equipment status to 'rented'
                // The equipment remains 'active' and availability is managed through rental periods
            });
        } catch (\Exception $e) {
            \Log::error('Error accepting equipment rental request: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l\'acceptation de la demande.'], 500);
            }
            
            return redirect()->route('prestataire.equipment-rental-requests.index')
                            ->with('error', 'Une erreur est survenue lors de l\'acceptation de la demande.');
        }
        
        // Envoyer notification au client
        $equipmentRentalRequest->load('client.user');
        Notification::send($equipmentRentalRequest->client->user, new SimpleEquipmentRentalAcceptedNotification($equipmentRentalRequest));
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Demande acceptée avec succès! La location a été créée.'
            ]);
        }
        
        return redirect()->route('prestataire.equipment-rental-requests.show', $equipmentRentalRequest->id)
                        ->with('success', 'Demande acceptée avec succès! La location a été créée.');
    }
    
    /**
     * Vérifie s'il y a des demandes ou locations qui se chevauchent avec la demande actuelle
     */
    private function hasConflictingRequests($request)
    {
        // Vérifier les autres demandes de location acceptées ou en cours
        $conflictingRentals = $request->equipment->rentals()
            ->where('id', '!=', $request->id)
            ->whereIn('status', ['confirmed', 'in_preparation', 'delivered', 'in_use'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();
        
        if ($conflictingRentals) {
            return true;
        }
        
        // Vérifier également les autres demandes acceptées (mais pas encore converties en locations)
        $conflictingAcceptedRequests = $request->equipment->rentalRequests()
            ->where('id', '!=', $request->id)
            ->where('status', 'accepted')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();
        
        return $conflictingAcceptedRequests;
    }
    
    /**
     * Rejette une demande de location
     */
    public function reject(Request $request, $requestId)
    {
        // Récupérer explicitement la demande de location
        $equipmentRentalRequest = EquipmentRentalRequest::findOrFail($requestId);
        
        // $this->authorize('update', $equipmentRentalRequest);
        
        $rejectionReason = $request->input('rejection_reason');
        $equipmentRentalRequest->reject($rejectionReason);
        
        // Envoyer notification au client
        $equipmentRentalRequest->load('client.user');
        Notification::send($equipmentRentalRequest->client->user, new SimpleEquipmentRentalRejectedNotification($equipmentRentalRequest, $rejectionReason));
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Demande rejetée.'
            ]);
        }
        
        return redirect()->route('prestataire.equipment-rental-requests.show', $equipmentRentalRequest->id)
                        ->with('success', 'Demande rejetée.');
    }
    
    /**
     * Annule une demande acceptée (avant confirmation)
     */
    public function cancel(CancelEquipmentRequest $request, EquipmentRentalRequest $rentalRequest)
    {
        $prestataireId = (int) (Auth::user()?->prestataire?->id ?? 0);
        if ($prestataireId <= 0 || (int) $rentalRequest->prestataire_id !== $prestataireId) {
            abort(403);
        }

        if (!in_array($rentalRequest->status, ['accepted', 'confirmed', 'in_preparation'], true)) {
            return back()->with('error', 'Cette demande ne peut pas être annulée.');
        }

        $rentalRequest->loadMissing('equipment');
        $cancellationHours = max(0, (int) ($rentalRequest->equipment?->cancellation_hours ?? 24));
        $startDate = $rentalRequest->start_date ? Carbon::parse($rentalRequest->start_date)->startOfDay() : null;
        if ($startDate) {
            $cancelDeadline = $startDate->copy()->subHours($cancellationHours);
            if (now()->greaterThan($cancelDeadline)) {
                return back()->with('error', 'Le délai d\'annulation est dépassé pour cette location.');
            }
        }

        $reason = trim((string) ($request->validated()['cancellation_reason'] ?? ''));
        if ($reason === '') {
            $reason = 'Annulée par le prestataire';
        }

        $refundSummary = $this->processCancellationRefunds($rentalRequest, 'prestataire', $reason);
        if (!$refundSummary['ok']) {
            return back()->with('error', 'Annulation impossible pour le moment. Le remboursement client n\'a pas pu être finalisé.');
        }

        DB::transaction(function () use ($rentalRequest, $reason, $refundSummary) {
            $rentalRequest->cancel($reason, Auth::id());

            if ($rentalRequest->rental) {
                $rentalRequest->rental->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::id()
                ]);

                if ($refundSummary['refund_total'] > 0) {
                    try {
                        $this->persistRentalPaymentStatus($rentalRequest->rental, 'refunded');
                    } catch (\Throwable $e) {
                        // Enum legacy: fallback handled in persistRentalPaymentStatus
                    }
                }
            }            
        });
        
        // Envoyer notification au client
        $rentalRequest->load('client.user');
        Notification::send($rentalRequest->client->user, new SimpleEquipmentRentalRejectedNotification($rentalRequest, $reason));

        $message = 'Demande annulée.';
        if ($refundSummary['refund_total'] > 0) {
            $message .= ' Remboursement déclenché: ' . number_format($refundSummary['refund_total'], 2) . ' €.';
        }
        if ($refundSummary['partial_failure']) {
            $message .= ' Attention: une partie du remboursement est en attente de reprise automatique.';
        }
        
        return redirect()->route('prestataire.equipment-rental-requests.show', $rentalRequest)
                        ->with('success', $message);
    }
    
    /**
     * Répond à une demande avec un message
     */
    public function respond(RespondToEquipmentRequest $request, EquipmentRentalRequest $rentalRequest)
    {
        $this->authorize('update', $rentalRequest);

        $responseMessage = $request->validated()['response_message'];
        
        $rentalRequest->update([
            'prestataire_response' => $responseMessage,
            'responded_at' => now()
        ]);
        
        // Envoyer notification au client
        $rentalRequest->load('client.user');
        Notification::send($rentalRequest->client->user, new SimpleEquipmentRentalResponseNotification($rentalRequest, $responseMessage));
        
        return back()->with('success', 'Réponse envoyée au client.');
    }
    
    /**
     * Marque une demande comme expirée
     */
    public function markExpired(EquipmentRentalRequest $request)
    {
        $this->authorize('update', $request);
        
        if (!$request->isPending()) {
            return back()->with('error', 'Cette demande ne peut pas être marquée comme expirée.');
        }
        
        $request->expire();
        
        return back()->with('success', 'Demande marquée comme expirée.');
    }
    
    /**
     * Exporte les demandes en CSV
     */
    public function export(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        $query = $prestataire->equipmentRentalRequests()
                            ->with(['equipment', 'client.user']);
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('equipment')) {
            $query->where('equipment_id', $request->equipment);
        }
        
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }
        
        $requests = $query->get();
        
        $filename = 'demandes_location_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Numéro',
                'Équipement',
                'Client',
                'Date début',
                'Date fin',
                'Durée (jours)',
                'Montant total',
                'Statut',
                'Date demande',
                'Date réponse'
            ]);
            
            // Données
            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->request_number,
                    $request->equipment->name,
                    $request->client->user->name,
                    $request->start_date->format('d/m/Y'),
                    $request->end_date->format('d/m/Y'),
                    $request->duration_days,
                    number_format($request->final_amount, 2) . ' €',
                    $request->formatted_status,
                    $request->created_at->format('d/m/Y H:i'),
                    $request->responded_at ? $request->responded_at->format('d/m/Y H:i') : ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Vérifie les conflits de dates pour une demande
     */
    private function checkDateConflicts(EquipmentRentalRequest $request)
    {
        // Vérifier si l'équipement existe
        if (!$request->equipment) {
            return collect(); // Retourner une collection vide si l'équipement n'existe pas
        }
        
        return $request->equipment->rentals()
                      ->where('id', '!=', $request->id)
                      ->whereIn('status', ['confirmed', 'in_preparation', 'delivered', 'in_use'])
                      ->where(function ($query) use ($request) {
                          $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                                ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('start_date', '<=', $request->start_date)
                                      ->where('end_date', '>=', $request->end_date);
                                });
                      })
                      ->with('client.user')
                      ->get();
    }

    private function arrayFromJsonOrObject($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return (array) $value->toArray();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return (array) $value;
    }

    private function normalizePaymentStatus(?string $status): string
    {
        return match ((string) $status) {
            'deposit_paid' => 'partial',
            'full_paid', 'completed' => 'paid',
            'refunded', 'partially_refunded' => 'refunded',
            'partial', 'paid', 'pending' => (string) $status,
            default => 'pending',
        };
    }

    private function normalizeTransactionType(?string $type): string
    {
        return PaymentMetadataNormalizer::normalizeTransactionType($type) ?: 'payment';
    }

    private function retrievePaymentIntentForRequest(EquipmentRentalRequest $request, string $paymentIntentId): ?object
    {
        try {
            return app(StripePaymentService::class)->retrievePaymentIntent($paymentIntentId, null);
        } catch (\Throwable $platformError) {
            $connectedAccountId = $request->prestataire?->stripe_account_id ?? $request->equipment?->prestataire?->stripe_account_id;
            if (!empty($connectedAccountId)) {
                try {
                    return app(StripePaymentService::class)->retrievePaymentIntent($paymentIntentId, $connectedAccountId);
                } catch (\Throwable $connectedError) {
                    Log::warning('Prestataire: unable to retrieve rental PaymentIntent on platform and connected account', [
                        'request_id' => $request->id,
                        'payment_intent_id' => $paymentIntentId,
                        'platform_error' => $platformError->getMessage(),
                        'connected_error' => $connectedError->getMessage(),
                    ]);
                }
            } else {
                Log::warning('Prestataire: unable to retrieve rental PaymentIntent on platform account', [
                    'request_id' => $request->id,
                    'payment_intent_id' => $paymentIntentId,
                    'platform_error' => $platformError->getMessage(),
                ]);
            }
        }

        return null;
    }

    private function findLatestRentalPaymentTransaction(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        $requestId = (int) $request->id;
        $requestNumber = trim((string) ($request->request_number ?? ''));
        $statusCandidates = ['paid', 'held', 'succeeded', 'completed', 'refunded', 'partially_refunded'];

        $baseQuery = PaymentTransaction::query()->whereIn('status', $statusCandidates);

        $direct = (clone $baseQuery)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($direct) {
            return $direct;
        }

        $recent = (clone $baseQuery)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(300)
            ->get();

        foreach ($recent as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata)
            );
            if ((int) ($meta['rental_request_id'] ?? 0) === $requestId) {
                return $transaction;
            }

            $description = (string) ($transaction->description ?? '');
            if ($requestNumber !== '' && stripos($description, $requestNumber) !== false) {
                return $transaction;
            }
            if (stripos($description, '#' . $requestId) !== false) {
                return $transaction;
            }
        }

        return null;
    }

    private function findLatestPendingRentalPaymentTransaction(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        $requestId = (int) $request->id;
        $requestNumber = trim((string) ($request->request_number ?? ''));
        $statusCandidates = ['pending', 'processing'];

        $baseQuery = PaymentTransaction::query()->whereIn('status', $statusCandidates);

        $direct = (clone $baseQuery)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($direct) {
            return $direct;
        }

        $recent = (clone $baseQuery)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(300)
            ->get();

        foreach ($recent as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata)
            );
            if ((int) ($meta['rental_request_id'] ?? 0) === $requestId) {
                return $transaction;
            }

            $description = (string) ($transaction->description ?? '');
            if ($requestNumber !== '' && stripos($description, $requestNumber) !== false) {
                return $transaction;
            }
            if (stripos($description, '#' . $requestId) !== false) {
                return $transaction;
            }
        }

        return null;
    }

    private function promotePendingRentalPaymentFromStripe(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        try {
            $pending = $this->findLatestPendingRentalPaymentTransaction($request);
            if (!$pending) {
                return null;
            }

            $piId = trim((string) ($pending->stripe_payment_intent_id ?? $pending->transaction_id ?? ''));
            if ($piId === '') {
                return null;
            }

            $paymentIntent = $this->retrievePaymentIntentForRequest($request, $piId);
            if (!$paymentIntent) {
                return null;
            }

            $piStatus = strtolower((string) ($paymentIntent->status ?? ''));
            if (!in_array($piStatus, ['succeeded', 'requires_capture'], true)) {
                if ($piStatus === 'canceled' && in_array((string) $pending->status, ['pending', 'processing'], true)) {
                    $pending->status = 'failed';
                    $pending->metadata = array_merge(
                        PaymentMetadataNormalizer::normalize((array) ($pending->metadata ?? [])),
                        ['reconciled_from' => 'stripe_intent_poll', 'stripe_intent_status' => $piStatus]
                    );
                    $pending->save();
                }
                return null;
            }

            $piMeta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($paymentIntent->metadata ?? [])
            );
            $metaRequestId = (int) ($piMeta['rental_request_id'] ?? 0);
            if ($metaRequestId > 0 && $metaRequestId !== (int) $request->id) {
                return null;
            }

            $existingMeta = PaymentMetadataNormalizer::normalize((array) ($pending->metadata ?? []));
            $rawType = (string) ($piMeta['tx_type'] ?? ($piMeta['payment_type'] ?? ($existingMeta['tx_type'] ?? ($existingMeta['payment_type'] ?? $pending->type))));
            $txType = $this->normalizeTransactionType($rawType);
            if ($txType === '') {
                $txType = 'payment';
            }

            $amount = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;
            $pending->equipment_rental_id = (int) $request->id;
            $pending->status = 'paid';
            $pending->type = $txType;
            $pending->currency = strtolower((string) ($paymentIntent->currency ?? ($pending->currency ?? 'eur')));
            $pending->transaction_id = $piId;
            if ($amount > 0) {
                $pending->amount = $amount;
            }
            if (empty($pending->description)) {
                $pending->description = (string) ($paymentIntent->description ?? ('Paiement location #' . ($request->request_number ?: $request->id)));
            }
            $pending->metadata = array_merge(
                $existingMeta,
                $piMeta,
                [
                    'rental_request_id' => (string) $request->id,
                    'reconciled_from' => 'stripe_intent_poll',
                    'stripe_intent_status' => $piStatus,
                ]
            );
            $pending->paid_at = $pending->paid_at ?? now();
            $pending->save();

            Log::warning('Prestataire: promoted pending rental transaction to paid from Stripe intent poll', [
                'request_id' => $request->id,
                'transaction_id' => $pending->id,
                'payment_intent_id' => $piId,
            ]);

            return $pending->fresh();
        } catch (\Throwable $e) {
            Log::warning('Prestataire: unable to promote pending rental transaction from Stripe', [
                'request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function findLatestRentalEscrow(EquipmentRentalRequest $request): ?object
    {
        if (!DB::getSchemaBuilder()->hasTable('escrow_transactions')) {
            return null;
        }

        return DB::table('escrow_transactions')
            ->where('escrowable_id', (int) $request->id)
            ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
            ->orderByDesc('id')
            ->first();
    }

    private function persistRentalPaymentStatus(EquipmentRental $rental, string $logicalStatus): void
    {
        if ($logicalStatus === 'partial') {
            $primary = 'partial';
            $fallback = 'deposit_paid';
        } elseif ($logicalStatus === 'refunded') {
            $primary = 'refunded';
            $fallback = 'refund_pending';
        } else {
            $primary = 'paid';
            $fallback = 'full_paid';
        }

        $lastError = null;
        foreach (array_unique([$primary, $fallback]) as $candidate) {
            try {
                $rental->payment_status = $candidate;
                $rental->save();
                return;
            } catch (\Throwable $e) {
                $lastError = $e;
            }
        }

        if ($lastError) {
            throw $lastError;
        }
    }

    private function buildPaymentContext(EquipmentRentalRequest $request): array
    {
        $transaction = $this->findLatestRentalPaymentTransaction($request);
        $escrow = $this->findLatestRentalEscrow($request);

        if (!$transaction && !$escrow) {
            $transaction = $this->promotePendingRentalPaymentFromStripe($request);
        }

        $logicalStatus = $this->normalizePaymentStatus($request->rental?->payment_status ?? 'pending');
        $paymentType = null;

        if ($transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata)
            );
            $rawType = (string) ($meta['payment_type'] ?? ($meta['tx_type'] ?? $transaction->type ?? ''));
            $normalizedTxType = $this->normalizeTransactionType($rawType);

            $paymentType = $rawType !== '' ? $rawType : $normalizedTxType;
            $txStatus = strtolower((string) ($transaction->status ?? ''));
            if (in_array($txStatus, ['refunded', 'partially_refunded'], true)) {
                $logicalStatus = 'refunded';
            } elseif ($logicalStatus === 'pending') {
                $logicalStatus = $normalizedTxType === 'deposit' ? 'partial' : 'paid';
            }
        }

        if ($escrow) {
            $escrowRow = (array) $escrow;
            $escrowMeta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($escrowRow['metadata'] ?? null)
            );
            $escrowPaymentType = strtolower((string) ($escrowMeta['payment_type'] ?? ($escrowMeta['tx_type'] ?? '')));
            if ($escrowPaymentType !== '') {
                $paymentType = $escrowPaymentType;
            }

            $escrowStatus = strtolower((string) ($escrowRow['status'] ?? ''));
            if ($escrowStatus === 'refunded') {
                $logicalStatus = 'refunded';
            } elseif (in_array($escrowStatus, ['pending', 'held', 'partial', 'released'], true) && $logicalStatus === 'pending') {
                $logicalStatus = $escrowPaymentType === 'deposit' ? 'partial' : 'paid';
            }
        }

        if ($request->rental && in_array($logicalStatus, ['partial', 'paid', 'refunded'], true)) {
            try {
                $this->persistRentalPaymentStatus($request->rental, $logicalStatus);
            } catch (\Throwable $e) {
                // keep rendering even if enum mismatch in this environment
            }
        }

        return [
            'logical_status' => $logicalStatus,
            'payment_type' => $paymentType,
            'transaction' => $transaction,
            'escrow' => $escrow,
        ];
    }

    private function findRefundableEscrowsForRequest(EquipmentRentalRequest $request)
    {
        if (!DB::getSchemaBuilder()->hasTable('escrow_transactions')) {
            return collect();
        }

        return DB::table('escrow_transactions')
            ->where('escrowable_id', (int) $request->id)
            ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
            ->whereIn('status', ['pending', 'held'])
            ->orderBy('id')
            ->get();
    }

    private function markRentalTransactionsRefunded(EquipmentRentalRequest $request, float $refundAmount, string $reason, string $cancelledBy): void
    {
        if ($refundAmount <= 0) {
            return;
        }

        $transactions = PaymentTransaction::query()
            ->where('equipment_rental_id', (int) $request->id)
            ->whereIn('status', ['paid', 'held', 'released', 'completed', 'partially_refunded'])
            ->orderByDesc('id')
            ->get();

        if ($transactions->isEmpty()) {
            $latest = $this->findLatestRentalPaymentTransaction($request);
            if ($latest) {
                $transactions = collect([$latest]);
            }
        }

        if ($transactions->isEmpty()) {
            return;
        }

        $totalCaptured = (float) $transactions->sum(function ($tx) {
            return (float) ($tx->amount ?? 0);
        });
        $targetStatus = ($refundAmount + 0.01 >= $totalCaptured) ? 'refunded' : 'partially_refunded';

        foreach ($transactions as $transaction) {
            try {
                $meta = PaymentMetadataNormalizer::normalize(
                    $this->arrayFromJsonOrObject($transaction->metadata ?? [])
                );
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
                Log::warning('Prestataire: unable to update payment transaction refund status after rental cancellation', [
                    'request_id' => $request->id,
                    'transaction_id' => $transaction->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function processCancellationRefunds(EquipmentRentalRequest $request, string $cancelledBy, string $reason): array
    {
        $summary = [
            'ok' => true,
            'refund_total' => 0.0,
            'partial_failure' => false,
        ];

        $escrows = $this->findRefundableEscrowsForRequest($request);
        if ($escrows->isEmpty()) {
            return $summary;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($escrows as $escrow) {
            try {
                $result = app(\App\Services\EscrowService::class)->cancelWithRefund((int) $escrow->id, $cancelledBy);
                if (!($result['success'] ?? false)) {
                    $failureCount++;
                    Log::warning('Prestataire: rental request cancellation refund failed on escrow', [
                        'request_id' => $request->id,
                        'escrow_id' => $escrow->id ?? null,
                        'result' => $result,
                    ]);
                    continue;
                }

                $successCount++;
                $summary['refund_total'] += max(0, (float) ($result['refund_amount'] ?? 0));
            } catch (\Throwable $e) {
                $failureCount++;
                Log::warning('Prestataire: rental request cancellation refund exception', [
                    'request_id' => $request->id,
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
            $this->markRentalTransactionsRefunded($request, (float) $summary['refund_total'], $reason, $cancelledBy);
        }

        return $summary;
    }
}
