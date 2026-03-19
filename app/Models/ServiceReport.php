<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
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
        'related_booking_id',
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
    const CATEGORY_INAPPROPRIATE_CONTENT = 'inappropriate_content';
    const CATEGORY_FRAUD = 'fraud';
    const CATEGORY_MISLEADING_INFO = 'misleading_info';
    const CATEGORY_POOR_SERVICE = 'poor_service';
    const CATEGORY_PRICING_ISSUE = 'pricing_issue';
    const CATEGORY_UNAVAILABLE = 'unavailable';
    const CATEGORY_SPAM = 'spam';
    const CATEGORY_COPYRIGHT = 'copyright';
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
     * Relation avec le service signalé
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
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
     * Relation avec la réservation liée (si applicable)
     */
    public function relatedBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'related_booking_id');
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
     * Scope pour les signalements rejetés
     */
    public function scopeDismissed($query)
    {
        return $query->where('status', self::STATUS_DISMISSED);
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
     * Obtenir le libellé de la catégorie
     */
    public function getCategoryLabelAttribute()
    {
        $labels = [
            self::CATEGORY_INAPPROPRIATE_CONTENT => 'Contenu inapproprié',
            self::CATEGORY_FRAUD => 'Fraude',
            self::CATEGORY_MISLEADING_INFO => 'Informations trompeuses',
            self::CATEGORY_POOR_SERVICE => 'Service de mauvaise qualité',
            self::CATEGORY_PRICING_ISSUE => 'Problème de tarification',
            self::CATEGORY_UNAVAILABLE => 'Service indisponible',
            self::CATEGORY_SPAM => 'Spam',
            self::CATEGORY_COPYRIGHT => 'Violation de droits d\'auteur',
            self::CATEGORY_OTHER => 'Autre'
        ];

        return $labels[$this->category] ?? 'Inconnu';
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_UNDER_REVIEW => 'En cours d\'examen',
            self::STATUS_INVESTIGATING => 'En cours d\'enquête',
            self::STATUS_RESOLVED => 'Résolu',
            self::STATUS_DISMISSED => 'Rejeté',
            self::STATUS_ESCALATED => 'Escaladé'
        ];

        return $labels[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtenir le libellé de la priorité
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_MEDIUM => 'Moyenne',
            self::PRIORITY_HIGH => 'Élevée',
            self::PRIORITY_URGENT => 'Urgente'
        ];

        return $labels[$this->priority] ?? 'Inconnu';
    }

    /**
     * Vérifier si le signalement est résolu
     */
    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Vérifier si le signalement est en attente
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifier si le signalement nécessite un suivi
     */
    public function requiresFollowUp()
    {
        return $this->follow_up_required && $this->follow_up_date;
    }

    /**
     * Marquer comme résolu
     */
    public function markAsResolved($adminId, $resolution = null)
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_by' => $adminId,
            'resolved_at' => now(),
            'resolution' => $resolution
        ]);
    }

    /**
     * Marquer comme rejeté
     */
    public function markAsDismissed($adminId, $reason = null)
    {
        $this->update([
            'status' => self::STATUS_DISMISSED,
            'resolved_by' => $adminId,
            'resolved_at' => now(),
            'admin_notes' => $reason
        ]);
    }
}