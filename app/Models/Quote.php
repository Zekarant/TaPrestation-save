<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prestataire_id',
        'client_id',
        'service_id',
        'title',
        'description',
        'items',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'discount_type',
        'total',
        'currency',
        'valid_until',
        'notes',
        'terms',
        'status',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'reference_number',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Statuts possibles
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_VIEWED = 'viewed';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Types de remise
    const DISCOUNT_PERCENTAGE = 'percentage';
    const DISCOUNT_FIXED = 'fixed';

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            // Générer le numéro de référence
            $quote->reference_number = self::generateReferenceNumber($quote->prestataire_id);
        });
    }

    /**
     * Générer un numéro de référence unique
     */
    public static function generateReferenceNumber($prestataireId): string
    {
        $year = date('Y');
        $month = date('m');
        $count = self::where('prestataire_id', $prestataireId)
            ->whereYear('created_at', $year)
            ->count() + 1;
        
        return sprintf('DEV-%s%s-%04d', $year, $month, $count);
    }

    /**
     * Relations
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_VIEWED]);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
            ->orWhere(function ($q) {
                $q->whereIn('status', [self::STATUS_SENT, self::STATUS_VIEWED])
                    ->where('valid_until', '<', now());
            });
    }

    public function scopeForPrestataire($query, $prestataireId)
    {
        return $query->where('prestataire_id', $prestataireId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Accesseurs
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2, ',', ' ') . ' ' . $this->currency;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_SENT => 'Envoyé',
            self::STATUS_VIEWED => 'Consulté',
            self::STATUS_ACCEPTED => 'Accepté',
            self::STATUS_REJECTED => 'Refusé',
            self::STATUS_EXPIRED => 'Expiré',
            self::STATUS_CANCELLED => 'Annulé',
            default => 'Inconnu',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SENT => 'blue',
            self::STATUS_VIEWED => 'yellow',
            self::STATUS_ACCEPTED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_EXPIRED => 'orange',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function getCanBeEditedAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT]);
    }

    public function getCanBeSentAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT && !$this->is_expired;
    }

    public function getCanBeAcceptedAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_VIEWED]) && !$this->is_expired;
    }

    /**
     * Actions
     */
    public function send(): bool
    {
        if (!$this->can_be_sent) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);

        // Notifier le client
        if ($this->client && $this->client->user) {
            try {
                $this->load(['prestataire.user', 'service']);
                $this->client->user->notify(new \App\Notifications\QuoteSentNotification($this));
            } catch (\Exception $e) {
                \Log::error('Failed to notify client of quote', ['error' => $e->getMessage()]);
            }
        }

        return true;
    }

    public function markAsViewed(): bool
    {
        if ($this->status !== self::STATUS_SENT) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_VIEWED,
            'viewed_at' => now(),
        ]);

        return true;
    }

    public function accept(): bool
    {
        if (!$this->can_be_accepted) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        // TODO: Créer une réservation à partir du devis
        return true;
    }

    public function reject(?string $reason = null): bool
    {
        if (!$this->can_be_accepted) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if ($this->status === self::STATUS_ACCEPTED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);

        return true;
    }

    /**
     * Calculer le total
     */
    public function calculateTotal(): void
    {
        $subtotal = 0;
        
        if (is_array($this->items)) {
            foreach ($this->items as $item) {
                $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
            }
        }

        $this->subtotal = $subtotal;
        
        // Appliquer la remise
        $discountAmount = 0;
        if ($this->discount_amount > 0) {
            if ($this->discount_type === self::DISCOUNT_PERCENTAGE) {
                $discountAmount = $subtotal * ($this->discount_amount / 100);
            } else {
                $discountAmount = $this->discount_amount;
            }
        }

        $afterDiscount = $subtotal - $discountAmount;

        // Calculer la taxe
        $taxAmount = 0;
        if ($this->tax_rate > 0) {
            $taxAmount = $afterDiscount * ($this->tax_rate / 100);
        }

        $this->tax_amount = $taxAmount;
        $this->total = $afterDiscount + $taxAmount;
    }
}
