<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FoodOrderCancelledForDriver extends Notification implements ShouldQueue
{
    use Queueable;

    public FoodOrder $order;
    public string $reason;

    public function __construct(FoodOrder $order, string $reason = null)
    {
        $this->order = $order;
        $this->reason = $reason ?? 'Aucune raison spécifiée';
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'food_order_cancelled_driver',
            'title' => '❌ Livraison annulée',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire->nom ?? '',
            'reason' => $this->reason,
            'message' => 'La commande #' . $this->order->order_number . ' a été annulée. Vous n\'avez plus besoin de la livrer.',
            'url' => route('driver.deliveries'),
        ];
    }
}
