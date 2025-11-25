<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Obtenir l'utilisateur associé à la notification.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir l'élément notifiable associé à la notification.
     *
     * @return MorphTo
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Marquer la notification comme lue.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if (is_null($this->read_at)) {
            $this->read_at = now();
            return $this->save();
        }

        return false;
    }

    /**
     * Déterminer si la notification a été lue.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Scope pour les notifications non lues.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    
    /**
     * Get the notification title.
     *
     * @return string
     */
    public function getTitleAttribute(): string
    {
        $data = $this->getDecodedData();
        return $data['title'] ?? $this->getDefaultTitle();
    }

    /**
     * Get the notification message.
     *
     * @return string
     */
    public function getMessageAttribute(): string
    {
        $data = $this->getDecodedData();
        return $data['message'] ?? $this->getDefaultMessage();
    }

    /**
     * Get the notification action URL.
     *
     * @return string|null
     */
    public function getActionUrlAttribute(): ?string
    {
        $data = $this->getDecodedData();
        return $data['url'] ?? $data['action_url'] ?? null;
    }

    /**
     * Get the notification action text.
     *
     * @return string|null
     */
    public function getActionTextAttribute(): ?string
    {
        $data = $this->getDecodedData();
        return $data['action_text'] ?? null;
    }
    
    /**
     * Get notification data in a consistent format.
     *
     * @return array
     */
    public function getDecodedData(): array
    {
        if (is_string($this->data)) {
            return json_decode($this->data, true) ?? [];
        }
        
        return $this->data ?? [];
    }
    
    /**
     * Get default title based on notification type.
     *
     * @return string
     */
    private function getDefaultTitle(): string
    {
        $typeMap = [
            'App\\Notifications\\NewBookingNotification' => 'Nouvelle réservation',
            'App\\Notifications\\BookingConfirmedNotification' => 'Réservation confirmée',
            'App\\Notifications\\BookingRejectedNotification' => 'Réservation refusée',
            'App\\Notifications\\NewMessageNotification' => 'Nouveau message',
            'App\\Notifications\\NewReviewNotification' => 'Nouvel avis',
            // Add other types as needed
        ];
        
        return $typeMap[$this->type] ?? 'Notification';
    }
    
    /**
     * Get default message based on notification type.
     *
     * @return string
     */
    private function getDefaultMessage(): string
    {
        return 'Vous avez reçu une notification.';
    }
}
