<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected string $appId;
    protected string $restApiKey;
    protected string $apiUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->appId = config('onesignal.app_id', '');
        $this->restApiKey = config('onesignal.rest_api_key', '');
        $this->apiUrl = config('onesignal.api_url', 'https://onesignal.com/api/v1/notifications');
        $this->enabled = config('onesignal.enabled', true);
    }

    /**
     * Envoyer une notification push à un utilisateur spécifique
     */
    public function sendToUser($userId, string $title, string $message, array $data = []): bool
    {
        // SDK v16+ utilise include_aliases au lieu de include_external_user_ids
        return $this->send([
            'include_aliases' => ['external_id' => [(string) $userId]],
            'target_channel' => 'push',
        ], $title, $message, $data);
    }

    /**
     * Envoyer une notification push à plusieurs utilisateurs
     */
    public function sendToUsers(array $userIds, string $title, string $message, array $data = []): bool
    {
        $userIds = array_map('strval', $userIds);
        
        // SDK v16+ utilise include_aliases
        return $this->send([
            'include_aliases' => ['external_id' => $userIds],
            'target_channel' => 'push',
        ], $title, $message, $data);
    }

    /**
     * Envoyer une notification push à tous les abonnés
     */
    public function sendToAll(string $title, string $message, array $data = []): bool
    {
        return $this->send([
            'included_segments' => ['All'],
        ], $title, $message, $data);
    }

    /**
     * Envoyer une notification push avec des filtres personnalisés
     */
    public function sendWithFilters(array $filters, string $title, string $message, array $data = []): bool
    {
        return $this->send([
            'filters' => $filters,
        ], $title, $message, $data);
    }

    /**
     * Méthode principale pour envoyer une notification
     */
    protected function send(array $targeting, string $title, string $message, array $data = []): bool
    {
        if (!$this->enabled || empty($this->appId) || empty($this->restApiKey)) {
            Log::warning('OneSignal not configured or disabled', [
                'enabled' => $this->enabled,
                'has_app_id' => !empty($this->appId),
                'has_api_key' => !empty($this->restApiKey),
            ]);
            return false;
        }

        try {
            $payload = array_merge([
                'app_id' => $this->appId,
                'headings' => ['en' => $title, 'fr' => $title],
                'contents' => ['en' => $message, 'fr' => $message],
                'data' => $data,
                // Chrome/Firefox Web Push
                'chrome_web_icon' => config('app.url') . '/icons/icon-192x192.png',
                'chrome_web_badge' => config('app.url') . '/icons/icon-72x72.png',
                'firefox_icon' => config('app.url') . '/icons/icon-192x192.png',
                // Safari macOS
                'safari_icon' => config('app.url') . '/icons/icon-256x256.png',
                // iOS PWA (iOS 16.4+) - Utilise les mêmes icônes Web
                'ios_badgeType' => 'Increase',
                'ios_badgeCount' => 1,
                // URL et options générales
                'url' => $data['url'] ?? config('app.url') . '/notifications',
                'web_push_topic' => $data['type'] ?? 'notification',
                'priority' => 10,
                'ttl' => 86400, // 24 heures
                // Paramètres iOS spécifiques
                'ios_sound' => 'default',
                'content_available' => true, // Important pour iOS background refresh
                'mutable_content' => true,   // Permet la modification du contenu côté iOS
            ], $targeting);

            Log::info('OneSignal sending notification', [
                'targeting' => $targeting,
                'title' => $title,
                'url' => $payload['url'],
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->restApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('OneSignal notification sent SUCCESS', [
                    'id' => $result['id'] ?? null,
                    'recipients' => $result['recipients'] ?? 0,
                    'external_id' => $result['external_id'] ?? null,
                ]);
                return true;
            }

            Log::error('OneSignal API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'targeting' => $targeting,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('OneSignal exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Programmer une notification push pour plus tard (rappel)
     * 
     * @param int|string $userId ID de l'utilisateur
     * @param string $title Titre de la notification
     * @param string $message Corps du message
     * @param \DateTime|\Carbon\Carbon $sendAt Date/heure d'envoi
     * @param array $data Données supplémentaires
     * @return string|false L'ID de la notification programmée ou false
     */
    public function scheduleNotification($userId, string $title, string $message, $sendAt, array $data = []): string|false
    {
        if (!$this->enabled || empty($this->appId) || empty($this->restApiKey)) {
            Log::warning('OneSignal not configured for scheduled notification');
            return false;
        }

        try {
            // Convertir en UTC ISO 8601 pour OneSignal
            $sendAfter = $sendAt instanceof \Carbon\Carbon 
                ? $sendAt->utc()->toIso8601String()
                : (new \DateTime($sendAt))->format('c');

            $payload = [
                'app_id' => $this->appId,
                'include_aliases' => ['external_id' => [(string) $userId]],
                'target_channel' => 'push',
                'headings' => ['en' => $title, 'fr' => $title],
                'contents' => ['en' => $message, 'fr' => $message],
                'data' => $data,
                'send_after' => $sendAfter,
                // Options
                'chrome_web_icon' => config('app.url') . '/icons/icon-192x192.png',
                'url' => $data['url'] ?? config('app.url') . '/notifications',
                'web_push_topic' => $data['type'] ?? 'scheduled_reminder',
                'priority' => 10,
                'ttl' => 3600, // 1 heure après l'heure prévue
            ];

            Log::info('OneSignal scheduling notification', [
                'user_id' => $userId,
                'title' => $title,
                'send_after' => $sendAfter,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->restApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                $notificationId = $result['id'] ?? null;
                
                Log::info('OneSignal scheduled notification SUCCESS', [
                    'notification_id' => $notificationId,
                    'send_after' => $sendAfter,
                ]);
                
                return $notificationId ?: 'scheduled';
            }

            Log::error('OneSignal scheduled notification error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('OneSignal schedule exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Annuler une notification programmée
     * 
     * @param string $notificationId ID de la notification à annuler
     * @return bool
     */
    public function cancelScheduledNotification(string $notificationId): bool
    {
        if (!$this->isConfigured() || empty($notificationId)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->restApiKey,
            ])->delete("https://onesignal.com/api/v1/notifications/{$notificationId}?app_id={$this->appId}");

            if ($response->successful()) {
                Log::info('OneSignal cancelled scheduled notification', ['id' => $notificationId]);
                return true;
            }

            Log::warning('OneSignal cancel notification failed', [
                'id' => $notificationId,
                'status' => $response->status(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('OneSignal cancel exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si OneSignal est configuré et actif
     */
    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->appId) && !empty($this->restApiKey);
    }
}
