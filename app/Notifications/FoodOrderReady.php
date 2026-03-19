<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderReady extends Notification
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
        $isPickup = $this->order->delivery_type === 'pickup';
        $code = $this->order->delivery_code;
        $msg = 'Votre commande #' . $this->order->order_number . ' est prête !';
        if ($code) {
            $msg .= $isPickup
                ? " Votre code de retrait : {$code}"
                : " Votre code de confirmation : {$code}";
        }

        return [
            'type' => 'food_order_ready',
            'title' => '🔔 Commande prête',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire->nom,
            'delivery_type' => $this->order->delivery_type,
            'delivery_code' => $code,
            'message' => $msg,
            'url' => route('food.orders.track', $this->order),
        ];
    }
}
