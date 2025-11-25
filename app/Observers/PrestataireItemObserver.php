<?php

namespace App\Observers;

use App\Models\Service;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Notifications\PrestataireNewItemNotification;

class PrestataireItemObserver
{
    /**
     * Handle the Service "created" event.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function created(Service $service)
    {
        $this->notifyFollowers($service->prestataire, 'service', $service);
    }

    /**
     * Handle the Equipment "created" event.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return void
     */
    public function created(Equipment $equipment)
    {
        $this->notifyFollowers($equipment->prestataire, 'equipment', $equipment);
    }

    /**
     * Handle the UrgentSale "created" event.
     *
     * @param  \App\Models\UrgentSale  $urgentSale
     * @return void
     */
    public function created(UrgentSale $urgentSale)
    {
        $this->notifyFollowers($urgentSale->prestataire, 'urgent_sale', $urgentSale);
    }

    /**
     * Notify followers of a prestataire when a new item is added.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @param  string  $itemType
     * @param  mixed  $item
     * @return void
     */
    private function notifyFollowers($prestataire, $itemType, $item)
    {
        // Get all followers of the prestataire
        $followers = $prestataire->followers;
        
        // Send notification to each follower
        foreach ($followers as $follower) {
            $follower->user->notify(new PrestataireNewItemNotification($prestataire, $itemType, $item));
        }
    }
}