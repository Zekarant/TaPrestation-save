<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class EquipmentRentalRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipment_id',
        'client_id',
        'prestataire_id',
        'start_date',
        'end_date',
        'status',

        'request_number',
        'duration_days',
        'unit_price',
        'total_amount',
        'security_deposit',
        'final_amount',
        'pickup_address',
        'rejection_reason',
        'responded_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',

        'insurance_accepted' => 'boolean',
        'terms_accepted' => 'boolean',
        'responded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_days' => 'integer',
        'special_requirements' => 'array'
    ];

    /**
     * Statuts possibles pour une demande de location
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Relation avec l'équipement
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
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
     * Relation avec la location effective
     */
    public function rental(): HasOne
    {
        return $this->hasOne(EquipmentRental::class, 'rental_request_id');
    }

    /**
     * Scope pour les demandes en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope pour les demandes acceptées
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope pour les demandes rejetées
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope pour les demandes expirées
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere(function($q) {
                        $q->where('status', self::STATUS_PENDING)
                          ->where('created_at', '<', now()->subDays(7));
                    });
    }

    /**
     * Vérifie si la demande est en attente
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifie si la demande est acceptée
     */
    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Vérifie si la demande est rejetée
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Vérifie si la demande est annulée
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Vérifie si la demande est expirée
     */
    public function isExpired()
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->status === self::STATUS_PENDING && $this->created_at->addDays(7)->isPast());
    }

    /**
     * Accepte la demande de location
     */
    public function accept($response = null)
    {
        // Check if equipment exists
        if (!$this->equipment) {
            throw new \Exception('Équipement introuvable.');
        }
        
        // Check if equipment is available for the requested period
        if (!$this->equipment->isAvailableForPeriod($this->start_date, $this->end_date)) {
            throw new \Exception('L\'équipement est déjà loué pour cette période.');
        }
        
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'prestataire_response' => $response,
            'responded_at' => now()
        ]);
    }

    /**
     * Rejette la demande de location
     */
    public function reject($reason = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'responded_at' => now()
        ]);
    }

    /**
     * Annule la demande de location
     */
    public function cancel($reason = null, $cancelledBy = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => now()
        ]);
    }

    /**
     * Marque la demande comme expirée
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => self::STATUS_EXPIRED
        ]);
    }

    /**
     * Calcule le montant total
     */
    public function calculateTotalAmount()
    {
        $baseAmount = $this->daily_rate * $this->total_days;
        return $baseAmount;
    }

    /**
     * Obtient le statut formaté
     */
    public function getFormattedStatusAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_ACCEPTED => 'Acceptée',
            self::STATUS_REJECTED => 'Refusée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_EXPIRED => 'Expirée'
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtient la couleur du badge de statut
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'yellow',
            self::STATUS_ACCEPTED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_CANCELLED => 'gray',
            self::STATUS_EXPIRED => 'red'
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Vérifie si la demande peut être modifiée
     */
    public function canBeModified()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifie si la demande peut être annulée
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACCEPTED]);
    }

    /**
     * Obtient le délai de réponse restant
     */
    public function getRemainingResponseTimeAttribute()
    {
        if ($this->status !== self::STATUS_PENDING) {
            return null;
        }

        $deadline = $this->created_at->addDays(7);
        $now = now();

        if ($deadline->isPast()) {
            return 'Expiré';
        }

        return $deadline->diffForHumans($now);
    }

    /**
     * Vérifie si les dates sont valides
     */
    public function hasValidDates()
    {
        return $this->start_date && 
               $this->end_date && 
               $this->start_date->isFuture() && 
               $this->end_date->isAfter($this->start_date);
    }
}