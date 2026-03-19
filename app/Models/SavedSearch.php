<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'search_criteria',
        'alert_frequency',
        'is_active',
        'last_alert_sent'
    ];

    protected $casts = [
        'search_criteria' => 'array',
        'is_active' => 'boolean',
        'last_alert_sent' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les fréquences d'alerte disponibles
     */
    const ALERT_FREQUENCIES = [
        'immediate' => 'Immédiate',
        'daily' => 'Quotidienne',
        'weekly' => 'Hebdomadaire',
        'monthly' => 'Mensuelle',
        'never' => 'Jamais'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les alertes de correspondance
     */
    public function matchingAlerts(): HasMany
    {
        return $this->hasMany(MatchingAlert::class);
    }

    /**
     * Récupère le nom de la fréquence d'alerte en français
     */
    public function getAlertFrequencyNameAttribute(): string
    {
        return self::ALERT_FREQUENCIES[$this->alert_frequency] ?? 'Inconnu';
    }

    /**
     * Vérifie si une alerte doit être envoyée
     */
    public function shouldSendAlert(): bool
    {
        if (!$this->is_active || $this->alert_frequency === 'never') {
            return false;
        }

        if ($this->alert_frequency === 'immediate') {
            return true;
        }

        if (is_null($this->last_alert_sent)) {
            return true;
        }

        $now = now();
        $lastAlert = $this->last_alert_sent;

        switch ($this->alert_frequency) {
            case 'daily':
                return $lastAlert->diffInDays($now) >= 1;
            case 'weekly':
                return $lastAlert->diffInWeeks($now) >= 1;
            case 'monthly':
                return $lastAlert->diffInMonths($now) >= 1;
            default:
                return false;
        }
    }

    /**
     * Marque l'alerte comme envoyée
     */
    public function markAlertSent(): void
    {
        $this->update(['last_alert_sent' => now()]);
    }

    /**
     * Active la recherche sauvegardée
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Désactive la recherche sauvegardée
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Récupère les critères de recherche formatés
     */
    public function getFormattedCriteriaAttribute(): array
    {
        $criteria = $this->search_criteria ?? [];
        $formatted = [];

        if (isset($criteria['keyword']) && !empty($criteria['keyword'])) {
            $formatted['Mot-clé'] = $criteria['keyword'];
        }

        if (isset($criteria['category_id']) && !empty($criteria['category_id'])) {
            $category = \App\Models\Category::find($criteria['category_id']);
            $formatted['Catégorie'] = $category ? $category->name : 'Catégorie supprimée';
        }

        if (isset($criteria['skills']) && !empty($criteria['skills'])) {
            $skills = \App\Models\Skill::whereIn('id', $criteria['skills'])->pluck('name')->toArray();
            $formatted['Compétences'] = implode(', ', $skills);
        }

        if (isset($criteria['location']) && !empty($criteria['location'])) {
            $formatted['Localisation'] = $criteria['location'];
        }

        if (isset($criteria['radius']) && !empty($criteria['radius'])) {
            $formatted['Rayon'] = $criteria['radius'] . ' km';
        }

        // Critères de prix supprimés pour confidentialité
        // if (isset($criteria['min_price']) && !empty($criteria['min_price'])) {
        //     $formatted['Prix minimum'] = $criteria['min_price'] . ' €';
        // }
        // 
        // if (isset($criteria['max_price']) && !empty($criteria['max_price'])) {
        //     $formatted['Prix maximum'] = $criteria['max_price'] . ' €';
        // }

        if (isset($criteria['min_rating']) && !empty($criteria['min_rating'])) {
            $formatted['Note minimum'] = $criteria['min_rating'] . '/5';
        }

        if (isset($criteria['availability']) && !empty($criteria['availability'])) {
            $formatted['Disponibilité'] = $criteria['availability'] === 'available' ? 'Disponible' : 'Non spécifiée';
        }

        return $formatted;
    }

    /**
     * Scope pour récupérer uniquement les recherches actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour récupérer les recherches d'un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour récupérer les recherches qui nécessitent une alerte
     */
    public function scopeNeedingAlert($query)
    {
        return $query->active()->where('alert_frequency', '!=', 'never');
    }

    /**
     * Scope pour récupérer les recherches par fréquence d'alerte
     */
    public function scopeByAlertFrequency($query, $frequency)
    {
        return $query->where('alert_frequency', $frequency);
    }

    /**
     * Génère une URL de recherche basée sur les critères
     */
    public function getSearchUrlAttribute(): string
    {
        $criteria = $this->search_criteria ?? [];
        $params = [];

        foreach ($criteria as $key => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $params[$key] = implode(',', $value);
                } else {
                    $params[$key] = $value;
                }
            }
        }

        $queryString = http_build_query($params);
        return route('search.prestataires') . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Compte le nombre d'alertes reçues
     */
    public function getAlertCountAttribute(): int
    {
        return $this->matchingAlerts()->count();
    }

    /**
     * Récupère la dernière alerte reçue
     */
    public function getLastAlertAttribute()
    {
        return $this->matchingAlerts()->latest()->first();
    }
}