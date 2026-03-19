<?php

namespace App\Notifications;

use App\Models\Cart;
use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CartPaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public Cart $cart;
    public PaymentTransaction $transaction;

    public function __construct(Cart $cart, PaymentTransaction $transaction)
    {
        $this->cart = $cart;
        $this->transaction = $transaction;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format($this->transaction->amount ?? 0, 2, ',', ' ');
        $buyerName = $this->cart->user?->name ?? 'Un client';
        
        return (new MailMessage)
            ->subject('💰 Nouveau paiement reçu - ' . $amount . ' €')
            ->greeting('Bonjour ' . ($notifiable->name ?? 'Prestataire') . ' !')
            ->line('Vous avez reçu un nouveau paiement de **' . $buyerName . '**.')
            ->line('**Montant :** ' . $amount . ' €')
            ->line('**Référence :** Panier #' . $this->cart->id)
            ->line('**Transaction :** ' . ($this->transaction->stripe_payment_intent_id ?? 'N/A'))
            ->action('Voir mes paiements', route('prestataire.payments.index'))
            ->line('Merci d\'utiliser TaPrestation !');
    }

    public function toArray($notifiable): array
    {
        $amount = number_format($this->transaction->amount ?? 0, 2, ',', ' ');
        $buyerName = $this->cart->user?->name ?? 'Un client';
        
        return [
            'type' => 'cart_payment_received',
            'title' => 'Paiement reçu',
            'message' => $buyerName . ' a payé ' . $amount . ' € pour le panier #' . $this->cart->id,
            'cart_id' => $this->cart->id,
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'buyer_name' => $buyerName,
            'icon' => 'fa-money-bill-wave',
            'color' => 'green',
            'url' => route('prestataire.payments.index'),
        ];
    }
}
