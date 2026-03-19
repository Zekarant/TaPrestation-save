<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'booking_id',
        'equipment_rental_id',
        'food_order_id',
        'amount',
        'status',
        'type',
        'currency',
        'payment_method',
        'description',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'receipt_url',
        'failure_reason',
        'metadata',
        'paid_at',
        'refunded_at',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'json',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected $hidden = [
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'transaction_id',
        'receipt_url',
        'metadata',
    ];

    public static function systemCreate(array $attributes): self
    {
        $transaction = new static();
        $transaction->forceFill($attributes);
        $transaction->save();

        return $transaction;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function equipmentRentalRequest(): BelongsTo
    {
        return $this->belongsTo(EquipmentRentalRequest::class, 'equipment_rental_id');
    }

    /**
     * Alias for backward compatibility.
     */
    public function equipmentRental(): BelongsTo
    {
        return $this->equipmentRentalRequest();
    }

    public function foodOrder(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class, 'transaction_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    public function isDisputed(): bool
    {
        return $this->status === 'disputed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['paid', 'released', 'completed']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeHeld($query)
    {
        return $query->where('status', 'held');
    }

    public function scopeRefunded($query)
    {
        return $query->whereIn('status', ['refunded', 'partially_refunded']);
    }

    public function scopeRecent($query)
    {
        return $query->latest();
    }
}
