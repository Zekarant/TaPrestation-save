<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SatisfactionCertificate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prestataire_id',
        'year',
        'satisfaction_rate',
        'total_reviews',
        'certificate_number',
        'issued_at',
        'expires_at',
        'file_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'satisfaction_rate' => 'decimal:2',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the prestataire that owns the certificate.
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Check if the certificate is still valid.
     */
    public function isValid()
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Get the certificate title.
     */
    public function getTitleAttribute()
    {
        return "{$this->satisfaction_rate}% de clients satisfaits en {$this->year}";
    }

    /**
     * Generate a unique certificate number.
     */
    public static function generateCertificateNumber($prestataireId, $year)
    {
        return 'CERT-' . str_pad($prestataireId, 6, '0', STR_PAD_LEFT) . '-' . $year . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Scope for valid certificates only.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for certificates of a specific year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }
}