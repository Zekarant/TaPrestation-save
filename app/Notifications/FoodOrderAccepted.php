<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FoodOrderAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public FoodOrder $order;

    public function __construct(FoodOrder $order)
    {
        $this->order = $order->withoutRelations();
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Recharger la commande avec les relations nécessaires
        $order = FoodOrder::with(['prestataire', 'items.foodProduct'])->find($this->order->id);
        
        if (!$order) {
            return (new MailMessage)
                ->subject('✅ Votre commande a été acceptée !')
                ->greeting('Bonne nouvelle !')
                ->line('Votre commande a été acceptée.')
                ->line('Vous recevrez une notification quand votre commande sera prête.');
        }
        
        $prestataireName = $order->prestataire?->nom ?? 'le prestataire';
        $prepTime = $order->getEstimatedPreparationTime();
        
        return (new MailMessage)
            ->subject('✅ Votre commande #' . $order->order_number . ' a été acceptée !')
            ->greeting('Bonne nouvelle !')
            ->line('Votre commande a été acceptée par ' . $prestataireName . '.')
            ->line('**Numéro de commande:** ' . $order->order_number)
            ->line('Le prestataire va maintenant préparer votre commande.')
            ->line('Temps de préparation estimé: ' . $prepTime . ' minutes')
            ->action('Suivre ma commande', route('food.orders.track', $order))
            ->line('Vous recevrez une notification quand votre commande sera prête.');
    }

    public function toArray($notifiable): array
    {
        // Recharger la commande avec les relations nécessaires
        $order = FoodOrder::with(['prestataire'])->find($this->order->id);
        
        if (!$order) {
            return [
                'type' => 'food_order_accepted',
                'title' => '✅ Commande acceptée',
                'message' => 'Votre commande a été acceptée !',
            ];
        }
        
        return [
            'type' => 'food_order_accepted',
            'title' => '✅ Commande acceptée',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'prestataire_name' => $order->prestataire?->nom ?? 'Prestataire',
            'message' => 'Votre commande #' . $order->order_number . ' a été acceptée !',
            'url' => route('food.orders.track', $order),
        ];
    }
}
