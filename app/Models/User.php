<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

use App\Support\TableExistenceCache;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, SoftDeletes;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPasswordTrait;
    /**
     * Boot the model.
     * Génère automatiquement un UUID lors de la création d'un utilisateur.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_online',
        // SÉCURITÉ H5: 'commission_client_disabled' retiré de $fillable
        // Ce flag ne doit être modifiable que par l'admin (AdminSettingsController)
        // 'commission_client_disabled',
        'last_seen_at',
        'google_id',
        'apple_id',
        'avatar',
    ];

    // $casts property removed (audit 3.2): Laravel 12 uses the casts() method below.

    /**
     * Get the client associated with the user.
     */
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Get the prestataire associated with the user.
     */
    public function prestataire()
    {
        return $this->hasOne(Prestataire::class);
    }

    /**
     * Check if the user is a client.
     */
    public function isClient()
    {
        return $this->role === 'client';
    }

    /**
     * Check if the user is a prestataire.
     */
    public function isPrestataire()
    {
        return $this->role === 'prestataire';
    }

    /**
     * Check if the user signed up via Google or Apple (no usable password set).
     * Note: With Laravel's 'hashed' cast, empty strings get hashed too.
     * We check if the password is verifiable against an empty string.
     */
    public function isSocialAccount()
    {
        // L'utilisateur a un compte social (Google ou Apple)
        if (!$this->google_id && !$this->apple_id) {
            return false;
        }

        return (bool) ($this->password_setup_required ?? false);
    }

    /**
     * Check if the user has a usable password set (regular account or social account with added password).
     */
    public function hasPassword()
    {
        if ((bool) ($this->password_setup_required ?? false)) {
            return false;
        }

        if (is_null($this->password) || $this->password === '') {
            return false;
        }

        return true;
    }

    /**
     * Check if the user has a specific role.
     * Vérifie d'abord la colonne locale `role`, puis délègue à Spatie (audit 3.1).
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        if (is_string($roles) && $this->role === $roles) {
            return true;
        }

        return parent::hasRole($roles, $guard);
    }

    /**
     * Get user's subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get user's payment transactions
     */
    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get the latest subscription
     */
    public function latestSubscription()
    {
        try {
            return $this->subscriptions()->latest()->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        try {
            $subscription = $this->latestSubscription();

            if (!$subscription) {
                return false;
            }

            return $subscription->status === 'active'
                && ($subscription->current_period_end === null || $subscription->current_period_end->isFuture());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get user's inventory items
     */
    public function inventory()
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Get user's addresses
     */
    public function addresses()
    {
        return $this->hasMany(AddressBook::class);
    }

    /**
     * Get user's notification settings
     */
    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }





    /**
     * Relation avec les messages envoyés par l'utilisateur.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Relation avec les messages reçus par l'utilisateur.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Relation avec tous les messages de l'utilisateur (envoyés et reçus).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allMessages()
    {
        return Message::where('sender_id', $this->id)
            ->orWhere('receiver_id', $this->id)
            ->get();
    }

    /**
     * Compte les messages non lus de l'utilisateur.
     *
     * @return int
     */
    public function unreadMessagesCount()
    {
        return $this->receivedMessages()->whereNull('read_at')->count();
    }

    /**
     * Get the profile photo URL attribute.
     *
     * @return string|null
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->prestataire) {
            if ($this->prestataire->photo) {
                if (str_starts_with($this->prestataire->photo, 'http')) {
                    return $this->prestataire->photo;
                }
                return storage_asset_url($this->prestataire->photo);
            } elseif ($this->prestataire->profile_image) {
                if (str_starts_with($this->prestataire->profile_image, 'http')) {
                    return $this->prestataire->profile_image;
                }
                return storage_asset_url($this->prestataire->profile_image);
            }
        } elseif ($this->client && $this->client->photo) {
            if (str_starts_with($this->client->photo, 'http')) {
                return $this->client->photo;
            }
            return storage_asset_url($this->client->photo);
        }

        // Fallback: check avatar field (e.g. Google OAuth photo)
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            return storage_asset_url($this->avatar);
        }

        return null; // Or a default image path
    }

    /**
     * Get the profile photo URL for messaging.
     *
     * @return string
     */
    public function getMessagingPhotoUrl()
    {
        $photoUrl = $this->profile_photo_url;

        if ($photoUrl) {
            return $photoUrl;
        }

        // Return a default avatar if no photo is available
        return asset('images/default-avatar.svg');
    }

    /**
     * Get the prestataires followed by this client (via client profile).
     */
    public function followedPrestataires()
    {
        if ($this->client) {
            return $this->client->followedPrestataires();
        }
        return collect();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'apple_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'blocked_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'is_online' => 'boolean',
            'commission_client_disabled' => 'boolean',
            'password_setup_required' => 'boolean',
        ];
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    /**
     * Vérifier si l'utilisateur est considéré comme en ligne.
     * Un utilisateur est en ligne s'il a été actif dans les 5 dernières minutes.
     */
    public function getIsOnlineAttribute(): bool
    {
        if (!$this->attributes['is_online']) {
            return false;
        }

        if (!$this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at->diffInMinutes(now()) <= 5;
    }

    /**
     * Marquer l'utilisateur comme en ligne.
     */
    public function markAsOnline(): void
    {
        $this->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Marquer l'utilisateur comme hors ligne.
     */
    public function markAsOffline(): void
    {
        $this->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Obtenir le statut en ligne formaté.
     */
    public function getOnlineStatusAttribute(): string
    {
        if ($this->is_online) {
            return 'En ligne';
        }

        if ($this->last_seen_at) {
            $diffInMinutes = $this->last_seen_at->diffInMinutes(now());

            if ($diffInMinutes < 60) {
                return "Vu il y a " . round($diffInMinutes) . " min";
            } elseif ($diffInMinutes < 1440) { // 24 heures
                $hours = floor($diffInMinutes / 60);
                return "Vu il y a {$hours}h";
            } else {
                return 'Vu ' . $this->last_seen_at->format('d/m/Y');
            }
        }

        return 'Hors ligne';
    }

    /**
     * Avis reçus en tant que client (évaluations par les prestataires)
     * Note: client_id dans client_reviews référence clients.id, pas users.id
     */
    public function clientReviews()
    {
        // Si l'utilisateur a un profil client, on récupère les avis via client.id
        if ($this->client) {
            return ClientReview::where('client_id', $this->client->id);
        }
        // Fallback si pas de client associé
        return $this->hasMany(ClientReview::class, 'client_id')->whereRaw('1 = 0');
    }

    /**
     * Avis donnés en tant que prestataire (évaluations des clients)
     * Note: prestataire_id dans client_reviews référence prestataires.id, pas users.id
     */
    public function givenClientReviews()
    {
        // Si l'utilisateur a un profil prestataire, on récupère les avis via prestataire.id
        if ($this->prestataire) {
            return ClientReview::where('prestataire_id', $this->prestataire->id);
        }
        // Fallback si pas de prestataire associé
        return $this->hasMany(ClientReview::class, 'prestataire_id')->whereRaw('1 = 0');
    }

    /**
     * Note moyenne en tant que client
     */
    public function getClientRatingAttribute()
    {
        if (!TableExistenceCache::has('client_reviews')) {
            return null;
        }
        try {
            $avg = $this->clientReviews()->avg('rating');
            return $avg ? round($avg, 1) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Nombre d'avis reçus en tant que client
     */
    public function getClientReviewsCountAttribute()
    {
        if (!TableExistenceCache::has('client_reviews')) {
            return 0;
        }
        try {
            return $this->clientReviews()->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Pourcentage de prestataires qui travailleraient à nouveau avec ce client
     */
    public function getWouldWorkAgainPercentageAttribute()
    {
        if (!TableExistenceCache::has('client_reviews')) {
            return null;
        }
        try {
            $total = $this->clientReviews()->count();
            if ($total === 0)
                return null;

            $positive = $this->clientReviews()->where('would_work_again', true)->count();
            return round(($positive / $total) * 100);
        } catch (\Exception $e) {
            return null;
        }
    }
}
