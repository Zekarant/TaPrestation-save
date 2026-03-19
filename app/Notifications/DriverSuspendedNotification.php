<?php

namespace App\Notifications;

use App\Models\DeliveryDriver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected DeliveryDriver $driver;
    protected float $avgRating;
    protected int $ratingCount;

    public function __construct(DeliveryDriver $driver, float $avgRating, int $ratingCount)
    {
        $this->driver = $driver;
        $this->avgRating = $avgRating;
        $this->ratingCount = $ratingCount;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Livreur parrainé suspendu - ' . $this->driver->full_name)
            ->greeting('Bonjour,')
            ->line('Le livreur que vous avez parrainé a été suspendu automatiquement.')
            ->line('')
            ->line('**Livreur :** ' . $this->driver->full_name)
            ->line('**Note moyenne :** ' . $this->avgRating . '/5')
            ->line('**Nombre d\'avis :** ' . $this->ratingCount)
            ->line('**Raison :** Note moyenne insuffisante (< 3/5)')
            ->line('')
            ->line('En tant que parrain, vous êtes informé de cette suspension. Le livreur ne peut plus accepter de commandes jusqu\'à nouvel ordre.')
            ->action('Voir le profil du livreur', url('/prestataire/drivers/' . $this->driver->id))
            ->salutation('L\'équipe TaPrestation');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'driver_suspended',
            'driver_id' => $this->driver->id,
            'driver_name' => $this->driver->full_name,
            'avg_rating' => $this->avgRating,
            'rating_count' => $this->ratingCount,
            'message' => "Le livreur {$this->driver->full_name} que vous avez parrainé a été suspendu (note: {$this->avgRating}/5)",
        ];
    }
}
