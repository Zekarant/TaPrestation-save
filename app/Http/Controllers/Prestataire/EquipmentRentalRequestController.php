<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentRental;
use App\Http\Requests\Prestataire\CancelEquipmentRequest;
use App\Notifications\SimpleEquipmentRentalAcceptedNotification;
use App\Notifications\SimpleEquipmentRentalRejectedNotification;
use App\Notifications\SimpleEquipmentRentalResponseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        
        $request->load(['equipment', 'client.user', 'prestataire']);
        
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
        
        return view('prestataire.equipment-rental-requests.show', compact('request', 'conflicts'));
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
        // $this->authorize('update', $rentalRequest);

        if (!in_array($rentalRequest->status, ['accepted', 'confirmed'])) {
            return back()->with('error', 'Cette demande ne peut pas être annulée.');
        }
        
        DB::transaction(function () use ($rentalRequest, $request) {
            // Annuler la demande
            $rentalRequest->cancel($request->validated()['cancellation_reason']);
            
            // Annuler la location associée si elle existe
            if ($rentalRequest->rental) {
                $rentalRequest->rental->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $request->validated()['cancellation_reason'],
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::id()
                ]);
            }
            
            // We no longer need to change equipment status back to 'active'
            // The equipment status should remain unchanged as availability is managed through rental periods
        });
        
        // Envoyer notification au client
        $rentalRequest->load('client.user');
        Notification::send($rentalRequest->client->user, new SimpleEquipmentRentalRejectedNotification($rentalRequest, $request->validated()['cancellation_reason']));
        
        return redirect()->route('prestataire.equipment-rental-requests.show', $rentalRequest)
                        ->with('success', 'Demande annulée.');
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
}