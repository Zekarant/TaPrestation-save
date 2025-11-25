<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\EquipmentReview;
use App\Models\EquipmentRental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EquipmentReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('client');
    }
    
    /**
     * Affiche la liste des avis du client
     */
    public function index(Request $request)
    {
        $query = EquipmentReview::where('client_id', Auth::user()->client->id)
                               ->with(['equipment.prestataire.user', 'rental']);
        
        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrage par note
        if ($request->filled('rating')) {
            $query->where('overall_rating', $request->rating);
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('review_title', 'like', '%' . $search . '%')
                  ->orWhere('review_content', 'like', '%' . $search . '%')
                  ->orWhereHas('equipment', function ($eq) use ($search) {
                      $eq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('overall_rating', $sortOrder);
                break;
            case 'equipment':
                $query->join('equipment', 'equipment_reviews.equipment_id', '=', 'equipment.id')
                      ->orderBy('equipment.name', $sortOrder)
                      ->select('equipment_reviews.*');
                break;
            case 'helpful':
                $query->orderBy('helpful_count', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $reviews = $query->paginate(10)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => EquipmentReview::where('client_id', Auth::user()->client->id)->count(),
            'published' => EquipmentReview::where('client_id', Auth::user()->client->id)->where('status', 'approved')->count(),
            'pending' => EquipmentReview::where('client_id', Auth::user()->client->id)->where('status', 'pending')->count(),
            'average_rating' => EquipmentReview::where('client_id', Auth::user()->client->id)->avg('overall_rating'),
            'total_helpful' => EquipmentReview::where('client_id', Auth::user()->client->id)->sum('helpful_count')
        ];
        
        return view('client.equipment.reviews.index', compact('reviews', 'stats'));
    }
    
    /**
     * Affiche les détails d'un avis
     */
    public function show(EquipmentReview $review)
    {
        // Vérifier que l'avis appartient au client connecté
        if ($review->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        $review->load([
            'equipment.prestataire.user',
            'equipment.category',
            'equipment.subcategory',
            'rental',
            'prestataireResponse'
        ]);
        
        return view('client.equipment.reviews.show', compact('review'));
    }
    
    /**
     * Affiche le formulaire de modification d'un avis
     */
    public function edit(EquipmentReview $review)
    {
        if ($review->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        // Vérifier que l'avis peut encore être modifié
        if ($review->status === 'approved' && $review->created_at->diffInDays(now()) > 7) {
            return back()->with('error', 'Vous ne pouvez plus modifier cet avis après 7 jours de publication.');
        }
        
        if ($review->prestataire_response) {
            return back()->with('error', 'Vous ne pouvez plus modifier cet avis car le prestataire y a répondu.');
        }
        
        $review->load(['equipment.prestataire.user', 'rental']);
        
        return view('client.equipment.reviews.edit', compact('review'));
    }
    
    /**
     * Met à jour un avis
     */
    public function update(Request $request, EquipmentReview $review)
    {
        if ($review->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        // Vérifications de modification
        if ($review->status === 'approved' && $review->created_at->diffInDays(now()) > 7) {
            return back()->with('error', 'Vous ne pouvez plus modifier cet avis.');
        }
        
        if ($review->prestataire_response) {
            return back()->with('error', 'Vous ne pouvez plus modifier cet avis car le prestataire y a répondu.');
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
            'review_photos.*' => 'image|mimes:jpeg,png,jpg,webp',
            'remove_photos' => 'nullable|array'
        ]);
        
        // Gestion des photos
        $currentPhotos = $review->review_photos ?? [];
        
        // Supprimer les photos sélectionnées
        if ($request->filled('remove_photos')) {
            foreach ($request->remove_photos as $photoToRemove) {
                if (in_array($photoToRemove, $currentPhotos)) {
                    Storage::disk('public')->delete($photoToRemove);
                    $currentPhotos = array_diff($currentPhotos, [$photoToRemove]);
                }
            }
        }
        
        // Ajouter de nouvelles photos
        if ($request->hasFile('review_photos')) {
            foreach ($request->file('review_photos') as $photo) {
                $currentPhotos[] = $photo->store('reviews/photos', 'public');
            }
        }
        
        // Limiter à 5 photos maximum
        $currentPhotos = array_slice(array_values($currentPhotos), 0, 5);
        
        $review->update(array_merge($validated, [
            'review_photos' => $currentPhotos,
            'status' => 'pending', // Remettre en attente de modération si modifié
            'updated_at' => now()
        ]));
        
        // Mettre à jour les statistiques de l'équipement
        $review->equipment->updateRatingStats();
        
        return redirect()->route('client.equipment.reviews.show', $review)
                        ->with('success', 'Votre avis a été mis à jour avec succès!');
    }
    
    /**
     * Supprime un avis
     */
    public function destroy(EquipmentReview $review)
    {
        if ($review->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        // Vérifier que l'avis peut être supprimé
        if ($review->status === 'approved' && $review->created_at->diffInDays(now()) > 30) {
            return back()->with('error', 'Vous ne pouvez plus supprimer cet avis après 30 jours de publication.');
        }
        
        // Supprimer les photos associées
        if ($review->review_photos) {
            foreach ($review->review_photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }
        
        $equipment = $review->equipment;
        $review->delete();
        
        // Mettre à jour les statistiques de l'équipement
        $equipment->updateRatingStats();
        
        return redirect()->route('client.equipment.reviews.index')
                        ->with('success', 'Votre avis a été supprimé.');
    }
    
    /**
     * Marque un avis comme utile/pas utile
     */
    public function toggleHelpful(Request $request, EquipmentReview $review)
    {
        if ($review->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'helpful' => 'required|boolean'
        ]);
        
        // Pour l'instant, on ne permet pas aux clients de voter sur leurs propres avis
        // Cette fonctionnalité serait plutôt pour les autres utilisateurs
        
        return back()->with('info', 'Vous ne pouvez pas voter sur votre propre avis.');
    }
    
    /**
     * Signale un problème avec la réponse du prestataire
     */
    public function reportResponse(Request $request, EquipmentReview $review)
    {
        if ($review->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if (!$review->prestataire_response) {
            return back()->with('error', 'Aucune réponse à signaler.');
        }
        
        $validated = $request->validate([
            'report_reason' => 'required|in:inappropriate,offensive,spam,false_info,other',
            'report_description' => 'required|string|min:10|max:500'
        ]);
        
        // Ajouter le signalement aux métadonnées de l'avis
        $reports = $review->response_reports ?? [];
        $reports[] = [
            'reason' => $validated['report_reason'],
            'description' => $validated['report_description'],
            'reported_by' => 'client',
            'reported_at' => now()->toISOString(),
            'status' => 'pending'
        ];
        
        $review->update([
            'response_reports' => $reports,
            'has_reported_response' => true
        ]);
        
        // TODO: Envoyer notification aux administrateurs
        
        return back()->with('success', 'Signalement envoyé. Nous examinerons la réponse du prestataire.');
    }
    
    /**
     * Affiche les statistiques des avis du client
     */
    public function stats()
    {
        $clientId = Auth::user()->client->id;
        
        // Statistiques générales
        $totalReviews = EquipmentReview::where('client_id', $clientId)->count();
        $averageRating = EquipmentReview::where('client_id', $clientId)->avg('overall_rating');
        $totalHelpful = EquipmentReview::where('client_id', $clientId)->sum('helpful_count');
        
        // Répartition par note
        $ratingDistribution = EquipmentReview::where('client_id', $clientId)
                                           ->select('overall_rating', DB::raw('count(*) as count'))
                                           ->groupBy('overall_rating')
                                           ->orderBy('overall_rating', 'desc')
                                           ->pluck('count', 'overall_rating')
                                           ->toArray();
        
        // Répartition par statut
        $statusDistribution = EquipmentReview::where('client_id', $clientId)
                                           ->select('status', DB::raw('count(*) as count'))
                                           ->groupBy('status')
                                           ->pluck('count', 'status')
                                           ->toArray();
        
        // Évolution mensuelle
        $monthlyStats = EquipmentReview::where('client_id', $clientId)
                                     ->where('created_at', '>=', now()->subMonths(12))
                                     ->select(
                                         DB::raw('YEAR(created_at) as year'),
                                         DB::raw('MONTH(created_at) as month'),
                                         DB::raw('count(*) as count'),
                                         DB::raw('avg(overall_rating) as avg_rating')
                                     )
                                     ->groupBy('year', 'month')
                                     ->orderBy('year')
                                     ->orderBy('month')
                                     ->get();
        
        // Top équipements notés
        $topRatedEquipment = EquipmentReview::where('client_id', $clientId)
                                          ->with('equipment')
                                          ->orderBy('overall_rating', 'desc')
                                          ->orderBy('created_at', 'desc')
                                          ->limit(5)
                                          ->get();
        
        // Avis avec le plus d'interactions
        $mostHelpfulReviews = EquipmentReview::where('client_id', $clientId)
                                           ->where('helpful_count', '>', 0)
                                           ->with('equipment')
                                           ->orderBy('helpful_count', 'desc')
                                           ->limit(5)
                                           ->get();
        
        // Répartition par type d'usage
        $usageTypeStats = EquipmentReview::where('client_id', $clientId)
                                        ->whereNotNull('usage_type')
                                        ->select('usage_type', DB::raw('count(*) as count'))
                                        ->groupBy('usage_type')
                                        ->pluck('count', 'usage_type')
                                        ->toArray();
        
        // Taux de recommandation
        $recommendationRate = EquipmentReview::where('client_id', $clientId)
                                           ->whereNotNull('would_recommend')
                                           ->selectRaw('AVG(CASE WHEN would_recommend = 1 THEN 100 ELSE 0 END) as rate')
                                           ->value('rate');
        
        return view('client.equipment.reviews.stats', compact(
            'totalReviews',
            'averageRating',
            'totalHelpful',
            'ratingDistribution',
            'statusDistribution',
            'monthlyStats',
            'topRatedEquipment',
            'mostHelpfulReviews',
            'usageTypeStats',
            'recommendationRate'
        ));
    }
    
    /**
     * Exporte les avis en CSV
     */
    public function export(Request $request)
    {
        $query = EquipmentReview::where('client_id', Auth::user()->client->id)
                               ->with(['equipment.prestataire.user', 'rental']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('rating_min')) {
            $query->where('overall_rating', '>=', $request->rating_min);
        }
        
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        
        $reviews = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'mes_avis_equipements_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($reviews) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Équipement',
                'Prestataire',
                'Note globale',
                'Note condition',
                'Note performance',
                'Note rapport qualité/prix',
                'Note service',
                'Titre',
                'Commentaire',
                'Recommande',
                'Statut',
                'Date création',
                'Votes utiles'
            ]);
            
            foreach ($reviews as $review) {
                fputcsv($file, [
                    $review->equipment->name,
                    $review->equipment->prestataire->user->name,
                    $review->overall_rating . '/5',
                    $review->condition_rating . '/5',
                    $review->performance_rating . '/5',
                    $review->value_rating . '/5',
                    $review->service_rating . '/5',
                    $review->review_title,
                    strip_tags($review->review_content),
                    $review->would_recommend ? 'Oui' : 'Non',
                    ucfirst($review->status),
                    $review->created_at->format('d/m/Y H:i'),
                    $review->helpful_count
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}