<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderDelivered extends Notification
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
            'type' => 'food_order_delivered',
            'title' => '✅ Commande livrée',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Votre commande #' . $this->order->order_number . ' a été livrée.',
            'url' => route('food.orders.track', $this->order),
        ];
    }
}
