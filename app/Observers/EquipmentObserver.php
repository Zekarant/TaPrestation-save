<?php

namespace App\Observers;

use App\Models\Equipment;
use App\Notifications\PrestataireNewItemNotification;
use Illuminate\Support\Facades\Log;

class EquipmentObserver
{
    /**
     * Handle the Equipment "created" event.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return void
     */
    public function created(Equipment $equipment)
    {
        Log::info('EquipmentObserver: Equipment created', ['equipment_id' => $equipment->id]);
        $this->notifyFollowers($equipment->prestataire, 'equipment', $equipment);
    }

    /**
     * Notify followers of a prestataire when new equipment is added.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @param  string  $itemType
     * @param  mixed  $item
     * @return void
     */
    private function notifyFollowers($prestataire, $itemType, $item)
    {
        Log::info('EquipmentObserver: Notifying followers', [
            'prestataire_id' => $prestataire->id,
            'item_type' => $itemType,
            'item_id' => $item->id,
            'followers_count' => $prestataire->followers->count()
        ]);
        
        // Get all followers of the prestataire
        $followers = $prestataire->followers;
        
        // Send notification to each follower
        foreach ($followers as $follower) {
            Log::info('EquipmentObserver: Sending notification to follower', [
                'follower_id' => $follower->id,
                'user_id' => $follower->user->id
            ]);
            $follower->user->notify(new PrestataireNewItemNotification($prestataire, $itemType, $item));
        }
    }
}