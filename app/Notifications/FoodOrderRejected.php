<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FoodOrderRejected extends Notification implements ShouldQueue
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
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('❌ Votre commande #' . $this->order->order_number . ' a été refusée')
            ->greeting('Désolé...')
            ->line('Votre commande a été refusée par ' . $this->order->prestataire->nom . '.')
            ->line('**Numéro de commande:** ' . $this->order->order_number)
            ->line('**Raison:** ' . $this->reason)
            ->line('Aucun montant n\'a été débité de votre compte.')
            ->action('Explorer d\'autres prestataires', url('/prestataires'))
            ->line('Nous vous invitons à passer commande chez un autre prestataire.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'food_order_rejected',
            'title' => '❌ Commande refusée',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire->nom,
            'reason' => $this->reason,
            'message' => 'Votre commande #' . $this->order->order_number . ' a été refusée.',
            'url' => route('food.orders.show', $this->order),
        ];
    }
}
