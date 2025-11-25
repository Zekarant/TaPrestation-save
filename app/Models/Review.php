<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'prestataire_id',
        'service_id',
        'booking_id',
        'rating',
        'punctuality_rating',
        'quality_rating',
        'value_rating',
        'communication_rating',
        'comment',
        'photos',
        'verified',
        'status',
        'moderated_by',
        'moderated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'photos' => 'array',
        'verified' => 'boolean',
        'moderated_at' => 'datetime',
        'rating' => 'integer',
        'punctuality_rating' => 'integer',
        'quality_rating' => 'integer',
        'value_rating' => 'integer',
        'communication_rating' => 'integer',
    ];

    /**
     * Get the client that wrote the review.
     * 
     * Note: client_id dans la table reviews fait référence à users.id
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the user that wrote the review (direct relationship).
     */
    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the prestataire being reviewed.
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Get the service being reviewed.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the booking associated with this review.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the moderator who moderated this review.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get the client name safely, handling null client.
     *
     * @return string
     */
    public function getClientNameAttribute(): string
    {
        // Since client() now returns a User model, we can access the name directly
        return $this->client ? $this->client->name : 'Client anonyme';
    }

    /**
     * Scope a query to only include rejected reviews.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include verified reviews.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Get the number of photos in this review.
     */
    public function getPhotosCountAttribute()
    {
        return is_array($this->photos) ? count($this->photos) : 0;
    }

    /**
     * Check if the review is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the review is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the client email safely, handling null client.
     *
     * @return string|null
     */
    public function getClientEmailAttribute(): ?string
    {
        // Since client() now returns a User model, we can access the email directly
        return $this->client ? $this->client->email : null;
    }

    /**
     * Get the prestataire name safely, handling null prestataire or user.
     *
     * @return string
     */
    public function getPrestataireNameAttribute(): string
    {
        return $this->prestataire && $this->prestataire->user ? $this->prestataire->user->name : 'Prestataire anonyme';
    }

    /**
     * Check if the review is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}