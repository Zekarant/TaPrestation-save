<?php

namespace App\Listeners;

use App\Services\OneSignalService;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendOneSignalPush
{
    protected OneSignalService $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * Handle the event.
     * Appelé après chaque notification envoyée via le canal 'database'
     */
    public function handle(NotificationSent $event): void
    {
        // Seulement pour les notifications database (évite les doublons mail+database)
        if ($event->channel !== 'database') {
            return;
        }

        // Vérifier si OneSignal est configuré
        if (!$this->oneSignal->isConfigured()) {
            return;
        }

        try {
            $notifiable = $event->notifiable;
            $notification = $event->notification;
            
            // Récupérer l'ID de l'utilisateur
            $userId = $notifiable->id ?? null;
            if (!$userId) {
                return;
            }
            
            // Créer une clé unique pour cette notification pour éviter les doublons
            $notificationClass = get_class($notification);
            $notificationId = $notification->id ?? spl_object_id($notification);
            $dedupeKey = "push_sent:{$userId}:{$notificationClass}:{$notificationId}";
            
            // Vérifier si on a déjà envoyé cette notification récemment (dans les 30 secondes)
            if (Cache::has($dedupeKey)) {
                Log::info('SendOneSignalPush: Skipping duplicate push', [
                    'user_id' => $userId,
                    'notification_class' => $notificationClass,
                ]);
                return;
            }
            
            // Marquer comme envoyée avant d'envoyer (évite les race conditions)
            Cache::put($dedupeKey, true, now()->addSeconds(30));

            // Récupérer les données de la notification
            $data = [];
            if (is_object($notification) && method_exists($notification, 'toArray')) {
                $data = (array) call_user_func([$notification, 'toArray'], $notifiable);
            }

            // Construire le titre et le message
            $title = $this->extractTitle($notification, $data);
            $body = $this->extractBody($notification, $data);
            $url = $data['url'] ?? $data['action_url'] ?? '/notifications';

            // Envoyer via OneSignal
            $result = $this->oneSignal->sendToUser($userId, $title, $body, [
                'type' => $data['type'] ?? 'notification',
                'url' => $url,
            ]);

            Log::info('SendOneSignalPush: Result', [
                'user_id' => $userId,
                'notification' => $notificationClass,
                'result' => $result ? 'SUCCESS' : 'FAILED',
            ]);

            if ($result) {
                // Marquer comme pushée dans la table notifications
                $this->markAsPushed($event);
            }

        } catch (\Exception $e) {
            Log::error('SendOneSignalPush: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Marquer la notification comme pushée
     */
    private function markAsPushed(NotificationSent $event): void
    {
        try {
            $response = $event->response;
            
            // Si la réponse contient l'ID de la notification database
            if ($response && isset($response->id)) {
                \App\Models\Notification::where('id', $response->id)
                    ->update(['push_sent_at' => now()]);
            } else {
                // Sinon, on cherche la dernière notification pour cet utilisateur
                \App\Models\Notification::where('notifiable_id', $event->notifiable->id)
                    ->whereNull('push_sent_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->update(['push_sent_at' => now()]);
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs de marquage
        }
    }

    /**
     * Extraire le titre de la notification
     */
    private function extractTitle($notification, array $data): string
    {
        // Mapper les types de notifications aux titres
        $typeToTitle = [
            'App\\Notifications\\NewDeliveryAvailable' => '🚗 Nouvelle livraison disponible',
            'App\\Notifications\\DriverApprovedNotification' => '✅ Compte livreur approuvé',
            'App\\Notifications\\NewFoodOrder' => '🍽️ Nouvelle commande',
            'App\\Notifications\\FoodOrderAccepted' => '✅ Commande acceptée',
            'App\\Notifications\\FoodOrderRejected' => '❌ Commande refusée',
            'App\\Notifications\\FoodOrderPreparing' => '👨‍🍳 En préparation',
            'App\\Notifications\\FoodOrderReady' => '🔔 Commande prête',
            'App\\Notifications\\FoodOrderReadyForDriver' => '📦 Commande prête à récupérer',
            'App\\Notifications\\FoodOrderDriverAssigned' => '🚚 Livreur assigné',
            'App\\Notifications\\FoodOrderPickedUp' => '🚚 Commande récupérée',
            'App\\Notifications\\FoodOrderPickedUpForPrestataire' => '🚚 Commande récupérée',
            'App\\Notifications\\FoodOrderInTransit' => '🛵 En livraison',
            'App\\Notifications\\FoodOrderInTransitForPrestataire' => '🛵 En livraison',
            'App\\Notifications\\FoodOrderDelivered' => '✅ Commande livrée',
            'App\\Notifications\\FoodOrderDeliveredForPrestataire' => '✅ Commande livrée',
            'App\\Notifications\\FoodOrderCompleted' => '✅ Commande livrée',
            'App\\Notifications\\FoodOrderReminderTomorrow' => '⏰ Rappel commande (demain)',
            'App\\Notifications\\FoodOrderReminder4h' => '⏰ Rappel commande (4h)',
            'App\\Notifications\\FoodOrderRefunded' => '💸 Remboursement',
            'App\\Notifications\\FoodOrderConvertedToPickup' => '🏪 Retrait sur place',
            'App\\Notifications\\FoodOrderConfirmedByClient' => '✅ Réception confirmée',
            'App\\Notifications\\NewBookingNotification' => '📅 Nouvelle réservation',
            'App\\Notifications\\BookingConfirmedNotification' => '✅ Réservation confirmée',
            'App\\Notifications\\BookingCancelledNotification' => '❌ Réservation annulée',
            'App\\Notifications\\NewMessageNotification' => '💬 Nouveau message',
            'App\\Notifications\\NewReviewNotification' => '⭐ Nouvel avis',
            'App\\Notifications\\NewTenderResponseNotification' => '📋 Nouvelle réponse',
            'App\\Notifications\\NewTenderMatchNotification' => '🎯 Nouvelle opportunité',
            'App\\Notifications\\PaymentReceivedNotification' => '💰 Paiement reçu',
        ];

        $class = get_class($notification);
        
        return $typeToTitle[$class] ?? $data['title'] ?? 'TaPrestation';
    }

    /**
     * Extraire le corps du message
     */
    private function extractBody($notification, array $data): string
    {
        if (isset($data['message'])) {
            return $data['message'];
        }
        
        if (isset($data['body'])) {
            return $data['body'];
        }

        // Construire un message par défaut
        $class = class_basename($notification);
        return "Vous avez reçu une notification: $class";
    }
}
