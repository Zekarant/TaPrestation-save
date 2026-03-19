<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advertisement extends Model
{
    protected $fillable = [
        'advertiser_id',
        'title',
        'description',
        'image_url',
        'target_url',
        'category',
        'status',
        'approved_at',
        'starts_at',
        'expires_at',
        'daily_budget',
        'total_budget',
        'spent_amount',
        'impression_count',
        'click_count',
        'conversion_count',
        'targeting',
        'metadata',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'targeting' => 'json',
        'metadata' => 'json',
    ];

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'approved' 
            && (!$this->starts_at || $this->starts_at->isPast())
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function hasQuotaRemaining(): bool
    {
        return ($this->total_budget ?? 0) > ($this->spent_amount ?? 0);
    }

    public function getClickThroughRateAttribute(): float
    {
        return $this->impression_count > 0 ? ($this->click_count / $this->impression_count) * 100 : 0;
    }

    public function recordImpression()
    {
        $this->increment('impression_count');
    }

    public function recordClick()
    {
        $this->increment('click_count');
    }
}
