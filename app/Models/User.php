<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPasswordTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_blocked',
        'blocked_at',
        'is_online',
        'last_seen_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'blocked_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'password' => 'hashed',
        'is_online' => 'boolean',
    ];

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
     * Check if the user has a specific role.
     */
    public function hasRole($role)
    {
        return $this->role === $role;
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
                return asset('storage/' . $this->prestataire->photo);
            } elseif ($this->prestataire->profile_image) {
                return asset('storage/' . $this->prestataire->profile_image);
            }
        } elseif ($this->client && $this->client->photo) {
            return asset('storage/' . $this->client->photo);
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
            'password' => 'hashed',
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
}