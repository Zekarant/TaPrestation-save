<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\EquipmentReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('prestataire');
    }
    
    /**
     * Affiche la liste des avis reçus par le prestataire
     */
    public function index(Request $request)
    {
        $query = EquipmentReview::where('prestataire_id', Auth::user()->prestataire->id)
                               ->with(['equipment', 'client.user', 'rental']);
        
        // Filtrage par équipement
        if ($request->filled('equipment')) {
            $query->where('equipment_id', $request->equipment);
        }
        
        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrage par note
        if ($request->filled('rating')) {
            $query->where('overall_rating', $request->rating);
        }
        
        // Filtrage par réponse
        if ($request->filled('response_status')) {
            if ($request->response_status === 'responded') {
                $query->whereNotNull('prestataire_response');
            } else {
                $query->whereNull('prestataire_response');
            }
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('review_title', 'like', '%' . $search . '%')
                  ->orWhere('review_content', 'like', '%' . $search . '%')
                  ->orWhereHas('equipment', function ($eq) use ($search) {
                      $eq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('client.user', function ($u) use ($search) {
                      $u->where('name', 'like', '%' . $search . '%');
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
            case 'client':
                $query->join('clients', 'equipment_reviews.client_id', '=', 'clients.id')
                      ->join('users', 'clients.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortOrder)
                      ->select('equipment_reviews.*');
                break;
            case 'helpful':
                $query->orderBy('helpful_count', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $reviews = $query->paginate(15)->withQueryString();
        
        // Équipements du prestataire pour le filtre
        $equipment = Auth::user()->prestataire->equipments()->active()->get(['id', 'name']);
        
        // Statistiques
        $stats = [
            'total' => EquipmentReview::where('prestataire_id', Auth::user()->prestataire->id)->count(),
            'pending_response' => EquipmentReview::where('prestataire_id', Auth::user()->prestataire->id)
                                                ->whereNull('prestataire_response')
                                                ->where('status', 'approved')
                                                ->count(),
            'average_rating' => EquipmentReview::where('prestataire_id', Auth::user()->prestataire->id)->avg('overall_rating'),
            'five_stars' => EquipmentReview::where('prestataire_id', Auth::user()->prestataire->id)->where('overall_rating', 5)->count(),
            'one_star' => EquipmentReview::where('prestataire_id', Auth::user()->prestataire->id)->where('overall_rating', 1)->count()
        ];
        
        return view('prestataire.equipment.reviews.index', compact('reviews', 'equipment', 'stats'));
    }
    
    /**
     * Affiche les détails d'un avis
     */
    public function show(EquipmentReview $review)
    {
        // Vérifier que l'avis concerne un équipement du prestataire
        if ($review->prestataire_id !== Auth::user()->prestataire->id) {
            abort(403);
        }
        
        $review->load([
            'equipment.category',
            'equipment.subcategory',
            'client.user',
            'rental'
        ]);
        
        return view('prestataire.equipment.reviews.show', compact('review'));
    }
    
    /**
     * Affiche le formulaire de réponse à un avis
     */
    public function respond(EquipmentReview $review)
    {
        if ($review->prestataire_id !== Auth::user()->prestataire->id) {
            abort(403);
        }
        
        if ($review->status !== 'approved') {
            return back()->with('error', 'Vous ne pouvez répondre qu\'aux avis approuvés.');
        }
        
        if ($review->prestataire_response) {
            return back()->with('error', 'Vous avez déjà répondu à cet avis.');
        }
        
        $review->load(['equipment', 'client.user', 'rental']);
        
        return view('prestataire.equipment.reviews.respond', compact('review'));
    }
    
    /**
     * Enregistre la réponse à un avis
     */
    public function submitResponse(Request $request, EquipmentReview $review)
    {
        if ($review->prestataire_id !== Auth::user()->prestataire->id) {
            abort(403);
        }
        
        if ($review->status !== 'approved' || $review->prestataire_response) {
            return back()->with('error', 'Impossible de répondre à cet avis.');
        }
        
        $validated = $request->validate([
            'response_content' => 'required|string|min:10|max:1000',
            'response_tone' => 'required|in:professional,friendly,apologetic,grateful'
        ]);
        
        $review->update([
            'prestataire_response' => $validated['response_content'],
            'response_tone' => $validated['response_tone'],
            'responded_at' => now()
        ]);
        
        // TODO: Envoyer notification au client
        
        return redirect()->route('prestataire.equipment.reviews.show', $review)
                        ->with('success', 'Votre réponse a été publiée avec succès!');
    }
    
    /**
     * Modifie une réponse (dans les 24h)
     */
    public function editResponse(EquipmentReview $review)
    {
        if ($review->prestataire_id !== Auth::user()->prestataire->id) {
            abort(403);
        }
        
        if (!$review->prestataire_response) {
            return back()->with('error', 'Aucune réponse à modifier.');
        }
        
        // Vérifier que la réponse peut encore être modifiée (24h)
        if ($review->responded_at->diffInHours(now()) > 24) {
            return back()->with('error', 'Vous ne pouvez plus modifier votre réponse après 24h.');
        }
        
        $review->load(['equipment', 'client.user']);
        
        return view('prestataire.equipment.reviews.edit-response', compact('review'));
    }
    
    /**
     * Met à jour une réponse
     */
    public function updateResponse(Request $request, EquipmentReview $review)
    {
        if ($review->prestataire_id !== Auth::user()->prestataire->id) {
            abort(403);
        }
        
        if (!$review->prestataire_response || $review->responded_at->diffInHours(now()) > 24) {
            return back()->with('error', 'Impossible de modifier cette réponse.');
        }
        
        $validated = $request->validate([
            'response_content' => 'required|string|min:10|max:1000',
            'response_tone' => 'required|in:professional,friendly,apologetic,grateful'
        ]);
        
        $review->update([
            'prestataire_response' => $validated['response_content'],
            'response_tone' => $validated['response_tone'],
            'response_updated_at' => now()
        ]);
        
        return redirect()->route('prestataire.equipment.reviews.show', $review)
                        ->with('success', 'Votre réponse a été mise à jour.');
    }
    
    /**
     * Supprime une réponse (dans les 24h)
     */
    public function deleteResponse(EquipmentReview $review)
    {
        if ($review->prestataire_id !== Auth::user()->prestataire->id) {
            abort(403);
        }
        
        if (!$review->prestataire_response) {
            return back()->with('error', 'Aucune réponse à supprimer.');
        }
        
        if ($review->responded_at->diffInHours(now()) > 24) {
            return back()->with('error', 'Vous ne pouvez plus supprimer votre réponse après 24h.');
        }
        
        $review->update([
            'prestataire_response' => null,
            'response_tone' => null,
            'responded_at' => null,
            'response_updated_at' => null
        ]);
        
        return back()->with('success', 'Votre réponse a été supprimée.');
    }
    
    /**
     * Signale un avis inapproprié
     */
    public function report(Request $request, EquipmentReview $review)
    {
        if ($review->prestataire_id !== Auth::user()->prestataire->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'report_reason' => 'required|in:fake,inappropriate,offensive,spam,irrelevant,other',
            'report_description' => 'required|string|min:20|max:500'
        ]);
        
        // Vérifier qu'il n'y a pas déjà un signalement en cours
        if ($review->is_reported) {
            return back()->with('error', 'Cet avis a déjà été signalé.');
        }
        
        $review->update([
            'is_reported' => true,
            'report_reason' => $validated['report_reason'],
            'report_description' => $validated['report_description'],
            'reported_by' => 'prestataire',
            'reported_at' => now()
        ]);
        
        // TODO: Envoyer notification aux administrateurs
        
        return back()->with('success', 'Avis signalé. Nous examinerons votre demande.');
    }
    
    /**
     * Affiche les statistiques détaillées des avis
     */
    public function stats(Request $request)
    {
        $prestataireId = Auth::user()->prestataire->id;
        
        // Période pour les statistiques
        $period = $request->get('period', '12months');
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            'year' => now()->subYear(),
            default => now()->subMonths(12)
        };
        
        // Statistiques générales
        $totalReviews = EquipmentReview::where('prestataire_id', $prestataireId)->count();
        $averageRating = EquipmentReview::where('prestataire_id', $prestataireId)->avg('overall_rating');
        $totalHelpful = EquipmentReview::where('prestataire_id', $prestataireId)->sum('helpful_count');
        $responseRate = $this->calculateResponseRate($prestataireId);
        
        // Répartition par note
        $ratingDistribution = EquipmentReview::where('prestataire_id', $prestataireId)
                                           ->select('overall_rating', DB::raw('count(*) as count'))
                                           ->groupBy('overall_rating')
                                           ->orderBy('overall_rating', 'desc')
                                           ->pluck('count', 'overall_rating')
                                           ->toArray();
        
        // Évolution mensuelle
        $monthlyStats = EquipmentReview::where('prestataire_id', $prestataireId)
                                     ->where('created_at', '>=', $startDate)
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
        
        // Statistiques par équipement
        $equipmentStats = EquipmentReview::where('prestataire_id', $prestataireId)
                                        ->with('equipment')
                                        ->select(
                                            'equipment_id',
                                            DB::raw('count(*) as review_count'),
                                            DB::raw('avg(overall_rating) as avg_rating'),
                                            DB::raw('sum(helpful_count) as total_helpful')
                                        )
                                        ->groupBy('equipment_id')
                                        ->orderBy('review_count', 'desc')
                                        ->limit(10)
                                        ->get();
        
        // Statistiques par critère
        $criteriaStats = EquipmentReview::where('prestataire_id', $prestataireId)
                                       ->selectRaw('
                                           avg(overall_rating) as overall,
                                           avg(condition_rating) as condition,
                                           avg(performance_rating) as performance,
                                           avg(value_rating) as value,
                                           avg(service_rating) as service
                                       ')
                                       ->first();
        
        // Avis récents nécessitant une réponse
        $pendingResponses = EquipmentReview::where('prestataire_id', $prestataireId)
                                         ->whereNull('prestataire_response')
                                         ->where('status', 'approved')
                                         ->with(['equipment', 'client.user'])
                                         ->orderBy('created_at', 'desc')
                                         ->limit(5)
                                         ->get();
        
        // Mots-clés les plus fréquents dans les avis positifs/négatifs
        $positiveKeywords = $this->extractKeywords($prestataireId, [4, 5]);
        $negativeKeywords = $this->extractKeywords($prestataireId, [1, 2]);
        
        // Temps de réponse moyen
        $avgResponseTime = EquipmentReview::where('prestataire_id', $prestataireId)
                                         ->whereNotNull('responded_at')
                                         ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
                                         ->value('avg_hours');
        
        return view('prestataire.equipment.reviews.stats', compact(
            'totalReviews',
            'averageRating',
            'totalHelpful',
            'responseRate',
            'ratingDistribution',
            'monthlyStats',
            'equipmentStats',
            'criteriaStats',
            'pendingResponses',
            'positiveKeywords',
            'negativeKeywords',
            'avgResponseTime',
            'period'
        ));
    }
    
    /**
     * Calcule le taux de réponse aux avis
     */
    private function calculateResponseRate($prestataireId)
    {
        $totalApproved = EquipmentReview::where('prestataire_id', $prestataireId)
                                       ->where('status', 'approved')
                                       ->count();
        
        if ($totalApproved === 0) {
            return 0;
        }
        
        $totalResponded = EquipmentReview::where('prestataire_id', $prestataireId)
                                        ->where('status', 'approved')
                                        ->whereNotNull('prestataire_response')
                                        ->count();
        
        return round(($totalResponded / $totalApproved) * 100, 1);
    }
    
    /**
     * Extrait les mots-clés les plus fréquents des avis
     */
    private function extractKeywords($prestataireId, $ratings)
    {
        $reviews = EquipmentReview::where('prestataire_id', $prestataireId)
                                 ->whereIn('overall_rating', $ratings)
                                 ->pluck('review_content')
                                 ->implode(' ');
        
        // Nettoyage et extraction basique des mots-clés
        $words = str_word_count(strtolower($reviews), 1, 'àáâãäåæçèéêëìíîïñòóôõöøùúûüý');
        
        // Filtrer les mots trop courts et les mots vides
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'mais', 'donc', 'car', 'ni', 'or', 'ce', 'se', 'que', 'qui', 'quoi', 'dont', 'où', 'il', 'elle', 'on', 'nous', 'vous', 'ils', 'elles', 'je', 'tu', 'me', 'te', 'se', 'nous', 'vous', 'leur', 'leurs', 'son', 'sa', 'ses', 'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'notre', 'votre', 'dans', 'sur', 'avec', 'sans', 'pour', 'par', 'vers', 'chez', 'sous', 'entre', 'pendant', 'avant', 'après', 'depuis', 'jusqu', 'très', 'plus', 'moins', 'aussi', 'encore', 'déjà', 'toujours', 'jamais', 'souvent', 'parfois', 'bien', 'mal', 'mieux', 'pire', 'beaucoup', 'peu', 'assez', 'trop', 'tout', 'tous', 'toute', 'toutes', 'autre', 'autres', 'même', 'mêmes', 'chaque', 'plusieurs', 'quelques', 'certain', 'certains', 'certaine', 'certaines'];
        
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) >= 4 && !in_array($word, $stopWords);
        });
        
        $wordCounts = array_count_values($filteredWords);
        arsort($wordCounts);
        
        return array_slice($wordCounts, 0, 10, true);
    }
    
    /**
     * Exporte les avis en CSV
     */
    public function export(Request $request)
    {
        $query = EquipmentReview::where('prestataire_id', Auth::user()->prestataire->id)
                               ->with(['equipment', 'client.user', 'rental']);
        
        if ($request->filled('equipment')) {
            $query->where('equipment_id', $request->equipment);
        }
        
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
        
        $filename = 'avis_equipements_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($reviews) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Équipement',
                'Client',
                'Note globale',
                'Note condition',
                'Note performance',
                'Note rapport qualité/prix',
                'Note service',
                'Titre',
                'Commentaire',
                'Points positifs',
                'Points négatifs',
                'Recommande',
                'Réponse donnée',
                'Statut',
                'Date création',
                'Votes utiles'
            ]);
            
            foreach ($reviews as $review) {
                fputcsv($file, [
                    $review->equipment->name,
                    $review->client->user->name,
                    $review->overall_rating . '/5',
                    $review->condition_rating . '/5',
                    $review->performance_rating . '/5',
                    $review->value_rating . '/5',
                    $review->service_rating . '/5',
                    $review->review_title,
                    strip_tags($review->review_content),
                    $review->positive_points,
                    $review->negative_points,
                    $review->would_recommend ? 'Oui' : 'Non',
                    $review->prestataire_response ? 'Oui' : 'Non',
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