<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderDriverAssigned extends Notification
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
            'type' => 'food_order_driver_assigned',
            'title' => '🚚 Livreur assigné',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire?->nom,
            'message' => 'Un livreur a accepté votre commande #' . $this->order->order_number . '.',
            'url' => route('food.orders.track', $this->order),
        ];
    }
}
