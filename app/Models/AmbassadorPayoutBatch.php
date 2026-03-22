<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmbassadorPayoutBatch extends Model
{
    protected $fillable = [
        'ambassador_id',
        'total_amount',
        'stripe_transfer_id',
        'status',
        'processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function ambassador()
    {
        return $this->belongsTo(Ambassador::class);
    }

    public function commissions()
    {
        return $this->hasMany(AmbassadorCommission::class, 'payout_batch_id');
    }
}
