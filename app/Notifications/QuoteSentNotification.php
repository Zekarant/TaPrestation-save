<?php

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $prestataireName = $this->quote->prestataire->user->name ?? 'Un prestataire';

        return (new MailMessage)
            ->subject('Nouveau devis reçu - ' . $this->quote->reference_number)
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line($prestataireName . ' vous a envoyé un devis.')
            ->line('**Référence :** ' . $this->quote->reference_number)
            ->line('**Titre :** ' . ($this->quote->title ?? 'N/A'))
            ->line('**Montant total :** ' . number_format($this->quote->total, 2, ',', ' ') . ' €')
            ->line('**Valide jusqu\'au :** ' . ($this->quote->valid_until ? $this->quote->valid_until->format('d/m/Y') : 'N/A'))
            ->action('Voir le devis', url('/client/quotes/' . $this->quote->id))
            ->line('Merci de votre confiance !');
    }

    public function toArray($notifiable)
    {
        return [
            'quote_id' => $this->quote->id,
            'reference_number' => $this->quote->reference_number,
            'prestataire_name' => $this->quote->prestataire->user->name ?? 'Prestataire',
            'title' => 'Nouveau devis reçu',
            'message' => 'Vous avez reçu un devis (' . $this->quote->reference_number . ') de ' . ($this->quote->prestataire->user->name ?? 'un prestataire'),
            'total' => $this->quote->total,
            'url' => url('/client/quotes/' . $this->quote->id),
            'type' => 'quote_sent',
        ];
    }
}
