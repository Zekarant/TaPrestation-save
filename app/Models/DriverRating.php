<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestataire_id',
        'driver_id',
        'food_order_id',
        'rating',
        'punctuality_rating',
        'professionalism_rating',
        'care_rating',
        'comment',
        'is_public',
    ];

    protected $casts = [
        'rating' => 'integer',
        'punctuality_rating' => 'integer',
        'professionalism_rating' => 'integer',
        'care_rating' => 'integer',
        'is_public' => 'boolean',
    ];

    /**
     * Get the prestataire who gave this rating
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Get the rated driver
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    /**
     * Get the related food order
     */
    public function foodOrder(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class);
    }

    /**
     * Calculate average rating from all sub-ratings
     */
    public function getAverageRatingAttribute(): float
    {
        $ratings = array_filter([
            $this->rating,
            $this->punctuality_rating,
            $this->professionalism_rating,
            $this->care_rating,
        ]);

        return count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
    }

    /**
     * Get driver's average rating from a specific prestataire
     */
    public static function getAverageForDriverFromPrestataire(int $driverId, int $prestataireId): ?float
    {
        $avg = self::where('driver_id', $driverId)
            ->where('prestataire_id', $prestataireId)
            ->avg('rating');

        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get driver's overall average rating from all prestataires
     */
    public static function getOverallAverageForDriver(int $driverId): ?float
    {
        $avg = self::where('driver_id', $driverId)->avg('rating');
        return $avg ? round($avg, 1) : null;
    }
}
