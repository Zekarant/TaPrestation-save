<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderPickedUpForPrestataire extends Notification
{
    public FoodOrder $order;

    public function __construct(FoodOrder $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'food_order_picked_up',
            'title' => '🚚 Commande récupérée',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'client_name' => $this->order->client?->name,
            'message' => 'Le livreur a récupéré la commande #' . $this->order->order_number . '.',
            'url' => route('prestataire.food-orders.show', $this->order),
        ];
    }
}
