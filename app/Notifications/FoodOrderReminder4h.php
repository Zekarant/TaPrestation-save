<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

/**
 * Notification de rappel 4h avant une commande food planifiée.
 * Envoyée au client ET au prestataire.
 */
class FoodOrderReminder4h extends Notification
{
    public FoodOrder $order;
    public string $recipientType; // 'client' ou 'prestataire'

    public function __construct(FoodOrder $order, string $recipientType = 'client')
    {
        $this->order = $order;
        $this->recipientType = $recipientType;
    }

    public function via($notifiable): array
    {
        // Push géré par SendOneSignalPush après l'envoi de la notification database.
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $requestedAt = $this->order->requested_at;
        $timeText = $requestedAt ? $requestedAt->format('H:i') : null;
        $isClient = $this->recipientType === 'client';

        // URL différente selon le destinataire
        $url = $isClient
            ? route('food.orders.show', $this->order)
            : route('prestataire.food-orders.show', $this->order);

        // Message personnalisé selon le destinataire
        if ($isClient) {
            $title = '⏰ Rappel : votre commande dans 4h';
            $message = $timeText
                ? 'Votre commande #' . $this->order->order_number . ' est prévue à ' . $timeText . '. Pensez à vous préparer !'
                : 'Votre commande #' . $this->order->order_number . ' est prévue bientôt !';
        } else {
            $title = '⏰ Rappel : commande dans 4h';
            $message = $timeText
                ? 'Commande #' . $this->order->order_number . ' prévue à ' . $timeText . '. Préparez-vous à la traiter !'
                : 'Commande #' . $this->order->order_number . ' prévue bientôt !';
        }

        return [
            'type' => 'food_order_reminder_4h',
            'title' => $title,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'client_name' => $this->order->client?->name,
            'prestataire_name' => $this->order->prestataire?->nom,
            'requested_at' => $requestedAt?->toIso8601String(),
            'recipient_type' => $this->recipientType,
            'delivery_type' => $this->order->delivery_type,
            'message' => $message,
            'url' => $url,
        ];
    }
}
