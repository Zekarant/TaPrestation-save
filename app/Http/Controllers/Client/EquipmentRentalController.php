<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRental;
use App\Models\EquipmentReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EquipmentRentalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('client');
    }
    
    /**
     * Affiche la liste des locations du client
     */
    public function index(Request $request)
    {
        $query = EquipmentRental::where('client_id', Auth::user()->client->id)
                               ->with(['equipment.prestataire.user', 'equipment.category', 'equipment.subcategory']);
        
        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('rental_status', $request->status);
        }
        
        // Filtrage par période
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'current':
                    $query->where('start_date', '<=', now())
                          ->where('end_date', '>=', now());
                    break;
                case 'upcoming':
                    $query->where('start_date', '>', now());
                    break;
                case 'past':
                    $query->where('end_date', '<', now());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
            }
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rental_number', 'like', '%' . $search . '%')
                  ->orWhereHas('equipment', function ($eq) use ($search) {
                      $eq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'start_date':
                $query->orderBy('start_date', $sortOrder);
                break;
            case 'end_date':
                $query->orderBy('end_date', $sortOrder);
                break;
            case 'amount':
                $query->orderBy('final_amount', $sortOrder);
                break;
            case 'status':
                $query->orderBy('rental_status', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $rentals = $query->paginate(10)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => EquipmentRental::where('client_id', Auth::user()->client->id)->count(),
            'active' => EquipmentRental::where('client_id', Auth::user()->client->id)
                                     ->whereIn('status', ['confirmed', 'in_preparation'])
                                     ->count(),
            'completed' => EquipmentRental::where('client_id', Auth::user()->client->id)
                                        ->where('status', 'completed')
                                        ->count(),
            'pending' => EquipmentRental::where('client_id', Auth::user()->client->id)
                                        ->where('status', 'pending')
                                        ->count(),
            'total_spent' => EquipmentRental::where('client_id', Auth::user()->client->id)
                                          ->where('payment_status', 'paid')
                                          ->sum('final_amount')
        ];
        
        $rentals = EquipmentRental::where('client_id', Auth::user()->client->id)->latest()->paginate(10);

        return view('client.equipment-rentals.index', compact('rentals', 'stats'));
    }
    
    /**
     * Affiche les détails d'une location
     */
    public function show(EquipmentRental $rental)
    {
        // Vérifier que la location appartient au client connecté
        // if ($rental->client_id !== Auth::user()->client->id) {
        //     abort(403);
        // }
        
        $rental->load([
            'equipment.prestataire.user',
            'equipment.category',
            'equipment.subcategory',
            'equipment.photos',
            'rentalRequest',
            'review'
        ]);
        
        return view('client.equipment.rentals.show', compact('rental'));
    }
    

    
    /**
     * Signale un problème avec l'équipement
     */
    public function reportIssue(Request $request, EquipmentRental $rental)
    {
        // if ($rental->client_id !== Auth::user()->client->id) {
        //     abort(403);
        // }
        
        if (!in_array($rental->rental_status, ['in_progress'])) {
            return back()->with('error', 'Vous ne pouvez signaler un problème que pendant la location.');
        }
        
        $validated = $request->validate([
            'issue_type' => 'required|in:damage,malfunction,missing_parts,safety,other',
            'issue_description' => 'required|string|min:10|max:1000',
            'issue_photos' => 'nullable|array|max:5',
            'issue_photos.*' => 'image|mimes:jpeg,png,jpg,webp',
            'severity' => 'required|in:low,medium,high,critical'
        ]);
        
        // Gestion des photos du problème
        $issuePhotos = [];
        if ($request->hasFile('issue_photos')) {
            foreach ($request->file('issue_photos') as $photo) {
                $issuePhotos[] = $photo->store('rentals/issues', 'public');
            }
        }
        
        // Ajouter le problème aux données de la location
        $issues = $rental->damage_reports ?? [];
        $issues[] = [
            'type' => $validated['issue_type'],
            'description' => $validated['issue_description'],
            'photos' => $issuePhotos,
            'severity' => $validated['severity'],
            'reported_by' => 'client',
            'reported_at' => now()->toISOString(),
            'status' => 'reported'
        ];
        
        $rental->update([
            'damage_reports' => $issues,
            'has_issues' => true
        ]);
        
        // TODO: Envoyer notification urgente au prestataire
        
        return back()->with('success', 'Problème signalé avec succès. Le prestataire sera notifié.');
    }
    
    /**
     * Demande une prolongation de location
     */
    public function requestExtension(Request $request, EquipmentRental $rental)
    {
        if ($rental->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if (!in_array($rental->rental_status, ['in_progress'])) {
            return back()->with('error', 'Vous ne pouvez demander une prolongation que pendant la location.');
        }
        
        $validated = $request->validate([
            'new_end_date' => 'required|date|after:' . $rental->end_date,
            'extension_reason' => 'nullable|string|max:500'
        ]);
        
        $newEndDate = \Carbon\Carbon::parse($validated['new_end_date']);
        $originalEndDate = \Carbon\Carbon::parse($rental->end_date);
        $extensionDays = $originalEndDate->diffInDays($newEndDate);
        
        // Calculer le coût de la prolongation
        $extensionCost = $rental->equipment->calculatePrice($extensionDays);
        
        // Vérifier la disponibilité pour la période d'extension
        if (!$rental->equipment->isAvailableForPeriod($rental->end_date, $validated['new_end_date'], $rental->id)) {
            return back()->with('error', 'L\'équipement n\'est pas disponible pour cette période d\'extension.');
        }
        
        // Ajouter la demande d'extension aux métadonnées
        $extensionRequests = $rental->extension_requests ?? [];
        $extensionRequests[] = [
            'requested_end_date' => $validated['new_end_date'],
            'extension_days' => $extensionDays,
            'extension_cost' => $extensionCost,
            'reason' => $validated['extension_reason'],
            'requested_at' => now()->toISOString(),
            'status' => 'pending'
        ];
        
        $rental->update([
            'extension_requests' => $extensionRequests,
            'has_extension_request' => true
        ]);
        
        // TODO: Envoyer notification au prestataire
        
        return back()->with('success', 'Demande de prolongation envoyée au prestataire.');
    }
    
    /**
     * Confirme le retour de l'équipement
     */
    public function confirmReturn(Request $request, EquipmentRental $rental)
    {
        if ($rental->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if ($rental->rental_status !== 'returned') {
            return back()->with('error', 'Le retour n\'a pas encore été enregistré par le prestataire.');
        }
        
        $validated = $request->validate([
            'return_condition_photos' => 'nullable|array|max:5',
            'return_condition_photos.*' => 'image|mimes:jpeg,png,jpg,webp',
            'return_notes' => 'nullable|string|max:1000',
            'client_signature' => 'nullable|string'
        ]);
        
        // Gestion des photos de condition au retour
        $conditionPhotos = [];
        if ($request->hasFile('return_condition_photos')) {
            foreach ($request->file('return_condition_photos') as $photo) {
                $conditionPhotos[] = $photo->store('rentals/return-condition', 'public');
            }
        }
        
        $rental->update([
            'rental_status' => 'completed',
            'return_condition_photos' => array_merge($rental->return_condition_photos ?? [], $conditionPhotos),
            'return_notes' => $validated['return_notes'],
            'client_return_signature' => $validated['client_signature'],
            'return_confirmed_at' => now()
        ]);
        
        // TODO: Envoyer notification au prestataire
        
        return back()->with('success', 'Retour confirmé! Vous pouvez maintenant laisser un avis.');
    }
    
    /**
     * Affiche le formulaire d'avis
     */
    public function reviewForm(EquipmentRental $rental)
    {
        if ($rental->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if ($rental->rental_status !== 'completed') {
            return back()->with('error', 'Vous ne pouvez laisser un avis qu\'après la fin de la location.');
        }
        
        if ($rental->review) {
            return back()->with('info', 'Vous avez déjà laissé un avis pour cette location.');
        }
        
        $rental->load(['equipment.prestataire.user', 'equipment.category', 'equipment.subcategory']);
        
        return view('client.equipment.rentals.review', compact('rental'));
    }
    
    /**
     * Enregistre l'avis
     */
    public function submitReview(Request $request, EquipmentRental $rental)
    {
        if ($rental->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if ($rental->rental_status !== 'completed' || $rental->review) {
            return back()->with('error', 'Impossible de laisser un avis pour cette location.');
        }
        
        $validated = $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'condition_rating' => 'required|integer|min:1|max:5',
            'performance_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5',
            'service_rating' => 'required|integer|min:1|max:5',
            'review_title' => 'required|string|max:100',
            'review_content' => 'required|string|min:20|max:1000',
            'positive_points' => 'nullable|string|max:500',
            'negative_points' => 'nullable|string|max:500',
            'usage_context' => 'nullable|string|max:300',
            'usage_duration' => 'nullable|in:few_hours,half_day,full_day,multiple_days,week_plus',
            'usage_type' => 'nullable|in:professional,personal,event,project,emergency,other',
            'usage_frequency' => 'nullable|in:first_time,occasional,regular,frequent',
            'would_recommend' => 'required|boolean',
            'review_photos' => 'nullable|array|max:5',
            'review_photos.*' => 'image|mimes:jpeg,png,jpg,webp'
        ]);
        
        // Gestion des photos de l'avis
        $reviewPhotos = [];
        if ($request->hasFile('review_photos')) {
            foreach ($request->file('review_photos') as $photo) {
                $reviewPhotos[] = $photo->store('reviews/photos', 'public');
            }
        }
        
        $review = EquipmentReview::create([
            'equipment_id' => $rental->equipment_id,
            'rental_id' => $rental->id,
            'client_id' => $rental->client_id,
            'prestataire_id' => $rental->prestataire_id,
            'overall_rating' => $validated['overall_rating'],
            'condition_rating' => $validated['condition_rating'],
            'performance_rating' => $validated['performance_rating'],
            'value_rating' => $validated['value_rating'],
            'service_rating' => $validated['service_rating'],
            'review_title' => $validated['review_title'],
            'review_content' => $validated['review_content'],
            'positive_points' => $validated['positive_points'],
            'negative_points' => $validated['negative_points'],
            'usage_context' => $validated['usage_context'],
            'usage_duration' => $validated['usage_duration'],
            'usage_type' => $validated['usage_type'],
            'usage_frequency' => $validated['usage_frequency'],
            'would_recommend' => $validated['would_recommend'],
            'review_photos' => $reviewPhotos,
            'is_verified' => true, // Avis vérifié car basé sur une vraie location
            'reviewer_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Mettre à jour les statistiques de l'équipement
        $rental->equipment->updateRatingStats();
        
        // TODO: Envoyer notification au prestataire
        
        return redirect()->route('client.equipment.rentals.show', $rental)
                        ->with('success', 'Merci pour votre avis!');
    }
    
    /**
     * Annule une location (si possible)
     */
    public function cancel(Request $request, EquipmentRental $rental)
    {
        if ($rental->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if (!in_array($rental->rental_status, ['confirmed', 'in_preparation'])) {
            return back()->with('error', 'Cette location ne peut plus être annulée.');
        }
        
        // Vérifier les conditions d'annulation
        $hoursUntilStart = now()->diffInHours($rental->start_date, false);
        if ($hoursUntilStart < 24) {
            return back()->with('error', 'Vous ne pouvez plus annuler moins de 24h avant le début de la location.');
        }
        
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);
        
        $rental->update([
            'rental_status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_by' => 'client'
        ]);
        
        // TODO: Gérer le remboursement selon les conditions
        // TODO: Envoyer notification au prestataire
        
        return back()->with('success', 'Location annulée. Le remboursement sera traité selon les conditions.');
    }
    
    /**
     * Télécharge le contrat de location
     */
    public function downloadContract(EquipmentRental $rental)
    {
        if ($rental->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        // TODO: Générer le PDF du contrat
        // Pour l'instant, redirection vers la page de détails
        return redirect()->route('client.equipment.rentals.show', $rental)
                        ->with('info', 'Génération de contrat à implémenter.');
    }
    
    /**
     * Exporte les locations en CSV
     */
    public function export(Request $request)
    {
        $query = EquipmentRental::where('client_id', Auth::user()->client->id)
                               ->with(['equipment.prestataire.user']);
        
        if ($request->filled('status')) {
            $query->where('rental_status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }
        
        $rentals = $query->orderBy('start_date', 'desc')->get();
        
        $filename = 'mes_locations_' . now()->format('Y-m-d') . '.csv';
        
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
                'Prestataire',
                'Date début',
                'Date fin',
                'Durée (jours)',
                'Montant total',
                'Statut location',
                'Statut paiement',
                'Date création'
            ]);
            
            foreach ($rentals as $rental) {
                fputcsv($file, [
                    $rental->rental_number,
                    $rental->equipment->name,
                    $rental->equipment->prestataire->user->name,
                    $rental->start_date,
                    $rental->end_date,
                    $rental->duration_days,
                    number_format($rental->final_amount, 2) . ' €',
                    ucfirst($rental->rental_status),
                    ucfirst($rental->payment_status),
                    $rental->created_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}