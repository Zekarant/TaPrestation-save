<?php

namespace App\Models;

use App\Models\Concerns\HandlesLegacyEncryptedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentConsent extends Model
{
    use HandlesLegacyEncryptedAttributes;

    protected $fillable = [
        'user_id',
        'consentable_type',
        'consentable_id',
        'consent_type',
        'version',
        'terms_hash',
        'ip_address',
        'user_agent',
        'metadata',
        'consented_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'consented_at' => 'datetime',
    ];

    protected $hidden = [
        'ip_address',
        'user_agent',
        'metadata',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getIpAddressAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setIpAddressAttribute($value): void
    {
        $this->attributes['ip_address'] = $this->encryptNullableString($value);
    }

    public function getUserAgentAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setUserAgentAttribute($value): void
    {
        $this->attributes['user_agent'] = $this->encryptNullableString($value);
    }
}
