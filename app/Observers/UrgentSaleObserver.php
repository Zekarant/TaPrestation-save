<?php

namespace App\Observers;

use App\Models\UrgentSale;
use App\Notifications\PrestataireNewItemNotification;
use Illuminate\Support\Facades\Log;

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
        Log::info('UrgentSaleObserver: UrgentSale created', ['urgent_sale_id' => $urgentSale->id]);
        $this->notifyFollowers($urgentSale->prestataire, 'urgent_sale', $urgentSale);
    }

    /**
     * Notify followers of a prestataire when a new urgent sale is added.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @param  string  $itemType
     * @param  mixed  $item
     * @return void
     */
    private function notifyFollowers($prestataire, $itemType, $item)
    {
        Log::info('UrgentSaleObserver: Notifying followers', [
            'prestataire_id' => $prestataire->id,
            'item_type' => $itemType,
            'item_id' => $item->id,
            'followers_count' => $prestataire->followers->count()
        ]);
        
        // Get all followers of the prestataire
        $followers = $prestataire->followers;
        
        // Send notification to each follower
        foreach ($followers as $follower) {
            Log::info('UrgentSaleObserver: Sending notification to follower', [
                'follower_id' => $follower->id,
                'user_id' => $follower->user->id
            ]);
            $follower->user->notify(new PrestataireNewItemNotification($prestataire, $itemType, $item));
        }
    }
}