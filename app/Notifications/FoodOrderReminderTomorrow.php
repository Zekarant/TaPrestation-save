<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderReminderTomorrow extends Notification
{
    public FoodOrder $order;

    public function __construct(FoodOrder $order)
    {
        $this->order = $order;
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

        return [
            'type' => 'food_order_reminder_tomorrow',
            'title' => '⏰ Rappel commande (demain)',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'client_name' => $this->order->client?->name,
            'prestataire_name' => $this->order->prestataire?->nom,
            'requested_at' => $requestedAt?->toIso8601String(),
            'message' => $timeText
                ? 'Rappel : commande #' . $this->order->order_number . ' prévue demain à ' . $timeText
                : 'Rappel : commande #' . $this->order->order_number . ' prévue demain',
            'url' => route('prestataire.food-orders.show', $this->order),
        ];
    }
}
