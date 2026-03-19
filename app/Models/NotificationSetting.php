<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Support\TableExistenceCache;
class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'booking_notifications',
        'payment_notifications',
        'review_notifications',
        'message_notifications',
        'auction_notifications',
        'promotion_notifications',
        'food_order_notifications',
        'equipment_notifications',
        'newsletter_notifications',
        'quiet_hours_enabled',
        'quiet_start',
        'quiet_end',
        'notification_frequency',
        'push_device_token',
        'phone_number',
        'preferences',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'booking_notifications' => 'boolean',
        'payment_notifications' => 'boolean',
        'review_notifications' => 'boolean',
        'message_notifications' => 'boolean',
        'auction_notifications' => 'boolean',
        'promotion_notifications' => 'boolean',
        'food_order_notifications' => 'boolean',
        'equipment_notifications' => 'boolean',
        'newsletter_notifications' => 'boolean',
        'quiet_hours_enabled' => 'boolean',
        'preferences' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default settings object (when table doesn't exist)
     */
    public static function getDefaultSettings($userId = null)
    {
        return (object) [
            'id' => null,
            'user_id' => $userId,
            'email_notifications' => true,
            'sms_notifications' => true,
            'push_notifications' => true,
            'booking_notifications' => true,
            'payment_notifications' => true,
            'message_notifications' => true,
            'review_notifications' => true,
            'auction_notifications' => true,
            'promotion_notifications' => false,
            'food_order_notifications' => true,
            'equipment_notifications' => true,
            'newsletter_notifications' => false,
            'quiet_hours_enabled' => true,
            'quiet_start' => '22:00',
            'quiet_end' => '08:00',
            'notification_frequency' => 'immediate',
            'push_device_token' => null,
            'phone_number' => null,
            'preferences' => null,
        ];
    }

    public static function getOrCreate($userId)
    {
        // Check if table exists
        if (!TableExistenceCache::has('notification_settings')) {
            return self::getDefaultSettings($userId);
        }

        try {
            return self::firstOrCreate(
                ['user_id' => $userId],
                [
                    'email_notifications' => true,
                    'sms_notifications' => true,
                    'push_notifications' => true,
                    'booking_notifications' => true,
                    'payment_notifications' => true,
                    'message_notifications' => true,
                    'review_notifications' => true,
                    'auction_notifications' => true,
                    'promotion_notifications' => false,
                    'food_order_notifications' => true,
                    'equipment_notifications' => true,
                    'newsletter_notifications' => false,
                    'quiet_hours_enabled' => true,
                    'quiet_start' => '22:00',
                    'quiet_end' => '08:00',
                    'notification_frequency' => 'immediate',
                    'push_device_token' => null,
                    'phone_number' => null,
                    'preferences' => null,
                ]
            );
        } catch (\Exception $e) {
            return self::getDefaultSettings($userId);
        }
    }
}
