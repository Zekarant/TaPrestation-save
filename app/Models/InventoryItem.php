<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InventoryItem extends Model
{
    protected $table = 'inventory';
    protected $fillable = [
        'user_id',
        'urgent_sale_id',
        'name',
        'description',
        'sku',
        'quantity',
        'initial_quantity',
        'unit',
        'cost_per_unit',
        'selling_price',
        'category',
        'condition',
        'location',
        'status',
        'photos',
        'reorder_level',
        'supplier',
        'last_restocked_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'photos' => 'array',
        'last_restocked_at' => 'datetime',
    ];

    /**
     * Get photos as array (handles string/json edge cases)
     */
    public function getPhotosArray(): array
    {
        $photos = $this->photos;
        
        if (empty($photos)) {
            return [];
        }
        
        // Already an array
        if (is_array($photos)) {
            return array_filter($photos, fn($p) => !empty($p) && is_string($p));
        }
        
        // String - try to decode JSON
        if (is_string($photos)) {
            $decoded = json_decode($photos, true);
            if (is_array($decoded)) {
                return array_filter($decoded, fn($p) => !empty($p) && is_string($p));
            }
            // Single path string
            return [$photos];
        }
        
        return [];
    }

    /**
     * Get photo URLs - returns URLs directly without checking file existence
     * (Storage::exists is slow and may fail on shared hosting)
     */
    public function getPhotoUrls(): array
    {
        $urls = [];
        $photos = $this->getPhotosArray();
        
        foreach ($photos as $photo) {
            if (empty($photo) || !is_string($photo)) continue;
            
            // Clean path and generate URL directly
            $cleanPath = ltrim($photo, '/');
            $urls[] = '/storage/' . $cleanPath;
        }
        
        return $urls;
    }

    /**
     * Get first photo URL or placeholder
     */
    public function getFirstPhotoUrl(): string
    {
        $urls = $this->getPhotoUrls();
        // Use data URI for placeholder - no external file needed
        return $urls[0] ?? 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="#f3f4f6"/><text x="50" y="55" text-anchor="middle" font-family="Arial" font-size="10" fill="#9ca3af">No image</text></svg>');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class, 'user_id', 'user_id');
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= ($this->reorder_level ?? 0);
    }

    public function decreaseStock(int $amount)
    {
        $this->decrement('quantity', $amount);
    }

    public function increaseStock(int $amount)
    {
        $this->increment('quantity', $amount);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'inventory_item_id');
    }

    public function urgentSale()
    {
        return $this->belongsTo(UrgentSale::class, 'urgent_sale_id');
    }

    public function urgentSales()
    {
        return $this->hasMany(UrgentSale::class, 'inventory_item_id');
    }
}
