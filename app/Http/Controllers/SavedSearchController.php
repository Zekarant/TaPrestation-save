<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\SavedSearch;
use App\Models\Prestataire;
use App\Models\MatchingAlert;
use App\Http\Controllers\SearchController;

class SavedSearchController extends Controller
{
    /**
     * Affiche la liste des recherches sauvegardées de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $savedSearches = SavedSearch::where('user_id', $user->id)
            ->with('matchingAlerts')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('saved-searches.index', compact('savedSearches'));
    }

    /**
     * Sauvegarde une nouvelle recherche.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'search_criteria' => 'required|array',
            'alert_frequency' => 'required|in:' . implode(',', array_keys(SavedSearch::ALERT_FREQUENCIES)),
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $savedSearch = SavedSearch::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'search_criteria' => $request->search_criteria,
                'alert_frequency' => $request->alert_frequency,
                'is_active' => $request->get('is_active', true)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recherche sauvegardée avec succès',
                'data' => $savedSearch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde de la recherche'
            ], 500);
        }
    }

    /**
     * Affiche une recherche sauvegardée spécifique.
     *
     * @param  \App\Models\SavedSearch  $savedSearch
     * @return \Illuminate\Http\Response
     */
    public function show(SavedSearch $savedSearch)
    {
        $this->authorize('view', $savedSearch);
        
        $savedSearch->load('matchingAlerts.prestataire.user');
        
        // Exécuter la recherche avec les critères sauvegardés
        $searchController = new SearchController();
        $searchRequest = new Request($savedSearch->search_criteria);
        $results = $searchController->searchPrestataires($searchRequest);
        
        return view('saved-searches.show', compact('savedSearch', 'results'));
    }

    /**
     * Met à jour une recherche sauvegardée.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SavedSearch  $savedSearch
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SavedSearch $savedSearch)
    {
        $this->authorize('update', $savedSearch);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'search_criteria' => 'sometimes|required|array',
            'alert_frequency' => 'sometimes|required|in:' . implode(',', array_keys(SavedSearch::ALERT_FREQUENCIES)),
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $savedSearch->update($request->only([
                'name', 'search_criteria', 'alert_frequency', 'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Recherche mise à jour avec succès',
                'data' => $savedSearch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la recherche'
            ], 500);
        }
    }

    /**
     * Supprime une recherche sauvegardée.
     *
     * @param  \App\Models\SavedSearch  $savedSearch
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SavedSearch $savedSearch)
    {
        $this->authorize('delete', $savedSearch);
        
        try {
            // Supprimer les alertes associées
            $savedSearch->matchingAlerts()->delete();
            
            // Supprimer la recherche
            $savedSearch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recherche supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la recherche'
            ], 500);
        }
    }

    /**
     * Active ou désactive les alertes pour une recherche.
     *
     * @param  \App\Models\SavedSearch  $savedSearch
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAlerts(SavedSearch $savedSearch)
    {
        $this->authorize('update', $savedSearch);
        
        try {
            $savedSearch->is_active = !$savedSearch->is_active;
            $savedSearch->save();

            $message = $savedSearch->is_active 
                ? 'Alertes activées pour cette recherche'
                : 'Alertes désactivées pour cette recherche';

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $savedSearch->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification des alertes'
            ], 500);
        }
    }

    /**
     * Exécute une recherche sauvegardée et retourne les résultats.
     *
     * @param  \App\Models\SavedSearch  $savedSearch
     * @return \Illuminate\Http\JsonResponse
     */
    public function runSearch(SavedSearch $savedSearch)
    {
        $this->authorize('view', $savedSearch);
        
        try {
            $searchController = new SearchController();
            $searchRequest = new Request($savedSearch->search_criteria);
            $results = $searchController->searchPrestataires($searchRequest);
            
            // Créer des alertes pour les nouveaux résultats
            $this->createMatchingAlerts($savedSearch, $results->getData());
            
            return response()->json([
                'success' => true,
                'data' => $results->getData(),
                'search_url' => $savedSearch->search_url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'exécution de la recherche'
            ], 500);
        }
    }

    /**
     * Crée des alertes de correspondance pour les nouveaux résultats.
     *
     * @param  \App\Models\SavedSearch  $savedSearch
     * @param  array  $results
     * @return void
     */
    private function createMatchingAlerts(SavedSearch $savedSearch, $results)
    {
        if (!$savedSearch->is_active || empty($results)) {
            return;
        }

        foreach ($results as $prestataire) {
            // Vérifier si une alerte existe déjà pour ce prestataire
            $existingAlert = MatchingAlert::where('saved_search_id', $savedSearch->id)
                ->where('prestataire_id', $prestataire['id'])
                ->first();

            if (!$existingAlert) {
                // Calculer un score de correspondance basique
                $matchingScore = $this->calculateMatchingScore($savedSearch->search_criteria, $prestataire);
                
                if ($matchingScore >= 0.5) { // Seuil minimum de 50%
                    MatchingAlert::create([
                        'saved_search_id' => $savedSearch->id,
                        'prestataire_id' => $prestataire['id'],
                        'matching_score' => $matchingScore,
                        'alert_data' => [
                            'prestataire_name' => $prestataire['name'] ?? '',
                            'services' => $prestataire['services'] ?? [],
                            'location' => $prestataire['location'] ?? [],
                            'rating' => $prestataire['rating'] ?? 0,
                            'matched_criteria' => $this->getMatchedCriteria($savedSearch->search_criteria, $prestataire)
                        ]
                    ]);
                }
            }
        }

        // Marquer la recherche comme ayant envoyé une alerte
        $savedSearch->markAlertSent();
    }

    /**
     * Calcule un score de correspondance entre les critères et un prestataire.
     *
     * @param  array  $criteria
     * @param  array  $prestataire
     * @return float
     */
    private function calculateMatchingScore($criteria, $prestataire)
    {
        $score = 0;
        $totalCriteria = 0;

        // Score basé sur les services
        if (isset($criteria['service_id']) && isset($prestataire['services'])) {
            $totalCriteria++;
            $serviceIds = is_array($prestataire['services']) 
                ? array_column($prestataire['services'], 'id')
                : [];
            
            if (in_array($criteria['service_id'], $serviceIds)) {
                $score += 0.4; // 40% du score pour le service
            }
        }

        // Score basé sur la localisation
        if (isset($criteria['city']) && isset($prestataire['location']['city'])) {
            $totalCriteria++;
            if (strtolower($criteria['city']) === strtolower($prestataire['location']['city'])) {
                $score += 0.3; // 30% du score pour la ville
            }
        }

        // Score basé sur la note
        if (isset($criteria['min_rating']) && isset($prestataire['rating'])) {
            $totalCriteria++;
            if ($prestataire['rating'] >= $criteria['min_rating']) {
                $score += 0.2; // 20% du score pour la note
            }
        }

        // Score basé sur le budget - SUPPRIMÉ pour confidentialité
        // Logique de calcul du score basé sur le budget supprimée pour des raisons de confidentialité

        return $totalCriteria > 0 ? $score : 0;
    }

    /**
     * Retourne les critères qui correspondent au prestataire.
     *
     * @param  array  $criteria
     * @param  array  $prestataire
     * @return array
     */
    private function getMatchedCriteria($criteria, $prestataire)
    {
        $matched = [];

        if (isset($criteria['service_id']) && isset($prestataire['services'])) {
            $serviceIds = is_array($prestataire['services']) 
                ? array_column($prestataire['services'], 'id')
                : [];
            
            if (in_array($criteria['service_id'], $serviceIds)) {
                $matched[] = 'service';
            }
        }

        if (isset($criteria['city']) && isset($prestataire['location']['city'])) {
            if (strtolower($criteria['city']) === strtolower($prestataire['location']['city'])) {
                $matched[] = 'location';
            }
        }

        if (isset($criteria['min_rating']) && isset($prestataire['rating'])) {
            if ($prestataire['rating'] >= $criteria['min_rating']) {
                $matched[] = 'rating';
            }
        }

        // Critère de budget supprimé pour confidentialité
        // if (isset($criteria['max_budget']) && isset($prestataire['price_range'])) {
        //     if ($prestataire['price_range']['min'] <= $criteria['max_budget']) {
        //         $matched[] = 'budget';
        //     }
        // }

        return $matched;
    }
}