<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmbassadorReferralVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ambassador_id',
        'ip_address',
        'user_agent',
        'referer_url',
        'converted',
        'converted_prestataire_id',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'converted' => 'boolean',
            'visited_at' => 'datetime',
        ];
    }

    public function ambassador()
    {
        return $this->belongsTo(Ambassador::class);
    }

    public function convertedPrestataire()
    {
        return $this->belongsTo(Prestataire::class, 'converted_prestataire_id');
    }
}
