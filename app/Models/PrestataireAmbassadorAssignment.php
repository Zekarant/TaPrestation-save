<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestataireAmbassadorAssignment extends Model
{
    protected $fillable = [
        'ambassador_id',
        'prestataire_id',
        'source',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function ambassador()
    {
        return $this->belongsTo(Ambassador::class);
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }
}
