<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmbassadorCommission extends Model
{
    protected $fillable = [
        'ambassador_id',
        'prestataire_id',
        'escrow_transaction_id',
        'booking_id',
        'order_type',
        'base_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'paid_at',
        'payout_batch_id',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function ambassador()
    {
        return $this->belongsTo(Ambassador::class);
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function payoutBatch()
    {
        return $this->belongsTo(AmbassadorPayoutBatch::class, 'payout_batch_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
