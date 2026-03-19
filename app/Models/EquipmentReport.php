<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipment_id',
        'reporter_id',
        'reporter_type',
        'reason',
        'category',
        'description',
        'evidence_photos',
        'contact_info',
        'status',
        'priority',
        'admin_notes',
        'resolution',
        'resolved_at',
        'resolved_by',
        'follow_up_required',
        'follow_up_date',
        'related_rental_id',
        'reporter_ip',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'evidence_photos' => 'array',
        'contact_info' => 'array',
        'follow_up_required' => 'boolean',
        'resolved_at' => 'datetime',
        'follow_up_date' => 'datetime'
    ];

    /**
     * Types de rapporteurs
     */
    const REPORTER_CLIENT = 'client';
    const REPORTER_PRESTATAIRE = 'prestataire';
    const REPORTER_ANONYMOUS = 'anonymous';

    /**
     * Catégories de signalement
     */
    const CATEGORY_SAFETY = 'safety';
    const CATEGORY_CONDITION = 'condition';
    const CATEGORY_FRAUD = 'fraud';
    const CATEGORY_INAPPROPRIATE = 'inappropriate';
    const CATEGORY_PRICING = 'pricing';
    const CATEGORY_AVAILABILITY = 'availability';
    const CATEGORY_OTHER = 'other';

    /**
     * Statuts de signalement
     */
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_INVESTIGATING = 'investigating';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_DISMISSED = 'dismissed';
    const STATUS_ESCALATED = 'escalated';

    /**
     * Niveaux de priorité
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Relation avec l'équipement signalé
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Relation avec l'utilisateur qui a fait le signalement (polymorphe)
     */
    public function reporter()
    {
        if ($this->reporter_type === self::REPORTER_CLIENT) {
            return $this->belongsTo(Client::class, 'reporter_id');
        } elseif ($this->reporter_type === self::REPORTER_PRESTATAIRE) {
            return $this->belongsTo(Prestataire::class, 'reporter_id');
        }
        
        return null;
    }

    /**
     * Relation avec la location liée (si applicable)
     */
    public function relatedRental(): BelongsTo
    {
        return $this->belongsTo(EquipmentRental::class, 'related_rental_id');
    }

    /**
     * Relation avec l'administrateur qui a résolu le signalement
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope pour les signalements en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope pour les signalements en cours de traitement
     */
    public function scopeUnderReview($query)
    {
        return $query->whereIn('status', [
            self::STATUS_UNDER_REVIEW,
            self::STATUS_INVESTIGATING
        ]);
    }

    /**
     * Scope pour les signalements résolus
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope pour les signalements par priorité
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope pour les signalements urgents
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    /**
     * Scope pour les signalements par catégorie
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope pour les signalements nécessitant un suivi
     */
    public function scopeRequiringFollowUp($query)
    {
        return $query->where('follow_up_required', true)
                    ->where('status', '!=', self::STATUS_RESOLVED);
    }

    /**
     * Vérifie si le signalement est en attente
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifie si le signalement est en cours de traitement
     */
    public function isUnderReview()
    {
        return in_array($this->status, [
            self::STATUS_UNDER_REVIEW,
            self::STATUS_INVESTIGATING
        ]);
    }

    /**
     * Vérifie si le signalement est résolu
     */
    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Vérifie si le signalement est urgent
     */
    public function isUrgent()
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    /**
     * Marque le signalement comme en cours de traitement
     */
    public function markAsUnderReview($adminNotes = null)
    {
        $this->update([
            'status' => self::STATUS_UNDER_REVIEW,
            'admin_notes' => $adminNotes
        ]);
    }

    /**
     * Marque le signalement comme en cours d'investigation
     */
    public function markAsInvestigating($adminNotes = null)
    {
        $this->update([
            'status' => self::STATUS_INVESTIGATING,
            'admin_notes' => $adminNotes
        ]);
    }

    /**
     * Résout le signalement
     */
    public function resolve($resolution, $resolvedBy = null)
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolution' => $resolution,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy
        ]);
    }

    /**
     * Rejette le signalement
     */
    public function dismiss($reason, $resolvedBy = null)
    {
        $this->update([
            'status' => self::STATUS_DISMISSED,
            'resolution' => $reason,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy
        ]);
    }

    /**
     * Escalade le signalement
     */
    public function escalate($reason = null)
    {
        $this->update([
            'status' => self::STATUS_ESCALATED,
            'priority' => self::PRIORITY_URGENT,
            'admin_notes' => $reason
        ]);
    }

    /**
     * Définit un suivi
     */
    public function setFollowUp($date, $required = true)
    {
        $this->update([
            'follow_up_required' => $required,
            'follow_up_date' => $date
        ]);
    }

    /**
     * Obtient la catégorie formatée
     */
    public function getFormattedCategoryAttribute()
    {
        $categories = [
            self::CATEGORY_SAFETY => 'Sécurité',
            self::CATEGORY_CONDITION => 'État du matériel',
            self::CATEGORY_FRAUD => 'Fraude',
            self::CATEGORY_INAPPROPRIATE => 'Contenu inapproprié',
            self::CATEGORY_PRICING => 'Prix abusif',
            self::CATEGORY_AVAILABILITY => 'Disponibilité',
            self::CATEGORY_OTHER => 'Autre'
        ];

        return $categories[$this->category] ?? 'Non spécifié';
    }

    /**
     * Obtient le statut formaté
     */
    public function getFormattedStatusAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_UNDER_REVIEW => 'En cours d\'examen',
            self::STATUS_INVESTIGATING => 'En cours d\'investigation',
            self::STATUS_RESOLVED => 'Résolu',
            self::STATUS_DISMISSED => 'Rejeté',
            self::STATUS_ESCALATED => 'Escaladé'
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtient la priorité formatée
     */
    public function getFormattedPriorityAttribute()
    {
        $priorities = [
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_MEDIUM => 'Moyenne',
            self::PRIORITY_HIGH => 'Élevée',
            self::PRIORITY_URGENT => 'Urgente'
        ];

        return $priorities[$this->priority] ?? 'Non spécifiée';
    }

    /**
     * Obtient la couleur du badge de statut
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'yellow',
            self::STATUS_UNDER_REVIEW => 'blue',
            self::STATUS_INVESTIGATING => 'indigo',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_DISMISSED => 'gray',
            self::STATUS_ESCALATED => 'red'
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Obtient la couleur du badge de priorité
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            self::PRIORITY_LOW => 'green',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red'
        ];

        return $colors[$this->priority] ?? 'gray';
    }

    /**
     * Obtient l'icône de la catégorie
     */
    public function getCategoryIconAttribute()
    {
        $icons = [
            self::CATEGORY_SAFETY => 'shield-exclamation',
            self::CATEGORY_CONDITION => 'wrench',
            self::CATEGORY_FRAUD => 'exclamation-triangle',
            self::CATEGORY_INAPPROPRIATE => 'flag',
            self::CATEGORY_PRICING => 'currency-dollar',
            self::CATEGORY_AVAILABILITY => 'calendar-x',
            self::CATEGORY_OTHER => 'question-mark-circle'
        ];

        return $icons[$this->category] ?? 'question-mark-circle';
    }

    /**
     * Vérifie si le signalement a des preuves photos
     */
    public function hasEvidencePhotos()
    {
        return $this->evidence_photos && count($this->evidence_photos) > 0;
    }

    /**
     * Obtient le nombre de photos de preuve
     */
    public function getEvidencePhotosCountAttribute()
    {
        return $this->evidence_photos ? count($this->evidence_photos) : 0;
    }

    /**
     * Vérifie si le signalement nécessite une action immédiate
     */
    public function requiresImmediateAction()
    {
        return $this->priority === self::PRIORITY_URGENT || 
               $this->category === self::CATEGORY_SAFETY;
    }

    /**
     * Obtient le délai de traitement recommandé
     */
    public function getRecommendedProcessingTimeAttribute()
    {
        $times = [
            self::PRIORITY_URGENT => '2 heures',
            self::PRIORITY_HIGH => '24 heures',
            self::PRIORITY_MEDIUM => '3 jours',
            self::PRIORITY_LOW => '7 jours'
        ];

        return $times[$this->priority] ?? '7 jours';
    }

    /**
     * Vérifie si le signalement est en retard de traitement
     */
    public function isOverdue()
    {
        $deadlines = [
            self::PRIORITY_URGENT => 2, // heures
            self::PRIORITY_HIGH => 24, // heures
            self::PRIORITY_MEDIUM => 72, // heures (3 jours)
            self::PRIORITY_LOW => 168 // heures (7 jours)
        ];

        $deadline = $deadlines[$this->priority] ?? 168;
        
        return $this->created_at->addHours($deadline)->isPast() && 
               !$this->isResolved();
    }
}