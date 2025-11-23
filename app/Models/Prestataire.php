<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Equipment;
use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentRental;
use App\Models\UrgentSale;
use App\Models\Video;
use App\Models\PrestataireVerificationRequest;

class Prestataire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'secteur_activite',
        'competences',
        'siret',
        'description',
        'phone',
        'address',
        'city',
        'photo',
        'postal_code',
        'country',
        'service_radius_km',
        'latitude',
        'longitude',
        'is_approved',
        'is_verified',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'website',
        'portfolio_url',
        'facebook',
        'instagram',
        'linkedin',
        'years_experience',
        'availability_radius',
        'profile_image',
        'cover_image',
        'certifications',
        'insurance_number',
        'tax_number',
        'requires_approval',
        'min_advance_hours',
        'max_advance_days',
        'buffer_between_appointments',
        'bank_details',
        'preferred_payment_methods',
        'response_time',
        'completion_rate',
        'rating_average',
        'total_reviews',
        'total_projects',
        'is_featured',
        'featured_until',
        'subscription_type',
        'subscription_expires_at',
        'last_active_at',
        'verification_status',
        'background_check_status',
        'background_check_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'business_license',
        'professional_insurance',
        'is_active',
        'verification_document',
        'video_storage_limit_mb',
        'video_storage_used_mb'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        // 'hourly_rate_min' => 'decimal:2', // Supprimé pour confidentialité
        // 'hourly_rate_max' => 'decimal:2', // Supprimé pour confidentialité
        'availability_radius' => 'integer',
        'certifications' => 'array',
        'bank_details' => 'array',
        'preferred_payment_methods' => 'array',
        'rating_average' => 'decimal:2',
        'total_reviews' => 'integer',
        'total_projects' => 'integer',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'last_active_at' => 'datetime',
        'years_experience' => 'integer',
        'response_time' => 'integer',
        'completion_rate' => 'decimal:2',
        'requires_approval' => 'boolean',
        'min_advance_hours' => 'integer',
        'max_advance_days' => 'integer',
        'buffer_between_appointments' => 'integer',
        'video_storage_limit_mb' => 'integer',
        'video_storage_used_mb' => 'integer'
    ];

    protected $dates = [
        'approved_at',
        'featured_until',
        'subscription_expires_at',
        'last_active_at',
        'deleted_at'
    ];

    /**
     * Relation avec le modèle User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les services proposés
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Relation avec les réservations
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Relation avec les avis reçus
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relation avec les disponibilités
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(PrestataireAvailability::class);
    }

    /**
     * Relation avec les équipements
     */
    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /**
     * Relation avec les demandes de location d'équipement
     */
    public function equipmentRentalRequests(): HasMany
    {
        return $this->hasMany(EquipmentRentalRequest::class);
    }

    /**
     * Relation avec les vidéos
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Relation avec les locations d'équipement
     */
    public function equipmentRentals(): HasMany
    {
        return $this->hasMany(EquipmentRental::class);
    }

    /**
     * Relation avec les annonces
     */
    public function urgentSales(): HasMany
    {
        return $this->hasMany(UrgentSale::class);
    }

    /**
     * Relation avec l'utilisateur qui a approuvé ce prestataire
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relation avec les clients qui suivent ce prestataire
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_prestataire_follows', 'prestataire_id', 'client_id')
                    ->withTimestamps();
    }

    /**
     * Relation avec la demande de vérification.
     */
    public function verificationRequest()
    {
        return $this->hasOne(ClientVerificationRequest::class);
    }

    /**
     * Relation avec les demandes de vérification manuelle
     */
    public function verificationRequests()
    {
        return $this->hasMany(PrestataireVerificationRequest::class);
    }

    /**
     * Relation avec la dernière demande de vérification
     */
    public function latestVerificationRequest()
    {
        return $this->hasOne(PrestataireVerificationRequest::class)->latest();
    }

    /**
     * Scope pour les prestataires approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope pour les prestataires en attente d'approbation
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false)->whereNull('rejection_reason');
    }

    /**
     * Scope pour les prestataires rejetés
     */
    public function scopeRejected($query)
    {
        return $query->where('is_approved', false)->whereNotNull('rejection_reason');
    }

    /**
     * Scope pour les prestataires actifs
     */
    public function scopeActive($query)
    {
        return $query->where('last_active_at', '>=', now()->subDays(30));
    }

    /**
     * Scope pour les prestataires mis en avant
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_until')
                          ->orWhere('featured_until', '>', now());
                    });
    }

    /**
     * Scope pour rechercher par localisation
     */
    public function scopeNearby($query, $latitude, $longitude, $radius = 50)
    {
        return $query->whereRaw(
            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
            [$latitude, $longitude, $latitude, $radius]
        );
    }

    /**
     * Scope pour filtrer par note minimum
     */
    public function scopeWithMinRating($query, $minRating)
    {
        return $query->where('rating_average', '>=', $minRating);
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getFullNameAttribute()
    {
        return $this->company_name ?: $this->user->name;
    }

    /**
     * Accesseur pour l'adresse complète
     */
    public function getFullAddressAttribute()
    {
        return trim($this->address . ', ' . $this->postal_code . ' ' . $this->city, ', ');
    }

    /**
     * Accesseur pour l'URL de l'image de profil
     */
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image ? asset('storage/' . $this->profile_image) : null;
    }

    /**
     * Accesseur pour l'URL de l'image de couverture
     */
    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    /**
     * Accesseur pour la note moyenne
     */
    public function getAverageRatingAttribute()
    {
        return $this->rating_average ?? $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Mutateur pour mettre à jour la dernière activité
     */
    public function updateLastActivity()
    {
        $this->update(['last_active_at' => now()]);
    }

    /**
     * Vérifier si le prestataire est disponible
     */
    public function isAvailable($date = null, $timeSlot = null)
    {
        $date = $date ?: now()->toDateString();
        
        // Vérifier les exceptions de disponibilité
        // Vérifier les disponibilités générales
        return $this->availabilities()
            ->where('day_of_week', date('w', strtotime($date)))
            ->when($timeSlot, function($query) use ($timeSlot) {
                return $query->where('start_time', '<=', $timeSlot)
                           ->where('end_time', '>=', $timeSlot);
            })
            ->exists();
    }

    /**
     * Calculer la distance avec des coordonnées données
     */
    public function distanceFrom($latitude, $longitude)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }
        
        $earthRadius = 6371; // Rayon de la Terre en kilomètres
        
        $latDelta = deg2rad($latitude - $this->latitude);
        $lonDelta = deg2rad($longitude - $this->longitude);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Obtenir les statistiques du prestataire
     */
    public function getStats()
    {
        return [
            'total_bookings' => $this->bookings()->count(),
            'completed_bookings' => $this->bookings()->where('status', 'completed')->count(),
            // 'total_revenue' => $this->bookings()->where('status', 'completed')->sum('total_amount'), // Supprimé pour confidentialité
            'total_revenue' => 0, // Valeur par défaut
            'average_rating' => $this->rating_average,
            'total_reviews' => $this->total_reviews,
            'response_rate' => $this->calculateResponseRate(),
            'completion_rate' => $this->completion_rate
        ];
    }

    /**
     * Vérifier si le prestataire peut être contacté
     */
    public function canBeContacted()
    {
        return $this->is_approved && 
               $this->user->email_verified_at && 
               $this->last_active_at >= now()->subDays(90);
    }

    /**
     * Obtenir les créneaux disponibles pour une date
     */
    public function getAvailableSlots($date)
    {
        $dayOfWeek = date('w', strtotime($date));
        
        $availabilities = $this->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->get();
            
        $bookedSlots = $this->bookings()
            ->whereDate('start_datetime', $date)
            ->where('status', '!=', 'cancelled')
            ->get(['start_datetime', 'end_datetime']);
            
        // Logique pour calculer les créneaux disponibles
        // (à implémenter selon les besoins spécifiques)
        
        return [];
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['is_verified'] = $this->is_verified;
        return $array;
    }

    /**
     * Vérifier si le prestataire remplit les critères de vérification automatique
     */
    public function meetsAutomaticVerificationCriteria(): bool
    {
        // 100 avis positifs minimum (note >= 4)
        $positiveReviews = $this->reviews()->where('rating', '>=', 4)->count();
        
        // Moins de 10 avis négatifs (note < 3)
        $negativeReviews = $this->reviews()->where('rating', '<', 3)->count();
        
        // Note moyenne >= 4/5
        $averageRating = $this->rating_average ?? 0;
        
        // Connexion au moins 5 fois par mois (basé sur last_active_at)
        $monthlyConnections = $this->getMonthlyConnectionCount();
        
        // Email et téléphone vérifiés
        $emailVerified = $this->user->email_verified_at !== null;
        $phoneVerified = $this->user->phone_verified_at !== null;
        
        // Aucun dépassement de 3 signalements (à implémenter selon votre système de signalement)
        $reportCount = $this->getReportCount();
        
        return $positiveReviews >= 100 &&
               $negativeReviews < 10 &&
               $averageRating >= 4.0 &&
               $monthlyConnections >= 5 &&
               $emailVerified &&
               $phoneVerified &&
               $reportCount <= 3;
    }

    /**
     * Obtenir le nombre de connexions mensuelles
     */
    private function getMonthlyConnectionCount(): int
    {
        // Logique simplifiée - peut être améliorée avec un système de tracking plus précis
        if (!$this->last_active_at) {
            return 0;
        }
        
        $daysSinceLastActive = now()->diffInDays($this->last_active_at);
        
        // Estimation basée sur la fréquence d'activité
        if ($daysSinceLastActive <= 7) {
            return 20; // Très actif
        } elseif ($daysSinceLastActive <= 14) {
            return 10; // Actif
        } elseif ($daysSinceLastActive <= 30) {
            return 5; // Modérément actif
        }
        
        return 0; // Inactif
    }

    /**
     * Obtenir le nombre de signalements
     */
    private function getReportCount(): int
    {
        // À implémenter selon votre système de signalement
        // Pour l'instant, retourne 0
        return 0;
    }

    /**
     * Appliquer la vérification automatique si les critères sont remplis
     */
    public function applyAutomaticVerification(): bool
    {
        if ($this->meetsAutomaticVerificationCriteria() && !$this->is_verified) {
            $this->update([
                'is_verified' => true,
                'verification_status' => 'verified_automatic'
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Vérifier si le prestataire a une demande de vérification en attente
     */
    public function hasPendingVerificationRequest(): bool
    {
        return $this->verificationRequests()->where('status', 'pending')->exists();
    }

    /**
     * Vérifier si le prestataire est vérifié (automatiquement ou manuellement)
     */
    public function isVerified(): bool
    {
        return $this->is_verified || 
               $this->verificationRequests()->where('status', 'approved')->exists();
    }

    /**
     * Obtenir le type de vérification
     */
    public function getVerificationType(): string
    {
        if ($this->is_verified && $this->verification_status === 'verified_automatic') {
            return 'automatic';
        } elseif ($this->verificationRequests()->where('status', 'approved')->exists()) {
            return 'manual';
        }
        
        return 'none';
    }
}