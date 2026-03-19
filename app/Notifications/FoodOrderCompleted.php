<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FoodOrderCompleted extends Notification implements ShouldQueue
{
    use Queueable;

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

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⭐ Commande #' . $this->order->order_number . ' terminée - Laissez un avis !')
            ->greeting('Merci pour votre commande !')
            ->line('Votre commande chez ' . $this->order->prestataire->nom . ' est terminée.')
            ->line('**Numéro de commande:** ' . $this->order->order_number)
            ->line('**Total:** ' . number_format($this->order->total, 2) . ' €')
            ->line('Nous espérons que vous avez apprécié votre repas !')
            ->action('Laisser un avis', route('food.orders.show', $this->order))
            ->line('Votre avis nous aide à améliorer notre service.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'food_order_completed',
            'title' => '✅ Commande terminée',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire->nom,
            'total' => $this->order->total,
            'message' => 'Commande #' . $this->order->order_number . ' terminée ! Laissez un avis.',
            'url' => route('food.orders.show', $this->order),
        ];
    }
}
