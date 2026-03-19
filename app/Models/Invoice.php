<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'type',
        'user_id',
        'prestataire_id',
        'invoiceable_type',
        'invoiceable_id',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_postal_code',
        'billing_country',
        'billing_company',
        'billing_siret',
        'billing_vat_number',
        'seller_name',
        'seller_address',
        'seller_siret',
        'seller_vat_number',
        'currency',
        'payment_method',
        'description',
        'notes',
        'terms',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'line_items' => 'array',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    protected $hidden = [
        'payment_reference',
        'pdf_path',
    ];

    public static function systemCreate(array $attributes): self
    {
        $invoice = new static();
        $invoice->forceFill($attributes);
        $invoice->save();

        return $invoice;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->type);
            }
        });
    }

    /**
     * Générer un numéro de facture unique
     */
    public static function generateInvoiceNumber(string $type = 'client'): string
    {
        switch ($type) {
            case 'client':
                $prefix = 'FAC';
                break;
            case 'prestataire':
                $prefix = 'FP';
                break;
            case 'platform':
                $prefix = 'PLAT';
                break;
            default:
                $prefix = 'FAC';
                break;
        }

        return sprintf(
            '%s-%s-%s',
            $prefix,
            now()->format('YmdHisv'),
            Str::upper(Str::random(6))
        );
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeForClient($query, $userId)
    {
        return $query->where('user_id', $userId)->where('type', 'client');
    }

    public function scopeForPrestataire($query, $prestataireId)
    {
        return $query->where('prestataire_id', $prestataireId)->where('type', 'prestataire');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeIssued($query)
    {
        return $query->whereIn('status', ['issued', 'paid']);
    }

    /**
     * Helpers
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'issued' 
            && $this->due_at 
            && $this->due_at->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        switch ($this->status) {
            case 'draft':
                return 'Brouillon';
            case 'issued':
                return 'Émise';
            case 'paid':
                return 'Payée';
            case 'cancelled':
                return 'Annulée';
            case 'refunded':
                return 'Remboursée';
            default:
                return ucfirst((string) $this->status);
        }
    }

    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case 'draft':
                return 'gray';
            case 'issued':
                return 'blue';
            case 'paid':
                return 'green';
            case 'cancelled':
                return 'red';
            case 'refunded':
                return 'orange';
            default:
                return 'gray';
        }
    }

    public function getTypeLabelAttribute(): string
    {
        switch ($this->type) {
            case 'client':
                return 'Facture Client';
            case 'prestataire':
                return 'Relevé Prestataire';
            case 'platform':
                return 'Commission Plateforme';
            default:
                return 'Facture';
        }
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2, ',', ' ') . ' ' . $this->currency;
    }

    public function getPdfUrl(): ?string
    {
        if ($this->pdf_path && Storage::disk('public')->exists($this->pdf_path)) {
            return Storage::disk('public')->url($this->pdf_path);
        }
        return null;
    }

    /**
     * Calculer les montants
     */
    public function calculateAmounts(): void
    {
        // Calcul TVA
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        
        // Total TTC
        $this->total = $this->subtotal + $this->tax_amount - $this->discount_amount;
        
        // Commission plateforme (calculée sur HT, audit 2.16)
        $this->commission_amount = $this->subtotal * ($this->commission_rate / 100);
        
        // Montant net prestataire
        $this->net_amount = $this->total - $this->commission_amount;
    }

    /**
     * Marquer comme payée
     */
    public function markAsPaid(string $paymentMethod = null, string $paymentReference = null): void
    {
        $this->status = 'paid';
        $this->paid_at = now();
        
        if ($paymentMethod) {
            $this->payment_method = $paymentMethod;
        }
        if ($paymentReference) {
            $this->payment_reference = $paymentReference;
        }
        
        $this->save();
    }

    /**
     * Émettre la facture
     */
    public function issue(): void
    {
        $this->status = 'issued';
        $this->issued_at = now();
        $this->due_at = now()->addDays(30);
        $this->save();
    }
}
