<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PrestataireNewItemNotification extends Notification
{
    protected $prestataire;
    protected $itemType;
    protected $item;
    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Prestataire $prestataire
     * @param string $itemType
     * @param mixed $item
     * @return void
     */
    public function __construct($prestataire, $itemType, $item)
    {
        $this->prestataire = $prestataire;
        $this->itemType = $itemType;
        $this->item = $item;
        
        // Set the message based on the item type (in French)
        switch ($itemType) {
            case 'service':
                $this->message = "Le prestataire {$prestataire->company_name} a ajouté un nouveau service : {$item->title}";
                break;
            case 'equipment':
                $this->message = "Le prestataire {$prestataire->company_name} a ajouté un nouvel équipement : {$item->name}";
                break;
            case 'urgent_sale':
                $this->message = "Le prestataire {$prestataire->company_name} a ajouté une nouvelle annonce : {$item->title}";
                break;
            case 'video':
                $this->message = "Le prestataire {$prestataire->company_name} a ajouté une nouvelle vidéo : {$item->title}";
                break;
            default:
                $this->message = "Le prestataire {$prestataire->company_name} a ajouté un nouvel élément";
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'prestataire_id' => $this->prestataire->id,
            'item_type' => $this->itemType,
            'item_id' => $this->item->id,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
        ];
    }

    /**
     * Get the action URL for the notification.
     *
     * @return string
     */
    private function getActionUrl()
    {
        switch ($this->itemType) {
            case 'service':
                return route('services.show', $this->item->id);
            case 'equipment':
                return route('equipment.show', $this->item->id);
            case 'urgent_sale':
                return route('urgent-sales.show', $this->item->id);
            case 'video':
                // We need to determine the appropriate route for videos
                // For now, we'll link to the prestataire's profile
                return route('client.prestataires.show', $this->prestataire->id);
            default:
                return route('client.prestataires.show', $this->prestataire->id);
        }
    }

    /**
     * Get the action text for the notification.
     *
     * @return string
     */
    private function getActionText()
    {
        switch ($this->itemType) {
            case 'service':
                return 'Voir le service';
            case 'equipment':
                return 'Voir l\'équipement';
            case 'urgent_sale':
                return 'Voir l\'annonce';
            case 'video':
                return 'Voir la vidéo';
            default:
                return 'Voir le prestataire';
        }
    }
}