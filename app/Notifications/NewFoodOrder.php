<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NewFoodOrder extends Notification
{

    public FoodOrder $order;

    public function __construct(FoodOrder $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        // IMPORTANT: 'database' en premier pour que le push soit envoyé même si mail échoue
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->subject('🍽️ Nouvelle commande #' . $this->order->order_number)
                ->greeting('Nouvelle commande !')
                ->line('Vous avez reçu une nouvelle commande de ' . $this->order->client->name . '.')
                ->line('**Numéro de commande:** ' . $this->order->order_number)
                ->line('**Total:** ' . number_format($this->order->total, 2) . ' €')
                ->line('**Type:** ' . $this->order->delivery_type_label)
                ->line('**Nombre d\'articles:** ' . $this->order->items->count())
                ->action('Voir la commande', route('prestataire.food-orders.show', $this->order))
                ->line('Veuillez accepter ou refuser cette commande rapidement.');
        } catch (\Exception $e) {
            Log::error('Erreur notification NewFoodOrder: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_food_order',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'client_name' => $this->order->client->name,
            'total' => $this->order->total,
            'message' => 'Nouvelle commande #' . $this->order->order_number . ' de ' . $this->order->client->name,
            'url' => route('prestataire.food-orders.show', $this->order),
        ];
    }
}
