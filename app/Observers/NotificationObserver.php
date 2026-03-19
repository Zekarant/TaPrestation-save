<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\OneSignalService;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    protected OneSignalService $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * Handle the Notification "created" event.
     * DÉSACTIVÉ - L'envoi push est maintenant géré par SendOneSignalPush listener
     * pour éviter les notifications en double/triple
     */
    public function created(Notification $notification): void
    {
        // DÉSACTIVÉ: Le listener SendOneSignalPush gère déjà l'envoi via OneSignal
        // après l'événement NotificationSent. Garder ce code ici causait des doublons.
        return;
        
        // Ne pas envoyer si OneSignal n'est pas configuré
        if (!$this->oneSignal->isConfigured()) {
            return;
        }

        try {
            $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
            
            // Extraire les infos pour la notification push
            $title = $this->extractTitle($notification->type, $data);
            $body = $this->extractBody($data);
            
            // Construire l'URL en fonction du rôle de l'utilisateur destinataire
            $url = $this->buildNotificationUrl($notification, $data);
            
            // Envoyer via OneSignal
            $this->oneSignal->sendToUser(
                $notification->notifiable_id,
                $title,
                $body,
                [
                    'type' => $data['type'] ?? 'notification',
                    'notification_id' => $notification->id,
                    'url' => $url,
                ]
            );
            
            // Marquer comme pushée
            $notification->update(['push_sent_at' => now()]);
            
        } catch (\Exception $e) {
            Log::error('NotificationObserver push failed: ' . $e->getMessage());
        }
    }

    /**
     * Construire l'URL de notification adaptée au rôle de l'utilisateur
     */
    private function buildNotificationUrl(Notification $notification, array $data): string
    {
        // URL par défaut vers les notifications générales (accessible à tous)
        $defaultUrl = '/notifications';
        
        // Récupérer l'utilisateur destinataire
        $user = \App\Models\User::find($notification->notifiable_id);
        if (!$user) {
            return $defaultUrl;
        }
        
        // Déterminer le préfixe selon le rôle
        $isPrestataire = $user->hasRole('prestataire');
        $isClient = $user->hasRole('client');
        
        // Si une URL spécifique est fournie, l'adapter au rôle
        $originalUrl = $data['url'] ?? $data['action_url'] ?? null;
        
        if ($originalUrl) {
            // Convertir les URLs client en URLs prestataire si nécessaire
            if ($isPrestataire && str_contains($originalUrl, '/client/')) {
                // Mapping des URLs client vers prestataire
                $mappings = [
                    '/client/bookings/' => '/prestataire/bookings/',
                    '/client/bookings' => '/prestataire/bookings',
                    '/client/orders/' => '/prestataire/food/orders/',
                    '/client/orders' => '/prestataire/food/orders',
                    '/client/messages/' => '/messaging/',
                    '/client/messages' => '/messaging',
                ];
                
                foreach ($mappings as $clientPath => $prestatairePath) {
                    if (str_contains($originalUrl, $clientPath)) {
                        return str_replace($clientPath, $prestatairePath, $originalUrl);
                    }
                }
                
                // Si pas de mapping, rediriger vers notifications
                return $defaultUrl;
            }
            
            // Convertir les URLs prestataire en URLs client si nécessaire
            if ($isClient && str_contains($originalUrl, '/prestataire/')) {
                $mappings = [
                    '/prestataire/bookings/' => '/client/bookings/',
                    '/prestataire/bookings' => '/client/bookings',
                    '/prestataire/food/orders/' => '/client/orders/',
                    '/prestataire/food/orders' => '/client/orders',
                ];
                
                foreach ($mappings as $prestatairePath => $clientPath) {
                    if (str_contains($originalUrl, $prestatairePath)) {
                        return str_replace($prestatairePath, $clientPath, $originalUrl);
                    }
                }
                
                return $defaultUrl;
            }
            
            return $originalUrl;
        }
        
        return $defaultUrl;
    }

    /**
     * Extraire le titre de la notification
     */
    private function extractTitle(string $type, array $data): string
    {
        $typeToTitle = [
            'App\\Notifications\\NewFoodOrder' => '🍽️ Nouvelle commande',
            'App\\Notifications\\FoodOrderAccepted' => '✅ Commande acceptée',
            'App\\Notifications\\FoodOrderRejected' => '❌ Commande refusée',
            'App\\Notifications\\FoodOrderReady' => '🔔 Commande prête',
            'App\\Notifications\\FoodOrderCompleted' => '✅ Commande livrée',
            'App\\Notifications\\FoodOrderConfirmedByClient' => '✅ Commande confirmée',
            'App\\Notifications\\NewBookingNotification' => '📅 Nouvelle réservation',
            'App\\Notifications\\BookingConfirmedNotification' => '✅ Réservation confirmée',
            'App\\Notifications\\BookingCancelledNotification' => '❌ Réservation annulée',
            'App\\Notifications\\NewMessageNotification' => '💬 Nouveau message',
            'App\\Notifications\\NewReviewNotification' => '⭐ Nouvel avis',
            'App\\Notifications\\NewTenderResponseNotification' => '📋 Nouvelle réponse',
            'App\\Notifications\\NewTenderMatchNotification' => '🎯 Nouvelle opportunité',
            'App\\Notifications\\NewEquipmentRentalRequestNotification' => '🔧 Demande de location',
            'App\\Notifications\\PaymentReceivedNotification' => '💰 Paiement reçu',
        ];

        return $typeToTitle[$type] ?? $data['title'] ?? 'TaPrestation';
    }

    /**
     * Extraire le corps de la notification
     */
    private function extractBody(array $data): string
    {
        if (!empty($data['message'])) return $data['message'];
        if (!empty($data['body'])) return $data['body'];
        if (!empty($data['content'])) return $data['content'];
        
        if (!empty($data['client_name']) && !empty($data['order_number'])) {
            return 'De ' . $data['client_name'] . ' - Commande #' . $data['order_number'];
        }
        if (!empty($data['sender_name'])) {
            return 'Message de ' . $data['sender_name'];
        }
        
        return 'Vous avez une nouvelle notification';
    }
}
