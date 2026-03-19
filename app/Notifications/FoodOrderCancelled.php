<?php

namespace App\Notifications;

use App\Models\FoodOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FoodOrderCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public FoodOrder $order;
    public string $reason;
    public string $cancelledBy;

    public function __construct(FoodOrder $order, string $reason = null, string $cancelledBy = 'system')
    {
        $this->order = $order;
        $this->reason = $reason ?? 'Aucune raison spécifiée';
        $this->cancelledBy = $cancelledBy;
    }

    public function via($notifiable): array
    {
        // IMPORTANT: 'database' en premier pour que le push soit envoyé même si mail échoue
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $cancellerName = match($this->cancelledBy) {
            'prestataire' => $this->order->prestataire->nom ?? 'le prestataire',
            'client' => 'vous',
            default => 'le système'
        };

        return (new MailMessage)
            ->subject('❌ Commande #' . $this->order->order_number . ' annulée')
            ->greeting('Commande annulée')
            ->line('Votre commande a été annulée par ' . $cancellerName . '.')
            ->line('**Numéro de commande:** ' . $this->order->order_number)
            ->line('**Raison:** ' . $this->reason)
            ->when($this->order->payment_status === 'paid' || $this->order->escrow_status === FoodOrder::ESCROW_HELD, function ($mail) {
                return $mail->line('💰 **Un remboursement a été initié.**');
            })
            ->action('Voir les détails', route('food.orders.show', $this->order))
            ->line('N\'hésitez pas à passer une nouvelle commande.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'food_order_cancelled',
            'title' => '❌ Commande annulée',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'prestataire_name' => $this->order->prestataire->nom ?? '',
            'reason' => $this->reason,
            'cancelled_by' => $this->cancelledBy,
            'message' => 'Votre commande #' . $this->order->order_number . ' a été annulée.',
            'url' => route('food.orders.show', $this->order),
        ];
    }
}
