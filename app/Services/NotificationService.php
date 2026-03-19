<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $twilio;

    public function __construct()
    {
        if (config('notifications.twilio.enabled')) {
            try {
                $this->twilio = new TwilioClient(
                    config('notifications.twilio.sid'),
                    config('notifications.twilio.token')
                );
            } catch (\Exception $e) {
                Log::error('Twilio init failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send SMS notification
     */
    public function sendSMS(string $phoneNumber, string $message): ?string
    {
        if (!config('notifications.twilio.enabled') || !$this->twilio) {
            return null;
        }

        try {
            $sms = $this->twilio->messages->create(
                $phoneNumber,
                [
                    'from' => config('notifications.twilio.from'),
                    'body' => $message,
                ]
            );

            return $sms->sid;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send Web Push notification to a user
     * Uses the Web Push API with subscriptions stored in user->push_subscriptions
     */
    public function sendWebPushToUser($user, string $title, string $body, array $data = []): bool
    {
        if (!$user || !$user->push_enabled) {
            return false;
        }

        $subscriptions = json_decode($user->push_subscriptions ?? '[]', true);
        
        if (empty($subscriptions)) {
            return false;
        }

        $successCount = 0;
        foreach ($subscriptions as $subscription) {
            if ($this->sendWebPush($subscription, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount > 0;
    }

    /**
     * Send Web Push notification using Web Push protocol
     */
    public function sendWebPush(array $subscription, string $title, string $body, array $data = []): bool
    {
        try {
            $endpoint = $subscription['endpoint'] ?? null;
            $p256dh = $subscription['keys']['p256dh'] ?? null;
            $auth = $subscription['keys']['auth'] ?? null;

            if (!$endpoint || !$p256dh || !$auth) {
                Log::warning('Invalid push subscription', ['subscription' => $subscription]);
                return false;
            }

            // Payload for the push notification
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/icons/icon-192x192.png',
                'badge' => '/icons/icon-72x72.png',
                'data' => $data,
                'timestamp' => time() * 1000,
            ]);

            // Use web-push library if available
            if (class_exists('\Minishlink\WebPush\WebPush')) {
                return $this->sendWithWebPushLibrary($subscription, $payload);
            }

            // Log the notification
            Log::info('Web Push notification (library not installed)', [
                'endpoint' => substr($endpoint, 0, 50) . '...',
                'title' => $title,
                'body' => $body,
            ]);
            
            return false;

        } catch (\Exception $e) {
            Log::error('Web Push failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send using minishlink/web-push library
     */
    protected function sendWithWebPushLibrary(array $subscription, string $payload): bool
    {
        try {
            $auth = [
                'VAPID' => [
                    'subject' => config('app.url'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);
            
            $sub = \Minishlink\WebPush\Subscription::create([
                'endpoint' => $subscription['endpoint'],
                'publicKey' => $subscription['keys']['p256dh'],
                'authToken' => $subscription['keys']['auth'],
            ]);

            $report = $webPush->sendOneNotification($sub, $payload);

            return $report->isSuccess();

        } catch (\Exception $e) {
            Log::error('WebPush library error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Legacy method - Send push notification via Firebase (if configured)
     */
    public function sendPushNotification(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        if (!config('notifications.fcm.enabled', false)) {
            return false;
        }

        try {
            $credentialsPath = config_path('firebase-credentials.json');
            if (!file_exists($credentialsPath)) {
                Log::warning('Firebase credentials not found');
                return false;
            }

            $client = new \Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/cloud-platform');

            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            $url = 'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send';

            $message = [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                ],
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode(['message' => $message]),
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                return true;
            }

            Log::error('FCM push failed', ['response' => $response, 'http_code' => $httpCode]);
            return false;

        } catch (\Exception $e) {
            Log::error('Push notification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send bulk SMS
     */
    public function sendBulkSMS(array $phoneNumbers, string $message): array
    {
        $results = [];
        foreach ($phoneNumbers as $phone) {
            $results[$phone] = $this->sendSMS($phone, $message);
        }
        return $results;
    }

    /**
     * Send bulk push notifications
     */
    public function sendBulkPush(array $deviceTokens, string $title, string $body, array $data = []): array
    {
        $results = [];
        foreach ($deviceTokens as $token) {
            $results[$token] = $this->sendPushNotification($token, $title, $body, $data);
        }
        return $results;
    }
}
