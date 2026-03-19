<?php

namespace App\Observers;

use App\Models\Video;
use App\Notifications\PrestataireNewItemNotification;
use Illuminate\Support\Facades\Log;

class VideoObserver
{
    /**
     * Handle the Video "created" event.
     *
     * @param  \App\Models\Video  $video
     * @return void
     */
    public function created(Video $video)
    {
        Log::info('VideoObserver: Video created', ['video_id' => $video->id]);
        // Disabled notifications for videos as per requirement
        // $this->notifyFollowers($video->prestataire, 'video', $video);
    }

    /**
     * Notify followers of a prestataire when a new video is added.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @param  string  $itemType
     * @param  mixed  $item
     * @return void
     */
    private function notifyFollowers($prestataire, $itemType, $item)
    {
        Log::info('VideoObserver: Notifying followers', [
            'prestataire_id' => $prestataire->id,
            'item_type' => $itemType,
            'item_id' => $item->id,
            'followers_count' => $prestataire->followers->count()
        ]);
        
        // Get all followers of the prestataire
        $followers = $prestataire->followers;
        
        // Send notification to each follower
        foreach ($followers as $follower) {
            Log::info('VideoObserver: Sending notification to follower', [
                'follower_id' => $follower->id,
                'user_id' => $follower->user->id
            ]);
            $follower->user->notify(new PrestataireNewItemNotification($prestataire, $itemType, $item));
        }
    }
}