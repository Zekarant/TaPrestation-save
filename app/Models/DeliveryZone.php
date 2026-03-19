<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'cities',
        'postal_codes',
        'min_lat',
        'max_lat',
        'min_lng',
        'max_lng',
        'base_delivery_fee',
        'per_km_fee',
        'min_order_amount',
        'free_delivery_threshold',
        'estimated_delivery_days',
        'express_available',
        'express_surcharge',
        'is_active',
        'priority',
        'working_hours',
        'metadata',
    ];

    protected $casts = [
        'cities' => 'json',
        'postal_codes' => 'json',
        'working_hours' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'express_available' => 'boolean',
        'base_delivery_fee' => 'decimal:2',
        'per_km_fee' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
        'express_surcharge' => 'decimal:2',
        'min_lat' => 'decimal:8',
        'max_lat' => 'decimal:8',
        'min_lng' => 'decimal:8',
        'max_lng' => 'decimal:8',
    ];

    /**
     * Get drivers assigned to this zone
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(DeliveryDriver::class, 'zone_id');
    }

    /**
     * Check if coordinates are within this zone
     */
    public function containsCoordinates(float $lat, float $lng): bool
    {
        return $lat >= $this->min_lat && $lat <= $this->max_lat
            && $lng >= $this->min_lng && $lng <= $this->max_lng;
    }

    /**
     * Check if a postal code is covered
     */
    public function coversPostalCode(string $postalCode): bool
    {
        if (empty($this->postal_codes)) {
            return true; // No restrictions
        }
        
        return in_array($postalCode, $this->postal_codes);
    }

    /**
     * Calculate delivery fee for this zone
     */
    public function calculateDeliveryFee(float $distance, float $orderAmount = 0, bool $express = false): float
    {
        // Free delivery if threshold is met
        if ($this->free_delivery_threshold && $orderAmount >= $this->free_delivery_threshold) {
            return $express ? $this->express_surcharge : 0;
        }

        $baseFee = $this->base_delivery_fee ?? 5.00;
        $perKmFee = $this->per_km_fee ?? 0.50;
        
        $fee = $baseFee + ($distance * $perKmFee);
        
        if ($express && $this->express_available) {
            $fee += $this->express_surcharge ?? 5.00;
        }
        
        return round($fee, 2);
    }

    /**
     * Scope for active zones
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Find zone for given coordinates
     */
    public static function findForCoordinates(float $lat, float $lng): ?self
    {
        return self::active()
            ->where('min_lat', '<=', $lat)
            ->where('max_lat', '>=', $lat)
            ->where('min_lng', '<=', $lng)
            ->where('max_lng', '>=', $lng)
            ->ordered()
            ->first();
    }

    /**
     * Find zone by postal code
     */
    public static function findByPostalCode(string $postalCode): ?self
    {
        return self::active()
            ->whereJsonContains('postal_codes', $postalCode)
            ->ordered()
            ->first();
    }
}
