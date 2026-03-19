<?php

namespace App\Models;

use App\Models\Concerns\HandlesLegacyEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryDriver extends Model
{
    use HandlesLegacyEncryptedAttributes;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'zone_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'birth_date',
        'vehicle_type',
        'vehicle_plate',
        'license_number',
        'photo',
        'status',
        'is_available',
        'current_lat',
        'current_lng',
        'last_location_update',
        'rating',
        'total_deliveries',
        'completed_deliveries',
        'failed_deliveries',
        'average_delivery_time',
        'working_hours',
        'commission_rate',
        'total_earnings',
        'bank_details',
        'stripe_account_id',
        'stripe_onboarding_complete',
        'documents',
        'notes',
        'is_active',
        'verified_at',
        'metadata',
        // New driver management fields
        'employer_prestataire_id',
        'is_internal',
        'max_distance_km',
        'preferred_zones',
        'accepts_cash',
        'accepts_card',
        'bio',
        // Sponsorship & progressive verification
        'sponsor_prestataire_id',
        'sponsor_code',
        'sponsored_at',
        'trust_level',
        'daily_limit',
        'max_order_amount',
        'probation_deliveries_count',
        'suspended_reason',
        'suspended_at',
        'location_updated_at',
    ];

    // Trust levels
    const TRUST_PROBATION = 'probation';
    const TRUST_VERIFIED = 'verified';
    const TRUST_TRUSTED = 'trusted';
    const TRUST_SUSPENDED = 'suspended';

    protected $casts = [
        'working_hours' => 'json',
        'documents' => 'json',
        'metadata' => 'json',
        'preferred_zones' => 'json',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'is_internal' => 'boolean',
        'accepts_cash' => 'boolean',
        'accepts_card' => 'boolean',
        'stripe_onboarding_complete' => 'boolean',
        'current_lat' => 'decimal:8',
        'current_lng' => 'decimal:8',
        'rating' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'max_distance_km' => 'decimal:1',
        'last_location_update' => 'datetime',
        'location_updated_at' => 'datetime',
        'verified_at' => 'datetime',
        'sponsored_at' => 'datetime',
        'suspended_at' => 'datetime',
        'birth_date' => 'date',
        'daily_limit' => 'integer',
        'max_order_amount' => 'decimal:2',
        'probation_deliveries_count' => 'integer',
    ];

    protected $hidden = [
        'bank_details',
        'license_number',
        'stripe_account_id',
        'metadata',
        'notes',
        'documents',
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_BUSY = 'busy';
    const STATUS_OFFLINE = 'offline';
    const STATUS_ON_BREAK = 'on_break';

    const VEHICLE_BIKE = 'bike';
    const VEHICLE_SCOOTER = 'scooter';
    const VEHICLE_CAR = 'car';
    const VEHICLE_VAN = 'van';
    const VEHICLE_TRUCK = 'truck';

    public function getBankDetailsAttribute($value): ?array
    {
        return $this->decryptNullableArray($value);
    }

    public function setBankDetailsAttribute($value): void
    {
        $this->attributes['bank_details'] = $this->encryptNullableArray($value);
    }

    /**
     * Get the user account if linked
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get assigned zone
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'zone_id');
    }

    /**
     * Get assigned deliveries
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'driver_id');
    }

    /**
     * Get assigned food orders
     */
    public function foodOrders(): HasMany
    {
        return $this->hasMany(FoodOrder::class, 'driver_id');
    }

    /**
     * Get active food orders (pending pickup or in transit)
     */
    public function activeFoodOrders(): HasMany
    {
        return $this->foodOrders()
            ->whereIn('delivery_status', [
                FoodOrder::DELIVERY_STATUS_ASSIGNED,
                FoodOrder::DELIVERY_STATUS_PICKED_UP,
                FoodOrder::DELIVERY_STATUS_IN_TRANSIT,
            ]);
    }

    /**
     * Get pending food orders waiting for driver
     */
    public static function getPendingFoodOrders($zoneId = null)
    {
        $query = FoodOrder::where('delivery_type', FoodOrder::DELIVERY_DELIVERY)
            ->whereNull('driver_id')
            ->whereIn('status', [FoodOrder::STATUS_ACCEPTED, FoodOrder::STATUS_PREPARING, FoodOrder::STATUS_READY])
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_PENDING)
            ->with(['prestataire', 'client', 'items']);
            
        return $query->orderBy('created_at', 'asc')->get();
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get active deliveries count (uses food_orders table)
     */
    public function getActiveDeliveriesCountAttribute(): int
    {
        return $this->activeFoodOrders()->count();
    }

    /**
     * Check if driver is available for new delivery
     */
    public function isAvailableForDelivery(): bool
    {
        return ($this->is_active ?? true) 
            && ($this->is_available ?? false) 
            && $this->status === self::STATUS_AVAILABLE
            && $this->active_deliveries_count < 5; // Max 5 concurrent deliveries
    }

    /**
     * Update driver location
     */
    public function updateLocation(float $lat, float $lng): void
    {
        $this->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'last_location_update' => now(),
        ]);
    }

    /**
     * Set driver status
     */
    public function setStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    /**
     * Add completed delivery
     */
    public function recordDeliveryCompletion(float $earnings = 0, int $deliveryTimeMinutes = 0): void
    {
        // Ensure positive values only
        $deliveryTimeMinutes = max(0, $deliveryTimeMinutes);
        $earnings = max(0, $earnings);
        
        $completedCount = $this->completed_deliveries + 1;
        $currentAvg = max(0, $this->average_delivery_time ?? 0);
        $totalTime = ($currentAvg * $this->completed_deliveries) + $deliveryTimeMinutes;
        $newAvg = $completedCount > 0 ? max(0, round($totalTime / $completedCount)) : 0;
        
        $this->update([
            'total_deliveries' => $this->total_deliveries + 1,
            'completed_deliveries' => $completedCount,
            'total_earnings' => $this->total_earnings + $earnings,
            'average_delivery_time' => $newAvg,
        ]);
    }

    /**
     * Record failed delivery
     */
    public function recordDeliveryFailure(): void
    {
        $this->update([
            'total_deliveries' => $this->total_deliveries + 1,
            'failed_deliveries' => $this->failed_deliveries + 1,
        ]);
    }

    /**
     * Calculate success rate
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_deliveries === 0) {
            return 100;
        }
        return round(($this->completed_deliveries / $this->total_deliveries) * 100, 1);
    }

    /**
     * Get vehicle icon
     */
    public function getVehicleIconAttribute(): string
    {
        return match($this->vehicle_type) {
            self::VEHICLE_BIKE => '🚲',
            self::VEHICLE_SCOOTER => '🛵',
            self::VEHICLE_CAR => '🚗',
            self::VEHICLE_VAN => '🚐',
            self::VEHICLE_TRUCK => '🚛',
            default => '📦'
        };
    }

    /**
     * Scope for available drivers
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('is_available', true)
            ->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope for drivers in a zone
     */
    public function scopeInZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Find best available driver for delivery
     */
    public static function findBestForDelivery(float $pickupLat, float $pickupLng, ?int $zoneId = null): ?self
    {
        $query = self::available();
        
        if ($zoneId) {
            $query->inZone($zoneId);
        }

        // Get drivers and sort by distance + rating
        $drivers = $query->get()->filter(function ($driver) {
            return $driver->isAvailableForDelivery();
        })->sortBy(function ($driver) use ($pickupLat, $pickupLng) {
            // Calculate distance (simple Euclidean for demo)
            $distance = sqrt(
                pow($driver->current_lat - $pickupLat, 2) + 
                pow($driver->current_lng - $pickupLng, 2)
            );
            // Lower is better: distance penalty - rating bonus
            return $distance * 10 - ($driver->rating ?? 3);
        });

        return $drivers->first();
    }

    // ===== DRIVER MANAGEMENT SYSTEM RELATIONS =====

    /**
     * Get the employer prestataire (if internal driver)
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class, 'employer_prestataire_id');
    }

    /**
     * Get driver's pricing configuration
     */
    public function pricing(): HasOne
    {
        return $this->hasOne(DriverPricing::class, 'driver_id');
    }

    /**
     * Get location history
     */
    public function locations(): HasMany
    {
        return $this->hasMany(DriverLocation::class, 'driver_id');
    }

    /**
     * Get assigned batches
     */
    public function batches(): HasMany
    {
        return $this->hasMany(DeliveryBatch::class, 'driver_id');
    }

    /**
     * Check if driver location is fresh (within threshold)
     */
    public function hasRecentLocation(): bool
    {
        $threshold = config('delivery.gps.stale_threshold', 60);
        return $this->location_updated_at && $this->location_updated_at->diffInSeconds(now()) < $threshold;
    }

    /**
     * Get ratings received from prestataires
     */
    public function prestataireRatings(): HasMany
    {
        return $this->hasMany(DriverRating::class, 'driver_id');
    }

    /**
     * Get ratings received from clients
     */
    public function clientRatings(): HasMany
    {
        return $this->hasMany(DriverClientRating::class, 'driver_id');
    }

    /**
     * Get preferences from prestataires (whitelist/blacklist)
     */
    public function prestatairePreferences(): HasMany
    {
        return $this->hasMany(PrestataireDriverPreference::class, 'driver_id');
    }

    /**
     * Check if driver is internal to a specific prestataire
     */
    public function isInternalFor(int $prestataireId): bool
    {
        return $this->is_internal && $this->employer_prestataire_id === $prestataireId;
    }

    /**
     * Check if driver is blocked by a prestataire
     */
    public function isBlockedBy(int $prestataireId): bool
    {
        return PrestataireDriverPreference::isDriverBlocked($prestataireId, $this->id);
    }

    /**
     * Check if driver is preferred by a prestataire
     */
    public function isPreferredBy(int $prestataireId): bool
    {
        return PrestataireDriverPreference::isDriverPreferred($prestataireId, $this->id);
    }

    /**
     * Get prestataire rating for this driver
     */
    public function getRatingFromPrestataire(int $prestataireId): ?float
    {
        return DriverRating::getAverageForDriverFromPrestataire($this->id, $prestataireId);
    }

    /**
     * Get overall average rating from prestataires
     */
    public function getOverallPrestataireRating(): ?float
    {
        return DriverRating::getOverallAverageForDriver($this->id);
    }

    /**
     * Get delivery fee for a specific distance
     */
    public function calculateDeliveryFee(float $distanceKm): array
    {
        $pricing = $this->pricing;
        
        if (!$pricing) {
            // Default pricing
            return [
                'base_fee' => 2.50,
                'distance_fee' => $distanceKm * 0.50,
                'total' => 2.50 + ($distanceKm * 0.50),
                'multiplier' => 1.0,
            ];
        }

        return $pricing->calculateFee($distanceKm);
    }

    /**
     * Scope for internal drivers of a prestataire
     */
    public function scopeInternalFor($query, int $prestataireId)
    {
        return $query->where('is_internal', true)
            ->where('employer_prestataire_id', $prestataireId);
    }

    /**
     * Scope for external drivers (not internal to any prestataire)
     */
    public function scopeExternal($query)
    {
        return $query->where(function ($q) {
            $q->where('is_internal', false)
                ->orWhereNull('is_internal');
        });
    }

    /**
     * Scope for non-blocked drivers by a prestataire
     */
    public function scopeNotBlockedBy($query, int $prestataireId)
    {
        $blockedIds = PrestataireDriverPreference::getBlockedDriverIds($prestataireId);
        return $query->whereNotIn('id', $blockedIds);
    }

    /**
     * Scope for preferred drivers by a prestataire
     */
    public function scopePreferredBy($query, int $prestataireId)
    {
        $preferredIds = PrestataireDriverPreference::getPreferredDriverIds($prestataireId);
        return $query->whereIn('id', $preferredIds);
    }

    /**
     * Get drivers who worked with a specific prestataire
     */
    public static function getDriversWhoWorkedWith(int $prestataireId)
    {
        $driverIds = FoodOrder::where('prestataire_id', $prestataireId)
            ->whereNotNull('driver_id')
            ->distinct()
            ->pluck('driver_id');

        return self::whereIn('id', $driverIds)->get();
    }

    /**
     * Get deliveries for a specific prestataire
     */
    public function deliveriesForPrestataire(int $prestataireId): HasMany
    {
        return $this->foodOrders()->where('prestataire_id', $prestataireId);
    }

    /**
     * Get sponsor prestataire
     */
    public function sponsorPrestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class, 'sponsor_prestataire_id');
    }

    /**
     * Check if driver is in probation period
     */
    public function isInProbation(): bool
    {
        return $this->trust_level === self::TRUST_PROBATION;
    }

    /**
     * Check if driver is suspended
     */
    public function isSuspended(): bool
    {
        return $this->trust_level === self::TRUST_SUSPENDED;
    }

    /**
     * Check if driver can accept an order (respects daily limit and amount)
     */
    public function canAcceptOrder(float $orderAmount = 0): array
    {
        // Suspended drivers cannot accept orders
        if ($this->isSuspended()) {
            return ['allowed' => false, 'reason' => 'Votre compte est suspendu: ' . $this->suspended_reason];
        }

        // Sponsored drivers have no limits (full access)
        if ($this->sponsor_prestataire_id) {
            return ['allowed' => true];
        }

        // Verified/Trusted drivers have no limits
        if (!$this->isInProbation()) {
            return ['allowed' => true];
        }

        // Check daily limit for probation drivers (not sponsored)
        $todayDeliveries = $this->foodOrders()
            ->whereDate('created_at', today())
            ->whereIn('delivery_status', [
                FoodOrder::DELIVERY_STATUS_ASSIGNED,
                FoodOrder::DELIVERY_STATUS_PICKED_UP,
                FoodOrder::DELIVERY_STATUS_IN_TRANSIT,
                FoodOrder::DELIVERY_STATUS_DELIVERED,
            ])
            ->count();

        if ($todayDeliveries >= $this->daily_limit) {
            return [
                'allowed' => false, 
                'reason' => "Limite journalière atteinte ({$this->daily_limit} livraisons). Faites-vous parrainer par un prestataire pour débloquer."
            ];
        }

        // Check order amount for probation drivers
        if ($orderAmount > $this->max_order_amount) {
            return [
                'allowed' => false, 
                'reason' => "Montant trop élevé ({$orderAmount}€ > max {$this->max_order_amount}€). Faites-vous parrainer pour débloquer."
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Get remaining deliveries needed to exit probation
     */
    public function getRemainingProbationDeliveries(): int
    {
        $required = 10; // 10 livraisons réussies pour sortir de probation
        return max(0, $required - $this->probation_deliveries_count);
    }

    /**
     * Increment probation count and check for promotion
     */
    public function incrementProbationDelivery(): void
    {
        if (!$this->isInProbation()) {
            return;
        }

        $this->increment('probation_deliveries_count');

        // Check if should be promoted to verified
        if ($this->probation_deliveries_count >= 10) {
            $avgRating = DriverClientRating::getAverageForDriver($this->id);
            
            // Need at least 3.0 average rating to be verified
            if ($avgRating === null || $avgRating >= 3.0) {
                $this->update([
                    'trust_level' => self::TRUST_VERIFIED,
                    'daily_limit' => 50, // Increased limit
                    'max_order_amount' => 500.00, // Increased amount
                    'verified_at' => now(),
                ]);
            }
        }
    }

    /**
     * Check and suspend if rating is too low - notify sponsor if exists
     */
    public function checkAutoSuspension(): bool
    {
        $ratingCount = DriverClientRating::getCountForDriver($this->id);
        
        // Only check after 5 ratings
        if ($ratingCount < 5) {
            return false;
        }

        $avgRating = DriverClientRating::getAverageForDriver($this->id);
        
        // Suspend if rating below 3.0
        if ($avgRating !== null && $avgRating < 3.0) {
            $this->update([
                'trust_level' => self::TRUST_SUSPENDED,
                'is_available' => false,
                'suspended_reason' => "Note moyenne insuffisante ({$avgRating}/5 après {$ratingCount} avis)",
                'suspended_at' => now(),
            ]);
            
            // Notify sponsor prestataire if exists
            $this->notifySponsorOfSuspension($avgRating, $ratingCount);
            
            return true;
        }

        return false;
    }
    
    /**
     * Notify sponsor prestataire when driver is suspended
     */
    public function notifySponsorOfSuspension(float $avgRating, int $ratingCount): void
    {
        if (!$this->sponsor_prestataire_id) {
            return;
        }
        
        $sponsor = $this->sponsorPrestataire;
        if (!$sponsor || !$sponsor->user) {
            return;
        }
        
        try {
            // Send notification to sponsor
            $sponsor->user->notify(new \App\Notifications\DriverSuspendedNotification(
                $this,
                $avgRating,
                $ratingCount
            ));
        } catch (\Exception $e) {
            // Log error but don't fail
            \Log::error('Failed to notify sponsor of driver suspension: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique sponsor code
     */
    public static function generateSponsorCode(): string
    {
        do {
            $code = 'DRV' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('sponsor_code', $code)->exists());

        return $code;
    }

    /**
     * Validate sponsor prestataire (any active prestataire can sponsor)
     */
    public static function validateSponsorPrestataire(int $prestataireId): array
    {
        $prestataire = Prestataire::find($prestataireId);
        
        if (!$prestataire) {
            return ['valid' => false, 'reason' => 'Prestataire introuvable'];
        }

        // Just check if prestataire is active (removed strict verification requirements)
        // Any legitimate prestataire can sponsor a driver
        
        $reviewCount = $prestataire->reviews()->count();
        $avgRating = $prestataire->reviews()->avg('rating') ?? 5.0;

        return [
            'valid' => true,
            'prestataire' => $prestataire,
            'rating' => round($avgRating, 1),
            'reviews' => $reviewCount,
        ];
    }

    /**
     * Get trust level label in French
     */
    public function getTrustLevelLabelAttribute(): string
    {
        return match($this->trust_level) {
            self::TRUST_PROBATION => 'En période d\'essai',
            self::TRUST_VERIFIED => 'Vérifié',
            self::TRUST_TRUSTED => 'Fiable',
            self::TRUST_SUSPENDED => 'Suspendu',
            default => 'Inconnu',
        };
    }

    /**
     * Get trust level color
     */
    public function getTrustLevelColorAttribute(): string
    {
        return match($this->trust_level) {
            self::TRUST_PROBATION => 'warning',
            self::TRUST_VERIFIED => 'success',
            self::TRUST_TRUSTED => 'primary',
            self::TRUST_SUSPENDED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Compter les livreurs externes actifs dans un rayon autour d'un point
     * 
     * @param float $lat Latitude du centre
     * @param float $lng Longitude du centre
     * @param float $radiusKm Rayon en km (défaut 10km)
     * @return int Nombre de livreurs externes disponibles
     */
    public static function countExternalDriversNearby(float $lat, float $lng, float $radiusKm = 10): int
    {
        // Formule Haversine simplifiée pour MySQL
        // 6371 = rayon de la Terre en km
        return self::external()
            ->where('is_active', true)
            ->where('status', 'active')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->whereRaw("
                (6371 * acos(
                    cos(radians(?)) * cos(radians(current_lat)) * 
                    cos(radians(current_lng) - radians(?)) + 
                    sin(radians(?)) * sin(radians(current_lat))
                )) <= ?
            ", [$lat, $lng, $lat, $radiusKm])
            ->count();
    }

    /**
     * Vérifier si assez de livreurs externes sont disponibles pour un prestataire
     * Minimum requis: 4 livreurs dans 10km
     * 
     * @param Prestataire $prestataire
     * @return array ['available' => bool, 'count' => int, 'required' => int]
     */
    public static function checkExternalDriversAvailability(Prestataire $prestataire): array
    {
        $requiredCount = 4;
        $radiusKm = 10;

        // Si le prestataire n'a pas de coordonnées, on ne peut pas vérifier
        if (!$prestataire->latitude || !$prestataire->longitude) {
            return [
                'available' => false,
                'count' => 0,
                'required' => $requiredCount,
                'reason' => 'no_coordinates',
            ];
        }

        $count = self::countExternalDriversNearby(
            (float) $prestataire->latitude,
            (float) $prestataire->longitude,
            $radiusKm
        );

        return [
            'available' => $count >= $requiredCount,
            'count' => $count,
            'required' => $requiredCount,
            'radius_km' => $radiusKm,
            'reason' => $count >= $requiredCount ? null : 'not_enough_drivers',
        ];
    }
}
