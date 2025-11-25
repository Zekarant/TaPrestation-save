<?php

namespace App\Observers;

use App\Models\Service;
use App\Notifications\PrestataireNewItemNotification;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     *
     * @param  \App\Models\Service  $service
     * @return void
     */
    public function created(Service $service)
    {
        Log::info('ServiceObserver: Service created', ['service_id' => $service->id]);
        $this->notifyFollowers($service->prestataire, 'service', $service);
    }

    /**
     * Notify followers of a prestataire when a new service is added.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @param  string  $itemType
     * @param  mixed  $item
     * @return void
     */
    private function notifyFollowers($prestataire, $itemType, $item)
    {
        Log::info('ServiceObserver: Notifying followers', [
            'prestataire_id' => $prestataire->id,
            'item_type' => $itemType,
            'item_id' => $item->id,
            'followers_count' => $prestataire->followers->count()
        ]);
        
        // Get all followers of the prestataire
        $followers = $prestataire->followers;
        
        // Send notification to each follower
        foreach ($followers as $follower) {
            Log::info('ServiceObserver: Sending notification to follower', [
                'follower_id' => $follower->id,
                'user_id' => $follower->user->id
            ]);
            $follower->user->notify(new PrestataireNewItemNotification($prestataire, $itemType, $item));
        }
    }
}