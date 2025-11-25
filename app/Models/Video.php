<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'prestataire_id',
        'title',
        'description',
        'video_path',
        'is_public',
        'duration',
        'status',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
    ];

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(VideoLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(VideoComment::class);
    }

    public function isLikedBy($user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the full URL for the video
     */
    public function getVideoUrlAttribute()
    {
        try {
            // Handle different storage paths
            if (str_contains($this->video_path, 'recorded_videos/')) {
                return Storage::disk('public')->url($this->video_path);
            }
            
            // Default path
            return Storage::disk('public')->url($this->video_path);
        } catch (\Exception $e) {
            // Fallback to direct URL construction
            return url('storage/' . $this->video_path);
        }
    }

    public function getMimeType()
    {
        $extension = pathinfo($this->video_path, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'mp4':
                return 'video/mp4';
            case 'webm':
                return 'video/webm';
            case 'ogv':
                return 'video/ogg';
            case 'mov':
                return 'video/quicktime';
            case 'avi':
                return 'video/x-msvideo';
            case 'wmv':
                return 'video/x-ms-wmv';
            case 'mpeg':
            case 'mpg':
                return 'video/mpeg';
            case '3gp':
                return 'video/3gpp';
            case '3g2':
                return 'video/3gpp2';
            case 'flv':
                return 'video/x-flv';
            case 'm4v':
                return 'video/x-m4v';
            default:
                return 'video/mp4'; // Fallback
        }
    }
}