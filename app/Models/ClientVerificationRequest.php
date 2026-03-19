<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientVerificationRequest extends Model
{
    protected $fillable = ['prestataire_id', 'status', 'admin_comment', 'document'];

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }
}
