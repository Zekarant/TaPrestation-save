<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ambassador extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'referral_code',
        'status',
        'phone',
        'city',
        'notes',
        'stripe_account_id',
        'stripe_account_status',
    ];

    protected function casts(): array
    {
        return [
            'total_commission_earned' => 'decimal:2',
            'total_commission_paid' => 'decimal:2',
        ];
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = 'AMB-' . Str::upper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(PrestataireAmbassadorAssignment::class);
    }

    public function prestataires()
    {
        return $this->belongsToMany(Prestataire::class, 'prestataire_ambassador_assignments')
            ->withPivot('source', 'assigned_at')
            ->withTimestamps();
    }

    public function commissions()
    {
        return $this->hasMany(AmbassadorCommission::class);
    }

    public function payoutBatches()
    {
        return $this->hasMany(AmbassadorPayoutBatch::class);
    }

    public function referralVisits()
    {
        return $this->hasMany(AmbassadorReferralVisit::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(AmbassadorActivityLog::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ── Accessors ──

    public function getUnpaidCommissionAttribute(): float
    {
        return round($this->total_commission_earned - $this->total_commission_paid, 2);
    }

    public function getReferralUrlAttribute(): string
    {
        return url('/ref/' . $this->referral_code);
    }
}
