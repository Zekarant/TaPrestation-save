<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EquipmentReview extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipment_id',
        'equipment_rental_id',
        'client_id',
        'prestataire_id',
        'rating',
        'title',
        'comment',
        'pros',
        'cons',
        'condition_rating',
        'delivery_rating',
        'value_rating',
        'ease_of_use_rating',
        'would_recommend',
        'photos',
        'usage_context',
        'rental_duration_days',
        'verified_rental',
        'helpful_votes',
        'total_votes',
        'is_featured',
        'is_moderated',
        'moderation_status',
        'moderation_reason',
        'moderated_at',
        'moderated_by',
        'prestataire_response',
        'prestataire_response_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'condition_rating' => 'integer',
        'delivery_rating' => 'integer',
        'value_rating' => 'integer',
        'ease_of_use_rating' => 'integer',
        'would_recommend' => 'boolean',
        'verified_rental' => 'boolean',
        'is_featured' => 'boolean',
        'is_moderated' => 'boolean',
        'helpful_votes' => 'integer',
        'total_votes' => 'integer',
        'rental_duration_days' => 'integer',
        'photos' => 'array',
        'pros' => 'array',
        'cons' => 'array',
        'moderated_at' => 'datetime',
        'prestataire_response_date' => 'datetime'
    ];

    /**
     * Statuts de modération
     */
    const MODERATION_PENDING = 'pending';
    const MODERATION_APPROVED = 'approved';
    const MODERATION_REJECTED = 'rejected';
    const MODERATION_FLAGGED = 'flagged';

    /**
     * Relation avec l'équipement
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Relation avec la location
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(EquipmentRental::class, 'equipment_rental_id');
    }

    /**
     * Relation avec le client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec le prestataire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Scope pour les avis approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('moderation_status', self::MODERATION_APPROVED)
                    ->orWhereNull('moderation_status');
    }

    /**
     * Scope pour les avis en attente de modération
     */
    public function scopePendingModeration($query)
    {
        return $query->where('moderation_status', self::MODERATION_PENDING);
    }

    /**
     * Scope pour les avis vérifiés (avec location confirmée)
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_rental', true);
    }

    /**
     * Scope pour les avis avec photos
     */
    public function scopeWithPhotos($query)
    {
        return $query->whereNotNull('photos')
                    ->where('photos', '!=', '[]');
    }

    /**
     * Scope pour les avis recommandés
     */
    public function scopeRecommended($query)
    {
        return $query->where('would_recommend', true);
    }

    /**
     * Scope pour les avis par note
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope pour les avis récents
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Vérifie si l'avis est approuvé
     */
    public function isApproved()
    {
        return $this->moderation_status === self::MODERATION_APPROVED || 
               is_null($this->moderation_status);
    }

    /**
     * Vérifie si l'avis est en attente de modération
     */
    public function isPendingModeration()
    {
        return $this->moderation_status === self::MODERATION_PENDING;
    }

    /**
     * Vérifie si l'avis est rejeté
     */
    public function isRejected()
    {
        return $this->moderation_status === self::MODERATION_REJECTED;
    }

    /**
     * Vérifie si l'avis est signalé
     */
    public function isFlagged()
    {
        return $this->moderation_status === self::MODERATION_FLAGGED;
    }

    /**
     * Approuve l'avis
     */
    public function approve($moderatorId = null)
    {
        $this->update([
            'moderation_status' => self::MODERATION_APPROVED,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId
        ]);
    }

    /**
     * Rejette l'avis
     */
    public function reject($reason = null, $moderatorId = null)
    {
        $this->update([
            'moderation_status' => self::MODERATION_REJECTED,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId
        ]);
    }

    /**
     * Signale l'avis
     */
    public function flag($reason = null, $moderatorId = null)
    {
        $this->update([
            'moderation_status' => self::MODERATION_FLAGGED,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId
        ]);
    }

    /**
     * Ajoute une réponse du prestataire
     */
    public function addPrestataireResponse($response)
    {
        $this->update([
            'prestataire_response' => $response,
            'prestataire_response_date' => now()
        ]);
    }

    /**
     * Marque l'avis comme utile
     */
    public function markAsHelpful()
    {
        $this->increment('helpful_votes');
        $this->increment('total_votes');
    }

    /**
     * Marque l'avis comme non utile
     */
    public function markAsNotHelpful()
    {
        $this->increment('total_votes');
    }

    /**
     * Calcule la note moyenne détaillée
     */
    public function getDetailedAverageRating()
    {
        $ratings = array_filter([
            $this->rating,
            $this->condition_rating,
            $this->delivery_rating,
            $this->value_rating,
            $this->ease_of_use_rating
        ]);

        return count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
    }

    /**
     * Obtient le pourcentage d'utilité
     */
    public function getHelpfulnessPercentageAttribute()
    {
        if ($this->total_votes === 0) {
            return 0;
        }

        return round(($this->helpful_votes / $this->total_votes) * 100);
    }

    /**
     * Obtient la note formatée avec étoiles
     */
    public function getStarRatingAttribute()
    {
        $fullStars = floor($this->rating);
        $halfStar = ($this->rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return [
            'full' => $fullStars,
            'half' => $halfStar,
            'empty' => $emptyStars
        ];
    }

    /**
     * Obtient le statut de modération formaté
     */
    public function getFormattedModerationStatusAttribute()
    {
        $statuses = [
            self::MODERATION_PENDING => 'En attente',
            self::MODERATION_APPROVED => 'Approuvé',
            self::MODERATION_REJECTED => 'Rejeté',
            self::MODERATION_FLAGGED => 'Signalé'
        ];

        return $statuses[$this->moderation_status] ?? 'Non modéré';
    }

    /**
     * Obtient la couleur du badge de modération
     */
    public function getModerationColorAttribute()
    {
        $colors = [
            self::MODERATION_PENDING => 'yellow',
            self::MODERATION_APPROVED => 'green',
            self::MODERATION_REJECTED => 'red',
            self::MODERATION_FLAGGED => 'orange'
        ];

        return $colors[$this->moderation_status] ?? 'gray';
    }

    /**
     * Vérifie si l'avis a des photos
     */
    public function hasPhotos()
    {
        return $this->photos && count($this->photos) > 0;
    }

    /**
     * Obtient le nombre de photos
     */
    public function getPhotosCountAttribute()
    {
        return $this->photos ? count($this->photos) : 0;
    }

    /**
     * Vérifie si l'avis peut être modifié
     */
    public function canBeEdited()
    {
        // L'avis peut être modifié dans les 24h après création et s'il n'est pas encore modéré
        return $this->created_at->addDay()->isFuture() && 
               !$this->is_moderated;
    }

    /**
     * Vérifie si le prestataire peut répondre
     */
    public function canPrestataireRespond()
    {
        return $this->isApproved() && !$this->prestataire_response;
    }

    /**
     * Obtient la durée depuis la création
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Obtient un résumé de l'avis
     */
    public function getSummaryAttribute()
    {
        $summary = [];
        
        if ($this->would_recommend) {
            $summary[] = 'Recommande ce matériel';
        }
        
        if ($this->verified_rental) {
            $summary[] = 'Location vérifiée';
        }
        
        if ($this->hasPhotos()) {
            $summary[] = $this->photos_count . ' photo(s)';
        }
        
        if ($this->rental_duration_days) {
            $summary[] = 'Loué ' . $this->rental_duration_days . ' jour(s)';
        }

        return implode(' • ', $summary);
    }

    /**
     * Scope pour trier par utilité
     */
    public function scopeOrderByHelpfulness($query)
    {
        return $query->orderByRaw('(helpful_votes / GREATEST(total_votes, 1)) DESC')
                    ->orderBy('helpful_votes', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope pour trier par note
     */
    public function scopeOrderByRating($query, $direction = 'desc')
    {
        return $query->orderBy('rating', $direction)
                    ->orderBy('created_at', 'desc');
    }
}