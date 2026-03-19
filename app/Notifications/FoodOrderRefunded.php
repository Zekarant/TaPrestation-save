<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Notification;

class FoodOrderRefunded extends Notification
{
    public FoodOrder $order;
    public string $reason;

    public function __construct(FoodOrder $order, string $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'food_order_refunded',
            'title' => '💸 Remboursement',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount_refunded' => $this->order->amount_refunded,
            'reason' => $this->reason,
            'message' => "Votre commande #{$this->order->order_number} a été remboursée ({$this->order->amount_refunded}€). Raison: {$this->reason}",
            'url' => route('food.orders.show', $this->order),
        ];
    }
}
