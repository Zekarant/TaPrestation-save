<?php

namespace App\Models;

use App\Notifications\SimpleRentalPeriodEndedNotification;
use App\Notifications\SimpleRentalStartedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Equipment Model
 * 
 * IMPORTANT: Equipment Status Management
 * ====================================
 * Equipment status remains unchanged by the rental process.
 * - Equipment can have status: 'active', 'inactive', 'maintenance'
 * - Equipment can be rented multiple times for different periods as long as:
 *   1. It has status different from 'inactive' or 'maintenance'
 *   2. It is marked as available (is_available = true)
 *   3. There are no date conflicts with existing rentals
 * - Availability is determined by checking for date conflicts in rentals,
 *   not by changing the equipment's status
 */
class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prestataire_id',
        'name',
        'slug',
        'description',
        'technical_specifications',
        'photos',
        'main_photo',
        'price_per_hour',
        'price_per_day',
        'price_per_week',
        'price_per_month',
        'security_deposit',

        'condition',
        'status',
        'is_available',
        'available_from',
        'available_until',
        'minimum_rental_duration',
        'maximum_rental_duration',
        'address',
        'city',
        'postal_code',
        'country',
        'latitude',
        'longitude',

        'rental_conditions',
        'usage_instructions',
        'safety_instructions',
        'included_accessories',
        'optional_accessories',
        'requires_license',
        'required_license_type',
        'minimum_age',
        'average_rating',
        'total_reviews',
        'total_rentals',
        'view_count',
        'last_rented_at',
        'metadata',
        'featured',
        'sort_order',
        'verified_at',
        'verified_by',
        'brand',
        'model',
        'weight',
        'dimensions',
        'power_requirements',
        'serial_number',
        'category_id',
        'subcategory_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'photos' => 'array',
        'included_accessories' => 'array',
        'optional_accessories' => 'array',
        'metadata' => 'array',
        'is_available' => 'boolean',

        'requires_license' => 'boolean',
        'featured' => 'boolean',
        'price_per_hour' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'price_per_week' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'security_deposit' => 'decimal:2',

        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'average_rating' => 'decimal:2',
        'available_from' => 'date',
        'available_until' => 'date',
        'last_rented_at' => 'datetime',
        'verified_at' => 'datetime',
        'minimum_rental_duration' => 'integer',
        'maximum_rental_duration' => 'integer',

        'minimum_age' => 'integer',
        'total_reviews' => 'integer',
        'total_rentals' => 'integer',
        'view_count' => 'integer',
        'sort_order' => 'integer',
        'verified_by' => 'integer'
    ];

    /**
     * Relation avec le prestataire propriétaire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Relation avec les demandes de location
     */
    public function rentalRequests(): HasMany
    {
        return $this->hasMany(EquipmentRentalRequest::class);
    }

    /**
     * Relation avec les locations actives
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(EquipmentRental::class);
    }

    /**
     * Relation avec les avis sur le matériel
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(EquipmentReview::class);
    }



    /**
     * Relation avec la catégorie principale
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Relation avec la sous-catégorie
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    /**
     * Relation avec les signalements
     */
    public function reports(): HasMany
    {
        return $this->hasMany(EquipmentReport::class);
    }

    /**
     * Scope pour les équipements actifs
     * Equipment is active as long as it's not explicitly inactive or in maintenance
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['inactive', 'maintenance']);
    }

    /**
     * Scope pour les équipements en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope pour filtrer par ville
     */
    public function scopeInSameCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Vérifie si l'équipement est actif
     * Equipment is considered active as long as the status isn't explicitly 'inactive' or 'maintenance'
     * This ensures equipment remains active even after being rented
     */
    public function isActive()
    {
        // Equipment is active unless it's explicitly set to inactive or maintenance
        return !in_array($this->status, ['inactive', 'maintenance']);
    }

    /**
     * Vérifie si l'équipement est disponible
     * Note: We only check the is_available flag, not the status
     * Status should only reflect whether the equipment is active/inactive overall
     * Actual rental availability is determined by checking specific time periods
     */
    public function isAvailable()
    {
        return $this->is_available && $this->isActive();
    }

    /**
     * Vérifie si un utilisateur est le propriétaire de cet équipement
     */
    public function isOwnedBy($user)
    {
        if (!$user || !$user->isPrestataire() || !$user->prestataire) {
            return false;
        }
        
        return $user->prestataire->id === $this->prestataire_id;
    }

    /**
     * Scope pour les équipements disponibles
     * Equipment is available if it's active (not inactive/maintenance) and marked as available
     */
    public function scopeAvailable($query)
    {
        return $query->whereNotIn('status', ['inactive', 'maintenance'])->where('is_available', true);
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
              ->orWhere('subcategory_id', $categoryId);
        });
    }

    /**
     * Scope pour filtrer par localisation
     */
    public function scopeNearLocation($query, $location, $radius = 50)
    {
        return $query->where('city', 'like', "%{$location}%")
                    ->orWhere('address', 'like', "%{$location}%");
    }

    /**
     * Scope pour filtrer par prix
     */
    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price_per_day', [$minPrice, $maxPrice]);
    }

    /**
     * Vérifie si l'équipement est disponible pour une période donnée
     * This is the key method for checking availability by date range
     * It checks:
     * 1. If the equipment is globally available (is_available flag is true and status is 'active')
     * 2. If there are no overlapping rentals in the requested period
     */
    public function isAvailableForPeriod($startDate, $endDate)
    {
        // First check if the equipment is globally available
        if (!$this->isAvailable()) {
            return false;
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        // Check for rental periods that overlap with the requested period
        // We only consider active rentals (confirmed, in_use, delivered)
        $overlappingRentals = $this->rentals()
            ->whereIn('status', ['confirmed', 'in_use', 'delivered'])
            ->where(function ($query) use ($start, $end) {
                // Period starts during an existing rental
                $query->whereBetween('start_date', [$start, $end])
                      // Period ends during an existing rental
                      ->orWhereBetween('end_date', [$start, $end])
                      // Period completely encompasses an existing rental
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                      });
            })
            ->exists();

        return !$overlappingRentals;
    }

    /**
     * Calcule le prix pour une période donnée
     */
    public function calculatePrice($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $start->diffInDays($end) + 1;

        // Calcul du prix selon la durée
        if ($days >= 30 && $this->price_per_month) {
            $months = ceil($days / 30);
            return $this->price_per_month * $months;
        } elseif ($days >= 7 && $this->price_per_week) {
            $weeks = ceil($days / 7);
            return $this->price_per_week * $weeks;
        } else {
            return $this->price_per_day * $days;
        }
    }

    /**
     * Obtient la note moyenne
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('overall_rating') ?? 0;
    }

    /**
     * Obtient le nombre total d'avis
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Obtient la première photo disponible (main_photo ou première du tableau photos)
     */
    public function getFirstPhotoAttribute()
    {
        if ($this->attributes['main_photo']) {
            return $this->attributes['main_photo'];
        }
        return $this->photos && count($this->photos) > 0 ? $this->photos[0] : null;
    }

    /**
     * Obtient les statistiques détaillées des avis
     */
    public function getDetailedRatingStats()
    {
        $reviews = $this->reviews();

        $stats = [
            'total_reviews' => $reviews->count(),
            'average_rating' => round($reviews->avg('overall_rating'), 1),
            'detailed_averages' => [
                'condition' => round($reviews->avg('condition_rating'), 1),
                'performance' => round($reviews->avg('performance_rating'), 1),
                'value' => round($reviews->avg('value_rating'), 1),
                'service' => round($reviews->avg('service_rating'), 1),
            ],
            'rating_counts' => [
                '5_stars' => $reviews->clone()->where('overall_rating', 5)->count(),
                '4_stars' => $reviews->clone()->where('overall_rating', 4)->count(),
                '3_stars' => $reviews->clone()->where('overall_rating', 3)->count(),
                '2_stars' => $reviews->clone()->where('overall_rating', 2)->count(),
                '1_star' => $reviews->clone()->where('overall_rating', 1)->count(),
            ]
        ];

        // Calculer les pourcentages
        if ($stats['total_reviews'] > 0) {
            foreach ($stats['rating_counts'] as $key => $count) {
                $stats['rating_percentages'][$key] = round(($count / $stats['total_reviews']) * 100);
            }
        } else {
            foreach ($stats['rating_counts'] as $key => $count) {
                $stats['rating_percentages'][$key] = 0;
            }
        }

        return $stats;
    }

    /**
     * Obtient le statut de disponibilité formaté
     */
    public function getFormattedAvailabilityStatusAttribute()
    {
        $statuses = [
            'active' => 'Disponible',
            'rented' => 'Loué',
            'maintenance' => 'En maintenance',
            'inactive' => 'Inactif',
            'unavailable' => 'Indisponible'
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtient l'état formaté
     */
    public function getFormattedConditionAttribute()
    {
        $conditions = [
            'new' => 'Neuf',
            'excellent' => 'Excellent',
            'good' => 'Bon',
            'fair' => 'Correct',
            'poor' => 'Usagé'
        ];

        return $conditions[$this->condition] ?? 'Non spécifié';
    }



    /**
     * Obtient les prochaines disponibilités
     */
    public function getUnavailableDates()
    {
        $unavailableDates = [];
        $rentals = $this->rentals()->whereIn('status', ['in_use', 'confirmed', 'delivered'])->get();

        foreach ($rentals as $rental) {
            $period = Carbon::parse($rental->start_date)->toPeriod($rental->end_date);
            foreach ($period as $date) {
                $unavailableDates[] = $date->format('Y-m-d');
            }
        }

        return array_unique($unavailableDates);
    }

    public function getNextAvailableDates($limit = 10)
    {
        $dates = [];
        $currentDate = Carbon::now();
        $count = 0;

        while ($count < $limit) {
            if ($this->isAvailableForPeriod($currentDate, $currentDate)) {
                $dates[] = $currentDate->copy();
                $count++;
            }
            $currentDate->addDay();
        }

        return $dates;
    }

    /**
     * Accesseur pour le tarif journalier
     */
    public function getDailyRateAttribute()
    {
        return $this->price_per_day;
    }

    /**
     * Accesseur pour le tarif hebdomadaire
     */
    public function getWeeklyRateAttribute()
    {
        return $this->price_per_week;
    }

    /**
     * Update equipment rental statuses based on the current date
     * This can be called from a scheduled command to automatically manage rental periods
     */
    public static function updateRentalStatuses()
    {
        $today = now()->startOfDay();
        
        // Find rentals that have ended (end_date < today) but are still marked as active
        $endedRentals = EquipmentRental::whereIn('status', ['confirmed', 'in_use', 'delivered'])
            ->where('end_date', '<', $today)
            ->get();
            
        foreach ($endedRentals as $rental) {
            // Mark the rental as returned if it hasn't been marked yet
            $rental->update([
                'status' => 'returned',
                'actual_end_datetime' => now(),
            ]);
            
            // Notify the prestataire that the rental period has ended
            $rental->prestataire->user->notify(new SimpleRentalPeriodEndedNotification($rental));
        }
        
        // Find rentals that should start today
        $startingRentals = EquipmentRental::where('status', 'confirmed')
            ->where('start_date', '=', $today)
            ->get();
            
        foreach ($startingRentals as $rental) {
            // Mark the rental as in_use
            $rental->update([
                'status' => 'in_use',
                'actual_start_datetime' => now(),
            ]);
            
            // Notify both client and prestataire
            $rental->client->user->notify(new SimpleRentalStartedNotification($rental));
            $rental->prestataire->user->notify(new SimpleRentalStartedNotification($rental));
        }
        
        return [
            'ended_rentals' => $endedRentals->count(),
            'starting_rentals' => $startingRentals->count()
        ];
    }

}