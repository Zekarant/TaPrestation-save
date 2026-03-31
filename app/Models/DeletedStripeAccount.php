<?php

namespace App\Models;

use App\Models\Concerns\HandlesLegacyEncryptedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;

class DeletedStripeAccount extends Model
{
    use HandlesLegacyEncryptedAttributes;

    protected $fillable = [
        'email',
        'email_hash',
        'stripe_customer_id',
        'stripe_account_id',
        'original_user_id',
        'user_name',
        'user_role',
        'metadata',
        'deleted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'email',
        'email_hash',
        'stripe_customer_id',
        'stripe_account_id',
        'user_name',
        'metadata',
    ];

    /**
     * Vérifie si la table existe en base de données
     */
    public static function tableExists(): bool
    {
        static $exists = null;
        
        if ($exists === null) {
            try {
                $exists = TableExistenceCache::has('deleted_stripe_accounts');
            } catch (\Throwable $e) {
                $exists = false;
            }
        }
        
        return $exists;
    }

    /**
     * Trouver un compte Stripe archivé par email
     */
    public static function findByEmail(string $email): ?self
    {
        $normalizedEmail = strtolower(trim($email));
        $emailHash = $normalizedEmail !== '' ? hash('sha256', $normalizedEmail) : null;

        // Si la table n'existe pas, retourner null silencieusement
        if (!static::tableExists()) {
            return null;
        }

        try {
            $query = static::query()->orderBy('deleted_at', 'desc');

            if ($emailHash !== null && Schema::hasColumn('deleted_stripe_accounts', 'email_hash')) {
                $query->where('email_hash', $emailHash);
            } else {
                $query->where('email', $normalizedEmail);
            }

            return $query->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Archiver les infos Stripe d'un utilisateur avant suppression
     */
    public static function archiveFromUser($user): ?self
    {
        // Si la table n'existe pas, ne rien faire
        if (!static::tableExists()) {
            return null;
        }

        try {
            $stripeCustomerId = $user->stripe_customer_id ?? null;
            $stripeAccountId = null;
            $role = 'client';

            // Si c'est un prestataire, récupérer aussi le compte Connect
            if ($user->prestataire) {
                $stripeAccountId = $user->prestataire->stripe_account_id ?? null;
                $role = 'prestataire';
            }

            // Ne rien archiver si pas de Stripe ID
            if (!$stripeCustomerId && !$stripeAccountId) {
                return null;
            }

            return static::create([
                'email' => $normalizedEmail = strtolower(trim($user->email)),
                'email_hash' => hash('sha256', $normalizedEmail),
                'stripe_customer_id' => $stripeCustomerId,
                'stripe_account_id' => $stripeAccountId,
                'original_user_id' => $user->id,
                'user_name' => null,
                'user_role' => $role,
                'metadata' => [
                    'version' => 2,
                    'created_at' => $user->created_at?->toISOString(),
                ],
                'deleted_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Log l'erreur mais ne pas bloquer la suppression
            \Illuminate\Support\Facades\Log::warning('Failed to archive Stripe account', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function getEmailAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setEmailAttribute($value): void
    {
        $normalized = strtolower(trim((string) $value));
        $this->attributes['email'] = $this->encryptNullableString($normalized);
        $this->attributes['email_hash'] = $normalized !== '' ? hash('sha256', $normalized) : null;
    }

    public function getStripeCustomerIdAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setStripeCustomerIdAttribute($value): void
    {
        $this->attributes['stripe_customer_id'] = $this->encryptNullableString($value);
    }

    public function getStripeAccountIdAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setStripeAccountIdAttribute($value): void
    {
        $this->attributes['stripe_account_id'] = $this->encryptNullableString($value);
    }

    public function getUserNameAttribute($value): ?string
    {
        return $this->decryptNullableString($value);
    }

    public function setUserNameAttribute($value): void
    {
        $this->attributes['user_name'] = $this->encryptNullableString($value);
    }

    public function getMetadataAttribute($value): ?array
    {
        return $this->decryptNullableArray($value);
    }

    public function setMetadataAttribute($value): void
    {
        $this->attributes['metadata'] = $this->encryptNullableArray($value);
    }
}
