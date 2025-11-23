<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class EquipmentRental extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rental_request_id',
        'equipment_id',
        'client_id',
        'prestataire_id',
        'rental_number',
        'start_date',
        'end_date',
        'actual_start_datetime',
        'actual_end_datetime',
        'planned_duration_days',
        'actual_duration_days',
        'unit_price',
        'base_amount',
        'security_deposit',

        'pickup_fee',
        'late_fee',
        'damage_fee',
        'cleaning_fee',
        'additional_fees',
        'discount_amount',
        'total_amount',
        'final_amount',
        'deposit_returned',
        'deposit_retained',

        'pickup_address',

        'picked_up_at',

        'picked_up_by',
        'status',
        'payment_status',

        'pickup_notes',
        'condition_notes',

        'pickup_photos',
        'equipment_condition_delivered',
        'equipment_condition_returned',
        'damage_report',
        'damage_photos',
        'late_return',
        'late_days',
        'late_hours',

        'client_signature_pickup',

        'prestataire_signature_pickup',

        'client_validated_pickup_at',
        'metadata',
        'internal_notes',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'completed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',

        'picked_up_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',

        'pickup_fee' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'damage_fee' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'additional_fees' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'deposit_returned' => 'decimal:2',
        'deposit_retained' => 'decimal:2',
        'planned_duration_days' => 'integer',
        'actual_duration_days' => 'integer',
        'late_days' => 'integer',
        'late_hours' => 'integer',
        'late_return' => 'boolean',

        'pickup_photos' => 'array',
        'damage_photos' => 'array',
        'metadata' => 'array',

        'client_validated_pickup_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Statuts possibles pour une location
     */
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';


    const STATUS_ACTIVE = 'active';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_RETURNED = 'returned';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DISPUTED = 'disputed';

    /**
     * Statuts de paiement
     */
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_DEPOSIT_PAID = 'deposit_paid';
    const PAYMENT_FULL_PAID = 'full_paid';
    const PAYMENT_REFUND_PENDING = 'refund_pending';
    const PAYMENT_COMPLETED = 'completed';

    /**
     * Boot method pour générer automatiquement le numéro de location
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rental) {
            if (!$rental->rental_number) {
                $rental->rental_number = 'LOC-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relation avec la demande de location originale
     */
    public function rentalRequest(): BelongsTo
    {
        return $this->belongsTo(EquipmentRentalRequest::class, 'rental_request_id');
    }

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
     * Relation avec l'avis (un seul avis par location)
     */
    public function review(): HasOne
    {
        return $this->hasOne(EquipmentReview::class);
    }

    /**
     * Scope pour les locations actives
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE]);
    }

    /**
     * Scope pour les locations en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                    ->orWhere(function($q) {
                        $q->whereIn('status', [self::STATUS_ACTIVE])
                          ->where('end_date', '<', now()->toDateString());
                    });
    }

    /**
     * Scope pour les locations terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope pour les locations en cours de préparation
     */
    public function scopePreparing($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PREPARING]);
    }

    /**
     * Vérifie si la location est active
     */
    public function isActive()
    {
        return in_array($this->status, [self::STATUS_ACTIVE]);
    }

    /**
     * Vérifie si la location est en retard
     */
    public function isOverdue()
    {
        return $this->status === self::STATUS_OVERDUE || 
               ($this->isActive() && Carbon::parse($this->end_date)->isPast());
    }

    /**
     * Vérifie si la location est terminée
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }



    /**
     * Marque la location comme retournée
     */
    public function markAsReturned($condition = null, $damageReported = false, $damageDescription = null)
    {
        $updateData = [
            'status' => self::STATUS_RETURNED,
            'returned_at' => now(),
            'actual_end_date' => now()->toDateString(),
            'condition_after' => $condition,
            'damage_reported' => $damageReported,
            'damage_description' => $damageDescription
        ];

        // Calculer les frais de retard si applicable
        if ($this->isOverdue()) {
            $daysLate = Carbon::parse($this->end_date)->diffInDays(now());
            $updateData['late_return_fee'] = $this->daily_rate * $daysLate * 0.5; // 50% du tarif journalier
        }

        $this->update($updateData);

        // Libérer l'équipement
        $this->equipment->update(['availability_status' => 'available']);
    }

    /**
     * Marque la location comme terminée
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'payment_status' => self::PAYMENT_COMPLETED
        ]);
    }

    /**
     * Annule la location
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason
        ]);

        // Libérer l'équipement si nécessaire
        if ($this->equipment->availability_status === 'rented') {
            $this->equipment->update(['availability_status' => 'available']);
        }
    }

    /**
     * Calcule le montant total final (avec frais supplémentaires)
     */
    public function calculateFinalAmount()
    {
        $baseAmount = $this->total_amount;
        $additionalFees = ($this->late_return_fee ?? 0) + 
                         ($this->cleaning_fee ?? 0) + 
                         ($this->damage_cost ?? 0);
        
        return $baseAmount + $additionalFees;
    }

    /**
     * Calcule le montant de la caution à retourner
     */
    public function calculateDepositReturn()
    {
        $depositPaid = $this->deposit_paid ?? $this->deposit_amount;
        $deductions = ($this->damage_cost ?? 0) + ($this->cleaning_fee ?? 0);
        
        return max(0, $depositPaid - $deductions);
    }

    /**
     * Obtient le statut formaté
     */
    public function getFormattedStatusAttribute()
    {
        $statuses = [
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_PREPARING => 'En préparation',
            self::STATUS_ACTIVE => 'En cours',
            self::STATUS_OVERDUE => 'En retard',
            self::STATUS_RETURNED => 'Retournée',
            self::STATUS_COMPLETED => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_DISPUTED => 'En litige'
        ];

        return $statuses[$this->status] ?? 'Inconnu';
    }

    /**
     * Obtient la couleur du badge de statut
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_PREPARING => 'yellow',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_OVERDUE => 'red',
            self::STATUS_RETURNED => 'purple',
            self::STATUS_COMPLETED => 'gray',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_DISPUTED => 'orange'
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Obtient le statut de paiement formaté
     */
    public function getFormattedPaymentStatusAttribute()
    {
        $statuses = [
            self::PAYMENT_PENDING => 'En attente',
            self::PAYMENT_DEPOSIT_PAID => 'Acompte payé',
            self::PAYMENT_FULL_PAID => 'Payé intégralement',
            self::PAYMENT_REFUND_PENDING => 'Remboursement en cours',
            self::PAYMENT_COMPLETED => 'Terminé'
        ];

        return $statuses[$this->payment_status] ?? 'Inconnu';
    }

    /**
     * Vérifie si la location peut être annulée
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,

        ]);
    }

    /**
     * Vérifie si la location peut être évaluée
     */
    public function canBeReviewed()
    {
        return $this->status === self::STATUS_COMPLETED && !$this->review;
    }

    /**
     * Obtient la durée effective de la location
     */
    public function getActualDurationAttribute()
    {
        if (!$this->actual_start_date || !$this->actual_end_date) {
            return null;
        }

        return Carbon::parse($this->actual_start_date)->diffInDays(Carbon::parse($this->actual_end_date)) + 1;
    }

    /**
     * Obtient le nombre de jours de retard
     */
    public function getDaysLateAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $endDate = Carbon::parse($this->end_date);
        $returnDate = $this->actual_end_date ? Carbon::parse($this->actual_end_date) : now();
        
        return $endDate->diffInDays($returnDate);
    }
}