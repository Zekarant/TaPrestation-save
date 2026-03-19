<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverClientRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'driver_id',
        'food_order_id',
        'rating',
        'comment',
        'anonymous',
    ];

    protected $casts = [
        'rating' => 'integer',
        'anonymous' => 'boolean',
    ];

    /**
     * Get the client who gave this rating
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
     * Get average rating for a driver from clients
     */
    public static function getAverageForDriver(int $driverId): ?float
    {
        $avg = self::where('driver_id', $driverId)->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get rating count for a driver
     */
    public static function getCountForDriver(int $driverId): int
    {
        return self::where('driver_id', $driverId)->count();
    }

    /**
     * Check if client already rated this order
     */
    public static function hasClientRatedOrder(int $clientId, int $foodOrderId): bool
    {
        return self::where('client_id', $clientId)
            ->where('food_order_id', $foodOrderId)
            ->exists();
    }
}
