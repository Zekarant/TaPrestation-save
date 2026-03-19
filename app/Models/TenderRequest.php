<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TenderRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'title',
        'description',
        'reference',
        'address',
        'city',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'radius_km',
        'start_date',
        'end_date',
        'preferred_time_start',
        'preferred_time_end',
        'flexible_dates',
        'urgency',
        'budget_min',
        'budget_max',
        'budget_type',
        'budget_visible',
        'photos',
        'videos',
        'documents',
        'access_instructions',
        'contact_name',
        'contact_phone',
        'contact_email',
        'contact_preference',
        'status',
        'max_responses',
        'auto_match',
        'public_visibility',
        'expires_at',
        'published_at',
        'awarded_at',
        'awarded_prestataire_id',
        'form_step',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'flexible_dates' => 'boolean',
        'budget_visible' => 'boolean',
        'photos' => 'array',
        'videos' => 'array',
        'documents' => 'array',
        'auto_match' => 'boolean',
        'public_visibility' => 'boolean',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
        'awarded_at' => 'datetime',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tender) {
            if (empty($tender->reference)) {
                $tender->reference = self::generateReference();
            }
        });
    }

    /**
     * Génère une référence unique
     */
    public static function generateReference(): string
    {
        $maxAttempts = 10;
        $attempts = 0;
        
        do {
            $reference = 'AO-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
            $attempts++;
            
            // Éviter une boucle infinie
            if ($attempts >= $maxAttempts) {
                $reference = 'AO-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(10));
                break;
            }
            
            $exists = \Illuminate\Support\Facades\DB::table('tender_requests')
                ->where('reference', $reference)
                ->whereNull('deleted_at')
                ->exists();
                
        } while ($exists);

        return $reference;
    }

    // ===== RELATIONS =====

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'tender_request_categories')
            ->withTimestamps();
    }

    public function responses()
    {
        return $this->hasMany(TenderResponse::class);
    }

    public function invitations()
    {
        return $this->hasMany(TenderInvitation::class);
    }

    public function awardedPrestataire()
    {
        return $this->belongsTo(Prestataire::class, 'awarded_prestataire_id');
    }

    // ===== SCOPES =====

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['published', 'in_progress']);
    }

    public function scopeForCity($query, $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeForCategories($query, array $categoryIds)
    {
        return $query->whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });
    }

    public function scopeInBudgetRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('budget_max', '>=', $min);
        }
        if ($max) {
            $query->where('budget_min', '<=', $max);
        }
        return $query;
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // ===== ACCESSORS =====

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'published' => 'Publié',
            'in_progress' => 'En cours',
            'awarded' => 'Attribué',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            'expired' => 'Expiré',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'yellow',
            'published' => 'green',
            'in_progress' => 'blue',
            'awarded' => 'purple',
            'completed' => 'emerald',
            'cancelled' => 'red',
            'expired' => 'orange',
            default => 'gray',
        };
    }

    public function getUrgencyLabelAttribute(): string
    {
        return match($this->urgency) {
            'low' => 'Faible',
            'normal' => 'Normal',
            'high' => 'Élevée',
            'urgent' => 'Urgent',
            default => $this->urgency,
        };
    }

    public function getBudgetDisplayAttribute(): string
    {
        if (!$this->budget_visible) {
            return 'Budget non communiqué';
        }

        if ($this->budget_min && $this->budget_max) {
            return number_format($this->budget_min, 0, ',', ' ') . ' € - ' . number_format($this->budget_max, 0, ',', ' ') . ' €';
        }
        if ($this->budget_min) {
            return 'À partir de ' . number_format($this->budget_min, 0, ',', ' ') . ' €';
        }
        if ($this->budget_max) {
            return "Jusqu'à " . number_format($this->budget_max, 0, ',', ' ') . ' €';
        }
        return 'Budget à définir';
    }

    public function getResponsesCountAttribute(): int
    {
        return $this->responses()->count();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // ===== METHODS =====

    /**
     * Publie l'appel d'offre
     */
    public function publish(): bool
    {
        if ($this->status !== 'draft' && $this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'expires_at' => $this->expires_at ?? now()->addDays(30),
        ]);

        // Lancer le matching automatique si activé
        if ($this->auto_match) {
            dispatch(new \App\Jobs\ProcessTenderMatching($this));
        }

        return true;
    }

    /**
     * Attribue l'appel d'offre à un prestataire
     */
    public function awardTo(Prestataire $prestataire): bool
    {
        $response = $this->responses()
            ->where('prestataire_id', $prestataire->id)
            ->first();

        if (!$response) {
            return false;
        }

        $this->update([
            'status' => 'awarded',
            'awarded_prestataire_id' => $prestataire->id,
            'awarded_at' => now(),
        ]);

        $response->update(['status' => 'accepted']);

        // Rejeter les autres réponses
        $this->responses()
            ->where('prestataire_id', '!=', $prestataire->id)
            ->where('status', '!=', 'withdrawn')
            ->update(['status' => 'rejected']);

        return true;
    }

    /**
     * Vérifie si un prestataire peut répondre
     */
    public function canReceiveResponse(Prestataire $prestataire): bool
    {
        // Déjà répondu ?
        // Une réponse "withdrawn" (retirée) ne doit pas bloquer une nouvelle soumission.
        if ($this->responses()
            ->where('prestataire_id', $prestataire->id)
            ->where('status', '!=', 'withdrawn')
            ->exists()
        ) {
            return false;
        }

        // Max de réponses atteint ?
        // Ne pas compter les réponses retirées dans la limite.
        if ($this->responses()->where('status', '!=', 'withdrawn')->count() >= $this->max_responses) {
            return false;
        }

        // Statut permet les réponses ?
        if (!in_array($this->status, ['published', 'in_progress'])) {
            return false;
        }

        return true;
    }

    /**
     * Calcule le score de matching pour un prestataire
     */
    public function calculateMatchScore(Prestataire $prestataire): array
    {
        $score = 0;
        $reasons = [];

        // Match par catégories (40 points max)
        $tenderCategoryIds = $this->categories()->pluck('categories.id')->toArray();
        $prestataireCategoryIds = $prestataire->services()
            ->with('categories')
            ->get()
            ->pluck('categories')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();

        $matchingCategories = array_intersect($tenderCategoryIds, $prestataireCategoryIds);
        if (count($matchingCategories) > 0) {
            $categoryScore = min(40, count($matchingCategories) * 10);
            $score += $categoryScore;
            $reasons[] = [
                'type' => 'category',
                'score' => $categoryScore,
                'label' => count($matchingCategories) . ' catégorie(s) correspondante(s)',
            ];
        }

        // Match par localisation (30 points max)
        if ($this->latitude && $this->longitude && $prestataire->latitude && $prestataire->longitude) {
            $distance = $this->calculateDistance(
                $this->latitude, $this->longitude,
                $prestataire->latitude, $prestataire->longitude
            );

            if ($distance <= $this->radius_km) {
                $locationScore = 30 - min(30, ($distance / $this->radius_km) * 20);
                $score += $locationScore;
                $reasons[] = [
                    'type' => 'location',
                    'score' => round($locationScore),
                    'label' => 'À ' . round($distance) . ' km',
                ];
            }
        }

        // Match par note moyenne (20 points max)
        if ($prestataire->rating_average > 0) {
            $ratingScore = ($prestataire->rating_average / 5) * 20;
            $score += $ratingScore;
            $reasons[] = [
                'type' => 'rating',
                'score' => round($ratingScore),
                'label' => 'Note: ' . number_format($prestataire->rating_average, 1) . '/5',
            ];
        }

        // Bonus prestataire vérifié (10 points)
        if ($prestataire->is_approved) {
            $score += 10;
            $reasons[] = [
                'type' => 'verified',
                'score' => 10,
                'label' => 'Prestataire vérifié',
            ];
        }

        return [
            'score' => round($score),
            'reasons' => $reasons,
        ];
    }

    /**
     * Calcule la distance entre deux points GPS
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
