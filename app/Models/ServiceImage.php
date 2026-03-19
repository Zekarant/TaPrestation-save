<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'image_path',
        'original_name',
        'file_size',
        'mime_type',
        'order',
    ];

    /**
     * Get the service that owns the image.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the full URL for the image.
     */
    public function getUrlAttribute()
    {
        return storage_asset_url($this->image_path);
    }

    public function getImagePathAttribute($value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        return function_exists('normalize_storage_asset_path')
            ? (normalize_storage_asset_path($value) ?? $value)
            : $value;
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete the image file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($image) {
            // Check if image_path exists and is not null before attempting to delete
            if ($image->image_path && Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }
        });
    }
}
