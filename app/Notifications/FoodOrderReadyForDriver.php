<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderReadyForDriver extends Notification
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
            'type' => 'food_order_ready_for_driver',
            'title' => '📦 Commande prête',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire?->nom,
            'message' => 'Commande #' . $this->order->order_number . ' est prête à être récupérée.',
            'url' => route('driver.deliveries.show', $this->order),
        ];
    }
}
