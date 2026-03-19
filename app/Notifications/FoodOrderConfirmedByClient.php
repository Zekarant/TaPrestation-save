<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FoodOrderConfirmedByClient extends Notification implements ShouldQueue
{
    use Queueable;

    public FoodOrder $order;

    public function __construct(FoodOrder $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Le client a confirmé la réception - Commande #' . $this->order->order_number)
            ->greeting('Confirmation du client !')
            ->line('Le client ' . $this->order->client->name . ' a confirmé la réception de sa commande.')
            ->line('**Numéro de commande:** ' . $this->order->order_number)
            ->line('**Total:** ' . number_format($this->order->total, 2) . ' €')
            ->line('La commande est maintenant terminée.')
            ->action('Voir la commande', route('prestataire.food-orders.show', $this->order))
            ->line('Merci pour votre service !');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'food_order_confirmed_by_client',
            'title' => '✅ Réception confirmée',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'client_name' => $this->order->client->name,
            'total' => $this->order->total,
            'message' => 'Commande #' . $this->order->order_number . ' confirmée par le client !',
            'url' => route('prestataire.food-orders.show', $this->order),
        ];
    }
}
