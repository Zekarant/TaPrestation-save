<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchingAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'saved_search_id',
        'prestataire_id',
        'matching_score',
        'alert_data',
        'is_read',
        'is_dismissed'
    ];

    protected $casts = [
        'alert_data' => 'array',
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'matching_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec la recherche sauvegardée
     */
    public function savedSearch(): BelongsTo
    {
        return $this->belongsTo(SavedSearch::class);
    }

    /**
     * Relation avec le prestataire correspondant
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    /**
     * Relation avec l'utilisateur via la recherche sauvegardée
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            SavedSearch::class,
            'id',
            'id',
            'saved_search_id',
            'user_id'
        );
    }

    /**
     * Marque l'alerte comme lue
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Marque l'alerte comme non lue
     */
    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Rejette l'alerte
     */
    public function dismiss(): void
    {
        $this->update(['is_dismissed' => true]);
    }

    /**
     * Annule le rejet de l'alerte
     */
    public function undismiss(): void
    {
        $this->update(['is_dismissed' => false]);
    }

    /**
     * Vérifie si l'alerte est nouvelle (non lue et non rejetée)
     */
    public function isNew(): bool
    {
        return !$this->is_read && !$this->is_dismissed;
    }

    /**
     * Récupère le score de correspondance formaté
     */
    public function getFormattedScoreAttribute(): string
    {
        return number_format($this->matching_score, 1) . '%';
    }

    /**
     * Récupère le niveau de correspondance basé sur le score
     */
    public function getMatchLevelAttribute(): string
    {
        if ($this->matching_score >= 90) {
            return 'excellent';
        } elseif ($this->matching_score >= 75) {
            return 'very_good';
        } elseif ($this->matching_score >= 60) {
            return 'good';
        } elseif ($this->matching_score >= 40) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Récupère le nom du niveau de correspondance en français
     */
    public function getMatchLevelNameAttribute(): string
    {
        $levels = [
            'excellent' => 'Excellente correspondance',
            'very_good' => 'Très bonne correspondance',
            'good' => 'Bonne correspondance',
            'fair' => 'Correspondance correcte',
            'poor' => 'Correspondance faible'
        ];

        return $levels[$this->match_level] ?? 'Correspondance inconnue';
    }

    /**
     * Récupère la couleur associée au niveau de correspondance
     */
    public function getMatchLevelColorAttribute(): string
    {
        $colors = [
            'excellent' => 'green',
            'very_good' => 'blue',
            'good' => 'yellow',
            'fair' => 'orange',
            'poor' => 'red'
        ];

        return $colors[$this->match_level] ?? 'gray';
    }

    /**
     * Récupère les détails de correspondance formatés
     */
    public function getMatchingDetailsAttribute(): array
    {
        $alertData = $this->alert_data ?? [];
        $details = [];

        if (isset($alertData['matching_criteria'])) {
            foreach ($alertData['matching_criteria'] as $criterion => $match) {
                $details[] = [
                    'criterion' => $this->formatCriterionName($criterion),
                    'matched' => $match['matched'] ?? false,
                    'score' => $match['score'] ?? 0,
                    'details' => $match['details'] ?? ''
                ];
            }
        }

        return $details;
    }

    /**
     * Formate le nom d'un critère pour l'affichage
     */
    private function formatCriterionName(string $criterion): string
    {
        $names = [
            'keyword' => 'Mot-clé',
            'category' => 'Catégorie',
            'skills' => 'Compétences',
            'location' => 'Localisation',
            'price_range' => 'Gamme de prix',
            'rating' => 'Note',
            'availability' => 'Disponibilité',
            'experience' => 'Expérience',
            'verification' => 'Vérifications'
        ];

        return $names[$criterion] ?? ucfirst($criterion);
    }

    /**
     * Scope pour récupérer uniquement les alertes non lues
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope pour récupérer uniquement les alertes non rejetées
     */
    public function scopeNotDismissed($query)
    {
        return $query->where('is_dismissed', false);
    }

    /**
     * Scope pour récupérer uniquement les nouvelles alertes
     */
    public function scopeNew($query)
    {
        return $query->unread()->notDismissed();
    }

    /**
     * Scope pour récupérer les alertes d'un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('savedSearch', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope pour récupérer les alertes par niveau de correspondance
     */
    public function scopeByMatchLevel($query, $level)
    {
        $ranges = [
            'excellent' => [90, 100],
            'very_good' => [75, 89.99],
            'good' => [60, 74.99],
            'fair' => [40, 59.99],
            'poor' => [0, 39.99]
        ];

        if (isset($ranges[$level])) {
            return $query->whereBetween('matching_score', $ranges[$level]);
        }

        return $query;
    }

    /**
     * Scope pour récupérer les alertes avec un score minimum
     */
    public function scopeWithMinScore($query, $minScore)
    {
        return $query->where('matching_score', '>=', $minScore);
    }

    /**
     * Scope pour ordonner par score de correspondance décroissant
     */
    public function scopeOrderByScore($query)
    {
        return $query->orderByDesc('matching_score');
    }

    /**
     * Scope pour ordonner par date de création décroissante
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Génère une URL vers le profil du prestataire
     */
    public function getPrestataireUrlAttribute(): string
    {
        return route('prestataires.show', $this->prestataire_id);
    }

    /**
     * Génère une URL vers la conversation avec le prestataire
     */
    public function getConversationUrlAttribute(): string
    {
        return route('messaging.show', $this->prestataire_id);
    }
}