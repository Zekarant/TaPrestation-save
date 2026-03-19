<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
/**
 * Service de gestion des notes mutuelles (Client ↔ Prestataire)
 */
class MutualRatingService
{
    /**
     * Créer une note
     */
    public function createRating(
        int $raterId,
        int $ratedId,
        string $raterType, // 'client' ou 'prestataire'
        string $ratableType,
        int $ratableId,
        int $rating,
        ?string $comment = null,
        bool $wouldRecommend = true
    ): ?int {
        try {
            // Vérifier qu'on n'a pas déjà noté
            $exists = DB::table('mutual_ratings')
                ->where('rater_id', $raterId)
                ->where('ratable_type', $ratableType)
                ->where('ratable_id', $ratableId)
                ->exists();

            if ($exists) {
                return null;
            }

            $ratingId = DB::table('mutual_ratings')->insertGetId([
                'rater_id' => $raterId,
                'rated_id' => $ratedId,
                'rater_type' => $raterType,
                'ratable_type' => $ratableType,
                'ratable_id' => $ratableId,
                'rating' => min(5, max(1, $rating)),
                'comment' => $comment,
                'would_recommend' => $wouldRecommend,
                'is_visible' => true,
                'is_flagged' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mettre à jour la moyenne du noté
            $this->updateAverageRating($ratedId, $raterType === 'client' ? 'prestataire' : 'client');

            return $ratingId;
        } catch (\Exception $e) {
            Log::error("Erreur création note: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir les notes reçues par un utilisateur
     */
    public function getRatingsReceived(int $userId, string $userType, int $limit = 10): array
    {
        return DB::table('mutual_ratings')
            ->where('rated_id', $userId)
            ->where('rater_type', $userType === 'prestataire' ? 'client' : 'prestataire')
            ->where('is_visible', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Obtenir la moyenne des notes d'un utilisateur
     */
    public function getAverageRating(int $userId, string $asType): array
    {
        $raterType = $asType === 'prestataire' ? 'client' : 'prestataire';
        
        $stats = DB::table('mutual_ratings')
            ->where('rated_id', $userId)
            ->where('rater_type', $raterType)
            ->where('is_visible', true)
            ->selectRaw('
                AVG(rating) as average,
                COUNT(*) as count,
                SUM(CASE WHEN would_recommend = 1 THEN 1 ELSE 0 END) as recommend_count
            ')
            ->first();

        return [
            'average' => round($stats->average ?? 0, 1),
            'count' => $stats->count ?? 0,
            'recommend_percent' => $stats->count > 0 
                ? round(($stats->recommend_count / $stats->count) * 100) 
                : 0,
        ];
    }

    /**
     * Mettre à jour la moyenne stockée
     */
    protected function updateAverageRating(int $userId, string $userType): void
    {
        $stats = $this->getAverageRating($userId, $userType);

        if ($userType === 'prestataire') {
            DB::table('prestataires')
                ->where('user_id', $userId)
                ->update([
                    'rating_average' => $stats['average'],
                    'total_reviews' => $stats['count'],
                ]);
        } elseif ($userType === 'client') {
            // Si on a une colonne rating sur clients
            if (\Schema::hasColumn('clients', 'rating_average')) {
                DB::table('clients')
                    ->where('user_id', $userId)
                    ->update([
                        'rating_average' => $stats['average'],
                        'total_reviews' => $stats['count'],
                    ]);
            }
        }
    }

    /**
     * Vérifier si une note est possible (transaction terminée)
     */
    public function canRate(int $userId, string $ratableType, int $ratableId): bool
    {
        try {
            // Vérifier que la table existe
            if (!TableExistenceCache::has('mutual_ratings')) {
                return false;
            }

            // Vérifier qu'on n'a pas déjà noté
            $alreadyRated = DB::table('mutual_ratings')
                ->where('rater_id', $userId)
                ->where('ratable_type', $ratableType)
                ->where('ratable_id', $ratableId)
                ->exists();

            if ($alreadyRated) {
                return false;
            }

            // Vérifier que la transaction est terminée
            $table = match(true) {
                str_contains($ratableType, 'Booking') => 'bookings',
                str_contains($ratableType, 'EquipmentRental') => 'equipment_rental_requests',
                str_contains($ratableType, 'UrgentSale') => 'urgent_sale_purchases',
                str_contains($ratableType, 'FoodOrder') => 'food_orders',
                default => null,
            };

            if (!$table) {
                // Si type inconnu mais qu'on arrive ici, permettre la notation
                return true;
            }

            $record = DB::table($table)->find($ratableId);
            
            if (!$record) {
                return false;
            }

            // Vérifier le statut - élargir la liste des statuts valides
            $completedStatuses = ['completed', 'confirmed', 'delivered', 'released', 'finished', 'done'];
            return in_array($record->status ?? '', $completedStatuses);
        } catch (\Exception $e) {
            \Log::warning('canRate error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Signaler une note (abusive)
     */
    public function flagRating(int $ratingId, string $reason): bool
    {
        try {
            DB::table('mutual_ratings')->where('id', $ratingId)->update([
                'is_flagged' => true,
                'updated_at' => now(),
            ]);

            // Log pour modération
            Log::info("Note #{$ratingId} signalée: {$reason}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir les notes en attente de modération
     */
    public function getFlaggedRatings(): array
    {
        return DB::table('mutual_ratings')
            ->where('is_flagged', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Modérer une note (admin)
     */
    public function moderateRating(int $ratingId, bool $approve): bool
    {
        try {
            if ($approve) {
                DB::table('mutual_ratings')->where('id', $ratingId)->update([
                    'is_flagged' => false,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('mutual_ratings')->where('id', $ratingId)->update([
                    'is_visible' => false,
                    'is_flagged' => false,
                    'updated_at' => now(),
                ]);
                
                // Recalculer la moyenne
                $rating = DB::table('mutual_ratings')->find($ratingId);
                if ($rating) {
                    $userType = $rating->rater_type === 'client' ? 'prestataire' : 'client';
                    $this->updateAverageRating($rating->rated_id, $userType);
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
