<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_order_id',
        'food_product_id',
        'product_name',
        'unit_price',
        'quantity',
        'total_price',
        'options',
        'special_instructions',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'options' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->total_price = $item->unit_price * $item->quantity;
        });

        static::updating(function ($item) {
            $item->total_price = $item->unit_price * $item->quantity;
        });
    }

    public function order()
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }

    public function foodProduct()
    {
        return $this->belongsTo(FoodProduct::class);
    }

    public function getFormattedOptionsAttribute(): string
    {
        if (empty($this->options)) {
            return '';
        }

        return collect($this->options)->map(function ($value, $key) {
            return "{$key}: {$value}";
        })->implode(', ');
    }
}
