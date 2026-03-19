<?php

namespace App\Notifications;

use App\Models\TenderResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTenderResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TenderResponse $response;

    public function __construct(TenderResponse $response)
    {
        $this->response = $response;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $tender = $this->response->tenderRequest;
        $prestataire = $this->response->prestataire;

        return (new MailMessage)
            ->subject('Nouvelle proposition pour : ' . $tender->title)
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Vous avez reçu une nouvelle proposition pour votre appel d\'offre.')
            ->line('**Appel d\'offre :** ' . $tender->title)
            ->line('**Prestataire :** ' . $prestataire->user->name)
            ->line('**Prix proposé :** ' . number_format($this->response->proposed_price, 2) . ' €')
            ->line('**Score de correspondance :** ' . $this->response->match_score . '%')
            ->action('Voir la proposition', route('client.tenders.show', $tender))
            ->line('Consultez la proposition et répondez rapidement pour ne pas manquer ce prestataire !');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'tender_response',
            'tender_id' => $this->response->tender_request_id,
            'response_id' => $this->response->id,
            'prestataire_id' => $this->response->prestataire_id,
            'prestataire_name' => $this->response->prestataire->user->name ?? 'Prestataire',
            'tender_title' => $this->response->tenderRequest->title ?? 'Appel d\'offre',
            'proposed_price' => $this->response->proposed_price,
            'match_score' => $this->response->match_score,
            'message' => 'Nouvelle proposition de ' . ($this->response->prestataire->user->name ?? 'un prestataire'),
        ];
    }
}
