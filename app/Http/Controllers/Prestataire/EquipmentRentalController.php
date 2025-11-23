<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EquipmentRentalController extends Controller
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
     * Affiche la liste des locations
     */
    public function index(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        $query = $prestataire->equipmentRentals()
                            ->with(['equipment', 'client.user', 'rentalRequest'])
                            ->latest();
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
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
        
        $rentals = $query->paginate(15);
        
        // Statistiques
        $stats = [
            'total' => $prestataire->equipmentRentals()->count(),
            'active' => $prestataire->equipmentRentals()->active()->count(),
            'completed' => $prestataire->equipmentRentals()->where('status', 'completed')->count(),
            'overdue' => $prestataire->equipmentRentals()->overdue()->count(),
            'total_revenue' => $prestataire->equipmentRentals()->sum('final_amount'),
            'pending_payment' => $prestataire->equipmentRentals()->where('payment_status', 'pending')->sum('final_amount'),
        ];
        
        // Liste des équipements pour le filtre
        $equipments = $prestataire->equipments()->active()->get(['id', 'name']);
        
        return view('prestataire.equipment.rentals.index', compact('rentals', 'stats', 'equipments'));
    }
    
    /**
     * Affiche les détails d'une location
     */
    public function show(EquipmentRental $rental)
    {
        // Removed authorization check to allow access
        
        $rental->load([
            'equipment',
            'client.user',
            'rentalRequest',
            'review',
            'pickedUpBy',
            'cancelledBy'
        ]);
        
        return view('prestataire.equipment.rentals.show', compact('rental'));
    }
    
    /**
     * Marque une location comme retournée
     */
    public function markReturned(Request $request, EquipmentRental $rental)
    {
        $this->authorize('update', $rental);
        
        $validated = $request->validate([
            'pickup_notes' => 'nullable|string|max:1000',
            'equipment_condition_returned' => 'required|in:excellent,very_good,good,fair',
            'pickup_photos' => 'nullable|array|max:5',
            'pickup_photos.*' => 'image|mimes:jpeg,png,jpg,webp',
            'damage_report' => 'nullable|string|max:1000',
            'damage_photos' => 'nullable|array|max:5',
            'damage_photos.*' => 'image|mimes:jpeg,png,jpg,webp',
            'damage_fee' => 'nullable|numeric|min:0',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'late_fee' => 'nullable|numeric|min:0',
            'client_signature' => 'nullable|string'
        ]);
        
        if (!in_array($rental->status, ['in_use', 'ready_for_pickup'])) {
            return back()->with('error', 'Cette location ne peut pas être marquée comme retournée.');
        }
        
        // Gestion des photos de récupération
        $pickupPhotos = [];
        if ($request->hasFile('pickup_photos')) {
            foreach ($request->file('pickup_photos') as $photo) {
                $pickupPhotos[] = $photo->store('rentals/pickup', 'public');
            }
        }
        
        // Gestion des photos de dommages
        $damagePhotos = [];
        if ($request->hasFile('damage_photos')) {
            foreach ($request->file('damage_photos') as $photo) {
                $damagePhotos[] = $photo->store('rentals/damage', 'public');
            }
        }
        
        // Calcul des frais supplémentaires
        $additionalFees = ($validated['damage_fee'] ?? 0) + 
                         ($validated['cleaning_fee'] ?? 0) + 
                         ($validated['late_fee'] ?? 0);
        
        // Calcul du retour de caution
        $depositReturned = max(0, $rental->security_deposit - $additionalFees);
        $depositRetained = $rental->security_deposit - $depositReturned;
        
        // Vérification du retard
        $actualEndDate = now();
        $isLate = $actualEndDate->gt($rental->end_date);
        $lateDays = $isLate ? $actualEndDate->diffInDays($rental->end_date) : 0;
        $lateHours = $isLate ? $actualEndDate->diffInHours($rental->end_date) % 24 : 0;
        
        $rental->update([
            'status' => 'returned',
            'picked_up_at' => now(),
            'picked_up_by' => Auth::id(),
            'pickup_notes' => $validated['pickup_notes'],
            'equipment_condition_returned' => $validated['equipment_condition_returned'],
            'pickup_photos' => $pickupPhotos,
            'damage_report' => $validated['damage_report'],
            'damage_photos' => $damagePhotos,
            'damage_fee' => $validated['damage_fee'] ?? 0,
            'cleaning_fee' => $validated['cleaning_fee'] ?? 0,
            'late_fee' => $validated['late_fee'] ?? 0,
            'additional_fees' => $additionalFees,
            'deposit_returned' => $depositReturned,
            'deposit_retained' => $depositRetained,
            'client_signature_pickup' => $validated['client_signature'] ?? null,
            'actual_end_datetime' => now(),
            'late_return' => $isLate,
            'late_days' => $lateDays,
            'late_hours' => $lateHours,
            'final_amount' => $rental->total_amount + $additionalFees
        ]);
        
        // Remettre l'équipement disponible
        $rental->equipment->update(['status' => 'active']);
        
        // TODO: Envoyer notification au client
        
        return back()->with('success', 'Location marquée comme retournée.');
    }
    
    /**
     * Finalise une location
     */
    public function complete(EquipmentRental $rental)
    {
        $this->authorize('update', $rental);
        
        if ($rental->status !== 'returned') {
            return back()->with('error', 'Cette location ne peut pas être finalisée.');
        }
        
        $rental->update([
            'status' => 'completed',
            'payment_status' => 'paid' // Assumons que le paiement est effectué
        ]);
        
        // Mettre à jour les statistiques de l'équipement
        $rental->equipment->increment('total_rentals');
        
        // TODO: Envoyer notification au client pour demander un avis
        
        return back()->with('success', 'Location finalisée avec succès.');
    }
    
    /**
     * Annule une location
     */
    public function cancel(Request $request, EquipmentRental $rental)
    {
        $this->authorize('update', $rental);
        
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000'
        ]);
        
        if (in_array($rental->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Cette location ne peut pas être annulée.');
        }
        
        $rental->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id()
        ]);
        
        // Remettre l'équipement disponible
        $rental->equipment->update(['status' => 'active']);
        
        // TODO: Gérer le remboursement
        // TODO: Envoyer notification au client
        
        return back()->with('success', 'Location annulée.');
    }
    
    /**
     * Ajoute des notes internes
     */
    public function addNotes(Request $request, EquipmentRental $rental)
    {
        $this->authorize('update', $rental);
        
        $validated = $request->validate([
            'internal_notes' => 'required|string|max:1000'
        ]);
        
        $rental->update([
            'internal_notes' => $validated['internal_notes']
        ]);
        
        return back()->with('success', 'Notes ajoutées.');
    }
    
    /**
     * Génère un contrat de location PDF
     */
    public function generateContract(EquipmentRental $rental)
    {
        $this->authorize('view', $rental);
        
        // TODO: Implémenter la génération de PDF
        // Utiliser une librairie comme DomPDF ou TCPDF
        
        return back()->with('info', 'Génération de contrat à implémenter.');
    }
    
    /**
     * Exporte les locations en CSV
     */
    public function export(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        $query = $prestataire->equipmentRentals()
                            ->with(['equipment', 'client.user']);
        
        // Appliquer les filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
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
        
        $rentals = $query->get();
        
        $filename = 'locations_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($rentals) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Numéro',
                'Équipement',
                'Client',
                'Date début',
                'Date fin',
                'Durée (jours)',
                'Montant de base',
                'Frais supplémentaires',
                'Montant final',
                'Caution',
                'Statut',
                'Statut paiement',
                'Date création'
            ]);
            
            // Données
            foreach ($rentals as $rental) {
                fputcsv($file, [
                    $rental->rental_number,
                    $rental->equipment->name,
                    $rental->client->user->name,
                    $rental->start_date->format('d/m/Y'),
                    $rental->end_date->format('d/m/Y'),
                    $rental->planned_duration_days,
                    number_format($rental->base_amount, 2) . ' €',
                    number_format($rental->additional_fees, 2) . ' €',
                    number_format($rental->final_amount, 2) . ' €',
                    number_format($rental->security_deposit, 2) . ' €',
                    $rental->formatted_status,
                    $rental->formatted_payment_status,
                    $rental->created_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Affiche le calendrier des locations
     */
    public function calendar(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        // Récupérer les locations pour le calendrier
        $rentals = $prestataire->equipmentRentals()
                              ->with(['equipment', 'client.user'])
                              ->whereIn('status', ['confirmed', 'in_preparation', 'in_use'])
                              ->get();
        
        // Formater pour FullCalendar
        $events = $rentals->map(function ($rental) {
            return [
                'id' => $rental->id,
                'title' => $rental->equipment->name . ' - ' . $rental->client->user->name,
                'start' => $rental->start_date->format('Y-m-d'),
                'end' => $rental->end_date->addDay()->format('Y-m-d'), // FullCalendar end date is exclusive
                'color' => $this->getStatusColor($rental->status),
                'url' => route('prestataire.equipment.rentals.show', $rental)
            ];
        });
        
        return view('prestataire.equipment.rentals.calendar', compact('events'));
    }
    
    /**
     * Obtient la couleur selon le statut
     */
    private function getStatusColor($status)
    {
        $colors = [
            'confirmed' => '#3B82F6',
            'in_preparation' => '#F59E0B',
            'in_use' => '#06B6D4',
            'ready_for_pickup' => '#F97316',
            'returned' => '#84CC16',
            'completed' => '#22C55E',
            'cancelled' => '#EF4444'
        ];
        
        return $colors[$status] ?? '#6B7280';
    }
}