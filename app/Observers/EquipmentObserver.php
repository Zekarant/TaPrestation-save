<?php

namespace App\Observers;

use App\Models\Equipment;
use App\Notifications\PrestataireNewItemNotification;

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
        $this->notifyFollowers($equipment->prestataire, 'equipment', $equipment);
    }

    /**
     * Notify followers of a prestataire when new equipment is added.
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