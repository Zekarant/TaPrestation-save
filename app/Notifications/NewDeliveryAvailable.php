<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class NewDeliveryAvailable extends Notification
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
        $distance = $this->order->delivery_distance 
            ? number_format($this->order->delivery_distance, 1) . ' km' 
            : 'Distance inconnue';

        return [
            'type' => 'new_delivery_available',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire->nom ?? 'Restaurant',
            'delivery_address' => $this->order->delivery_address,
            'distance' => $distance,
            'estimated_earnings' => $this->order->delivery_fee ?? 0,
            'message' => "🚗 Nouvelle livraison disponible ! {$this->order->prestataire->nom} - {$distance}",
            'url' => route('driver.deliveries.available'),
        ];
    }
}
