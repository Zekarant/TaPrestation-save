<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPricing extends Model
{
    use HasFactory;

    protected $table = 'driver_pricing';

    protected $fillable = [
        'driver_id',
        'base_fee',
        'fee_per_km',
        'min_order_value',
        'surge_multiplier',
        'surge_hours',
        'zone_pricing',
        'accepts_tips',
        'is_active',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'fee_per_km' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'surge_multiplier' => 'decimal:2',
        'surge_hours' => 'json',
        'zone_pricing' => 'json',
        'accepts_tips' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the driver
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    /**
     * Calculate delivery fee for a distance
     */
    public function calculateFee(float $distanceKm): float
    {
        $baseFee = $this->base_fee ?? 3.00;
        $feePerKm = $this->fee_per_km ?? 0.50;
        $multiplier = $this->getCurrentMultiplier();

        $fee = ($baseFee + ($distanceKm * $feePerKm)) * $multiplier;

        return round($fee, 2);
    }

    /**
     * Get current surge multiplier based on time
     */
    public function getCurrentMultiplier(): float
    {
        if (!$this->surge_hours || $this->surge_multiplier <= 1) {
            return 1.0;
        }

        $currentHour = now()->hour;
        $surgeHours = $this->surge_hours;

        // Check if current hour is in surge hours
        if (is_array($surgeHours)) {
            foreach ($surgeHours as $range) {
                $start = $range['start'] ?? 0;
                $end = $range['end'] ?? 0;

                if ($currentHour >= $start && $currentHour < $end) {
                    return $this->surge_multiplier;
                }
            }
        }

        return 1.0;
    }

    /**
     * Get or create pricing for a driver
     */
    public static function getOrCreateForDriver(int $driverId): self
    {
        return self::firstOrCreate(
            ['driver_id' => $driverId],
            [
                'base_fee' => 3.00,
                'fee_per_km' => 0.50,
                'surge_multiplier' => 1.00,
                'accepts_tips' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Get fee preview for different distances
     */
    public function getFeePreview(): array
    {
        return [
            '1km' => $this->calculateFee(1),
            '3km' => $this->calculateFee(3),
            '5km' => $this->calculateFee(5),
            '10km' => $this->calculateFee(10),
            '15km' => $this->calculateFee(15),
        ];
    }
}
