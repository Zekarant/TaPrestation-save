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

    protected $casts = [
        'is_public' => 'boolean',
        'duration' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
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
     * Utilise la route stream pour iOS Safari compatibility
     */
    public function getVideoUrlAttribute()
    {
        if (empty($this->video_path)) {
            return null;
        }

        // Utiliser la route stream pour une meilleure compatibilité iOS
        // La route /videos/{id}/stream gère correctement les Range Requests
        return route('videos.stream', $this->id);
    }

    /**
     * Get the direct storage URL (fallback)
     */
    public function getDirectUrlAttribute()
    {
        if (empty($this->video_path)) {
            return null;
        }
        
        // Utiliser la route stream qui gère bien iOS
        return route('videos.stream', $this->id);
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