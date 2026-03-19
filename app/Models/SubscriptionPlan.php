<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'billing_period',
        'features',
        'stripe_price_id',
        'is_active',
        'is_featured',
        'booking_limit',
        'max_bookings_per_month',
        'max_listings',
        'commission_rate',
        'includes_analytics',
        'includes_priority_support',
    ];

    protected $casts = [
        'features' => 'json',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'includes_analytics' => 'boolean',
        'includes_priority_support' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get billing period label in French
     */
    public function getBillingPeriodLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'weekly' => 'semaine',
            'monthly' => 'mois',
            'quarterly' => 'trimestre',
            'annual' => 'an',
            default => $this->billing_cycle ?? 'mois',
        };
    }

    /**
     * Get duration in days
     */
    public function getDurationInDaysAttribute(): int
    {
        return match($this->billing_cycle) {
            'weekly' => 7,
            'monthly' => 30,
            'quarterly' => 90,
            'annual' => 365,
            default => 30,
        };
    }

    public static function basic()
    {
        return self::where('slug', 'basic')->first();
    }

    public static function professional()
    {
        return self::where('slug', 'professional')->first();
    }

    public static function enterprise()
    {
        return self::where('slug', 'enterprise')->first();
    }
}
