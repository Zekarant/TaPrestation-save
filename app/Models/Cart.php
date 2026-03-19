<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Support\TableExistenceCache;
class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'status',
        'checked_out_at',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public static function forUserActive(int $userId): ?self
    {
        if (!TableExistenceCache::has('carts')) {
            return null;
        }
        
        try {
            return static::firstOrCreate(
                ['user_id' => $userId, 'status' => 'active'],
                ['currency' => 'eur']
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}
