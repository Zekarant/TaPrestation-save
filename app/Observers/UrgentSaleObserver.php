<?php

namespace App\Observers;

use App\Models\UrgentSale;
use App\Notifications\PrestataireNewItemNotification;

class UrgentSaleObserver
{
    /**
     * Handle the UrgentSale "created" event.
     *
     * @param  \App\Models\UrgentSale  $urgentSale
     * @return void
     */
    public function created(UrgentSale $urgentSale)
    {
        // Ne notifier que si c'est une annonce de prestataire (pas une annonce client)
        if ($urgentSale->prestataire_id && $urgentSale->prestataire) {
            $this->notifyFollowers($urgentSale->prestataire, 'urgent_sale', $urgentSale);
        }
    }

    /**
     * Notify followers of a prestataire when a new urgent sale is added.
     */
    private function notifyFollowers($prestataire, $itemType, $item)
    {
        $followers = $prestataire->followers()->with('user')->get();

        foreach ($followers as $follower) {
            if ($follower->user) {
                $follower->user->notify(new PrestataireNewItemNotification($prestataire, $itemType, $item));
            }
        }
    }
}