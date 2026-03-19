<?php

namespace App\Notifications;

use App\Models\DeliveryDriver;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverApprovedNotification extends Notification
{
    use Queueable;

    protected DeliveryDriver $driver;

    public function __construct(DeliveryDriver $driver)
    {
        $this->driver = $driver;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre inscription livreur a été approuvée !')
            ->greeting('Félicitations ' . ($this->driver->first_name ?? '') . ' !')
            ->line('Votre inscription comme livreur sur TaPrestation a été approuvée.')
            ->line('Vous pouvez maintenant recevoir et accepter des livraisons.')
            ->action('Accéder à mon espace livreur', route('driver.dashboard'))
            ->line('Bonne route !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'driver_approved',
            'driver_id' => $this->driver->id,
            'message' => 'Votre inscription livreur a été approuvée !',
        ];
    }
}
