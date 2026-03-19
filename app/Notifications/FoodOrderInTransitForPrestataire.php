<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderInTransitForPrestataire extends Notification
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
            'type' => 'food_order_in_transit',
            'title' => '🛵 En livraison',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'client_name' => $this->order->client?->name,
            'message' => 'Commande #' . $this->order->order_number . ' : le livreur est en route.',
            'url' => route('prestataire.food-orders.show', $this->order),
        ];
    }
}
