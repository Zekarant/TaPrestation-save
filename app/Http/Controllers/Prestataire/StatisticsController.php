<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Review;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:prestataire']);
    }

    /**
     * Affiche les statistiques du prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        // Vérifier que le prestataire est approuvé
        if (!$prestataire || !$prestataire->is_approved) {
            return redirect()->route('prestataire.dashboard')
                ->with('error', 'Vous devez être un prestataire validé pour accéder aux statistiques.');
        }

        // Période sélectionnée (par défaut: 30 derniers jours)
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays((int)$period);

        // Statistiques générales
        $generalStats = $this->getGeneralStats($user, $prestataire);
        
        // Statistiques de période
        $periodStats = $this->getPeriodStats($user, $prestataire, $startDate);
        

        
        // Répartition des avis par note
        $reviewsDistribution = $this->getReviewsDistribution($prestataire);
        
        // Services les plus populaires
        $popularServices = $this->getPopularServices($prestataire);
        
        // Statistiques de messagerie
        $messagingStats = $this->getMessagingStats($user, $startDate);

        return view('prestataire.statistics.index', [
            'generalStats' => $generalStats,
            'periodStats' => $periodStats,
            'reviewsDistribution' => $reviewsDistribution,
            'popularServices' => $popularServices,
            'messagingStats' => $messagingStats,
            'selectedPeriod' => $period
        ]);
    }

    /**
     * Récupère les statistiques générales du prestataire.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prestataire  $prestataire
     * @return array
     */
    private function getGeneralStats($user, $prestataire)
    {
        return [
            'total_services' => $prestataire->services()->count(),
            'active_services' => $prestataire->services()->where('status', 'active')->count(),
            'total_reviews' => $prestataire->reviews()->count(),
            'average_rating' => round($prestataire->reviews()->avg('rating') ?: 0, 2),
            'member_since' => $user->created_at->format('d/m/Y'),
            'profile_completion' => $this->calculateProfileCompletion($user, $prestataire)
        ];
    }

    /**
     * Récupère les statistiques pour une période donnée.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prestataire  $prestataire
     * @param  \Carbon\Carbon  $startDate
     * @return array
     */
    private function getPeriodStats($user, $prestataire, $startDate)
    {
        return [
            'new_reviews' => $prestataire->reviews()
                ->where('created_at', '>=', $startDate)
                ->count(),
            'messages_sent' => Message::where('sender_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->count(),
            'messages_received' => Message::where('receiver_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->count()
        ];
    }



    /**
     * Récupère la répartition des avis par note.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return array
     */
    private function getReviewsDistribution($prestataire)
    {
        $distribution = $prestataire->reviews()
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();

        // S'assurer que toutes les notes de 1 à 5 sont présentes
        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[$i] = $distribution[$i] ?? 0;
        }

        return $result;
    }

    /**
     * Récupère les services les plus populaires.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPopularServices($prestataire)
    {
        return $prestataire->services()
            ->withCount('reviews')
            ->orderBy('reviews_count', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Récupère les statistiques de messagerie.
     *
     * @param  \App\Models\User  $user
     * @param  \Carbon\Carbon  $startDate
     * @return array
     */
    private function getMessagingStats($user, $startDate)
    {
        $totalConversations = Message::where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->distinct()
            ->count(DB::raw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END', [$user->id]));

        $responseTime = Message::where('receiver_id', $user->id)
            ->whereNotNull('read_at')
            ->where('created_at', '>=', $startDate)
            ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, read_at)'));

        return [
            'total_conversations' => $totalConversations,
            'average_response_time' => $responseTime ? round($responseTime) : 0,
            'unread_messages' => Message::where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->count()
        ];
    }

    /**
     * Calcule le pourcentage de completion du profil.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Prestataire  $prestataire
     * @return int
     */
    private function calculateProfileCompletion($user, $prestataire)
    {
        $fields = [
            $user->name,
            $user->email,
            $prestataire->phone,
            $prestataire->description,
            $prestataire->sector,
            $prestataire->photo,
            $prestataire->skills()->count() > 0
        ];

        $completedFields = array_filter($fields, function($field) {
            return !empty($field);
        });

        return round((count($completedFields) / count($fields)) * 100);
    }
}