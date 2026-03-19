<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MatchingAlert;
use App\Models\SavedSearch;

class MatchingAlertController extends Controller
{
    /**
     * Affiche la liste des alertes de correspondance de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = MatchingAlert::whereHas('savedSearch', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['savedSearch', 'prestataire.user'])
        ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->has('status')) {
            switch ($request->status) {
                case 'unread':
                    $query->unread();
                    break;
                case 'read':
                    $query->where('is_read', true);
                    break;
                case 'dismissed':
                    $query->where('is_dismissed', true);
                    break;
                case 'new':
                    $query->new();
                    break;
            }
        }

        if ($request->has('match_level')) {
            $query->byMatchLevel($request->match_level);
        }

        if ($request->has('min_score')) {
            $query->withMinimumScore($request->min_score);
        }

        if ($request->has('saved_search_id')) {
            $query->where('saved_search_id', $request->saved_search_id);
        }

        $alerts = $query->paginate(15);
        
        // Statistiques pour le dashboard
        $stats = $this->getAlertStats($user);

        return view('matching-alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Affiche une alerte de correspondance spécifique.
     *
     * @param  \App\Models\MatchingAlert  $alert
     * @return \Illuminate\Http\Response
     */
    public function show(MatchingAlert $alert)
    {
        $this->authorize('view', $alert);
        
        // Marquer comme lu automatiquement
        if (!$alert->is_read) {
            $alert->markAsRead();
        }
        
        $alert->load(['savedSearch', 'prestataire.user', 'prestataire.services']);
        
        return view('matching-alerts.show', compact('alert'));
    }

    /**
     * Marque une alerte comme lue.
     *
     * @param  \App\Models\MatchingAlert  $alert
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(MatchingAlert $alert)
    {
        $this->authorize('update', $alert);
        
        try {
            $alert->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Alerte marquée comme lue'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'alerte'
            ], 500);
        }
    }

    /**
     * Rejette une alerte (la marque comme ignorée).
     *
     * @param  \App\Models\MatchingAlert  $alert
     * @return \Illuminate\Http\JsonResponse
     */
    public function dismiss(MatchingAlert $alert)
    {
        $this->authorize('update', $alert);
        
        try {
            $alert->dismiss();

            return response()->json([
                'success' => true,
                'message' => 'Alerte ignorée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'alerte'
            ], 500);
        }
    }

    /**
     * Supprime une alerte.
     *
     * @param  \App\Models\MatchingAlert  $alert
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(MatchingAlert $alert)
    {
        $this->authorize('delete', $alert);
        
        try {
            $alert->delete();

            return response()->json([
                'success' => true,
                'message' => 'Alerte supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'alerte'
            ], 500);
        }
    }

    /**
     * Retourne les statistiques des alertes pour l'utilisateur.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $user = Auth::user();
        $stats = $this->getAlertStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Calcule les statistiques des alertes pour un utilisateur.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    private function getAlertStats($user)
    {
        $baseQuery = MatchingAlert::whereHas('savedSearch', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        return [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->unread()->count(),
            'new' => (clone $baseQuery)->new()->count(),
            'dismissed' => (clone $baseQuery)->where('is_dismissed', true)->count(),
            'high_match' => (clone $baseQuery)->byMatchLevel('high')->count(),
            'medium_match' => (clone $baseQuery)->byMatchLevel('medium')->count(),
            'low_match' => (clone $baseQuery)->byMatchLevel('low')->count(),
            'this_week' => (clone $baseQuery)->where('created_at', '>=', now()->subWeek())->count(),
            'this_month' => (clone $baseQuery)->where('created_at', '>=', now()->subMonth())->count(),
            'average_score' => (clone $baseQuery)->avg('matching_score') ?? 0,
            'top_searches' => $this->getTopSavedSearches($user),
            'recent_activity' => $this->getRecentActivity($user)
        ];
    }

    /**
     * Retourne les recherches sauvegardées les plus actives.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    private function getTopSavedSearches($user)
    {
        return SavedSearch::where('user_id', $user->id)
            ->withCount('matchingAlerts')
            ->orderBy('matching_alerts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($search) {
                return [
                    'id' => $search->id,
                    'name' => $search->name,
                    'alerts_count' => $search->matching_alerts_count,
                    'is_active' => $search->is_active,
                    'alert_frequency' => $search->alert_frequency_name
                ];
            })
            ->toArray();
    }

    /**
     * Retourne l'activité récente des alertes.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    private function getRecentActivity($user)
    {
        return MatchingAlert::whereHas('savedSearch', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['savedSearch', 'prestataire.user'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function ($alert) {
            return [
                'id' => $alert->id,
                'type' => 'new_match',
                'message' => "Nouveau prestataire trouvé pour '{$alert->savedSearch->name}'",
                'prestataire_name' => $alert->prestataire->user->name ?? 'N/A',
                'matching_score' => $alert->formatted_score,
                'match_level' => $alert->match_level,
                'created_at' => $alert->created_at->diffForHumans(),
                'is_read' => $alert->is_read,
                'is_new' => $alert->is_new
            ];
        })
        ->toArray();
    }

    /**
     * Marque toutes les alertes non lues comme lues.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        try {
            MatchingAlert::whereHas('savedSearch', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Toutes les alertes ont été marquées comme lues'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des alertes'
            ], 500);
        }
    }

    /**
     * Supprime toutes les alertes ignorées.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearDismissed()
    {
        $user = Auth::user();
        
        try {
            $count = MatchingAlert::whereHas('savedSearch', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('is_dismissed', true)
            ->delete();

            return response()->json([
                'success' => true,
                'message' => "$count alertes ignorées ont été supprimées",
                'deleted_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des alertes'
            ], 500);
        }
    }

    /**
     * Exporte les alertes au format CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $alerts = MatchingAlert::whereHas('savedSearch', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['savedSearch', 'prestataire.user'])
        ->orderBy('created_at', 'desc')
        ->get();

        $filename = 'alertes_correspondance_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function () use ($alerts) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Recherche sauvegardée',
                'Prestataire',
                'Score de correspondance',
                'Niveau de correspondance',
                'Statut',
                'Date de création',
                'Date de lecture'
            ]);

            // Données
            foreach ($alerts as $alert) {
                fputcsv($file, [
                    $alert->id,
                    $alert->savedSearch->name,
                    $alert->prestataire->user->name ?? 'N/A',
                    $alert->formatted_score,
                    $alert->match_level_name,
                    $alert->is_read ? 'Lu' : 'Non lu',
                    $alert->created_at->format('Y-m-d H:i:s'),
                    $alert->read_at ? $alert->read_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}