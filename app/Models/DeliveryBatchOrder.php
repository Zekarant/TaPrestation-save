<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryBatchOrder extends Model
{
    protected $fillable = [
        'batch_id',
        'food_order_id',
        'pickup_order',
        'delivery_order',
        'distance_to_pickup',
        'distance_to_dropoff',
        'picked_up_at',
        'delivered_at',
    ];

    protected $casts = [
        'pickup_order' => 'integer',
        'delivery_order' => 'integer',
        'distance_to_pickup' => 'decimal:2',
        'distance_to_dropoff' => 'decimal:2',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DeliveryBatch::class, 'batch_id');
    }

    public function foodOrder(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }

    /**
     * Marquer comme récupéré
     */
    public function markPickedUp(): void
    {
        $this->update(['picked_up_at' => now()]);
        
        $this->foodOrder->update([
            'delivery_status' => FoodOrder::DELIVERY_STATUS_PICKED_UP,
            'picked_up_at' => now(),
        ]);
    }

    /**
     * Marquer comme livré
     */
    public function markDelivered(): void
    {
        $this->update(['delivered_at' => now()]);
        
        $this->foodOrder->update([
            'delivery_status' => FoodOrder::DELIVERY_STATUS_DELIVERED,
            'delivered_at' => now(),
            'status' => FoodOrder::STATUS_DELIVERED,
        ]);
    }
}
