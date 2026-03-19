<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressBook extends Model
{
    protected $table = 'address_books';
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'street',
        'city',
        'postal_code',
        'country',
        'phone',
        'latitude',
        'longitude',
        'is_default',
        'tags',
        'address_type',
        'notes',
    ];

    protected $casts = [
        'tags' => 'json',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        return "{$this->street}, {$this->postal_code} {$this->city}, {$this->country}";
    }

    public function getCoordinatesAttribute(): ?array
    {
        return $this->latitude && $this->longitude 
            ? ['lat' => $this->latitude, 'lng' => $this->longitude]
            : null;
    }
}
