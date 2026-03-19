<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryProvider extends Model
{
    protected $fillable = [
        'name',
        'code',
        'api_key',
        'api_url',
        'coverage_areas',
        'base_rate',
        'per_km_rate',
        'delivery_type',
        'estimated_days',
        'is_active',
        'features',
        'contact_info',
        'metadata',
    ];

    protected $casts = [
        'coverage_areas' => 'json',
        'features' => 'json',
        'contact_info' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'base_rate' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'delivery_provider_id');
    }

    // Popular providers
    public static function colissimo() { return self::where('code', 'colissimo')->first(); }
    public static function chronopost() { return self::where('code', 'chronopost')->first(); }
    public static function dpd() { return self::where('code', 'dpd')->first(); }
    public static function fedex() { return self::where('code', 'fedex')->first(); }
}
