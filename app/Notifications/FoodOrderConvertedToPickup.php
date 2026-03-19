<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderConvertedToPickup extends Notification
{
    public FoodOrder $order;
    public float $refundedDeliveryFee;

    public function __construct(FoodOrder $order, float $refundedDeliveryFee = 0)
    {
        $this->order = $order;
        $this->refundedDeliveryFee = $refundedDeliveryFee;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $message = 'Votre commande #' . $this->order->order_number . ' a été convertie en retrait sur place.';
        
        if ($this->refundedDeliveryFee > 0) {
            $message .= ' Les frais de livraison (' . number_format($this->refundedDeliveryFee, 2) . '€) vous seront remboursés.';
        }
        
        $message .= ' Veuillez venir récupérer votre commande chez ' . $this->order->prestataire->nom . '.';

        return [
            'type' => 'food_order_converted_pickup',
            'title' => '🏪 Retrait sur place',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire->nom,
            'refunded_amount' => $this->refundedDeliveryFee,
            'message' => $message,
            'url' => route('food.orders.track', $this->order),
        ];
    }
}
