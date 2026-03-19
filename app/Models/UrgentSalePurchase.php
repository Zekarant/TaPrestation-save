<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UrgentSalePurchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'urgent_sale_id',
        'buyer_user_id',
        'payment_transaction_id',
        'escrow_id',
        'quantity',
        'unit_price',
        'total_amount',
        'currency',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function urgentSale(): BelongsTo
    {
        return $this->belongsTo(UrgentSale::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    /**
     * Alias pour la relation transaction (utilisé par le sync escrow)
     */
    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    /**
     * Relation vers l'escrow associé
     */
    public function escrow()
    {
        return $this->belongsTo(\Illuminate\Database\Eloquent\Model::class, 'escrow_id', 'id')
            ->from('escrow_transactions');
    }
}
