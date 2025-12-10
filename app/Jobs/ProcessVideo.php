<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use getID3;

class ProcessVideo implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $videoPath = $this->video->video_path;
        $fullPath = Storage::disk('public')->path($videoPath);

        // Check file size
        $fileSizeInMb = filesize($fullPath) / (1024 * 1024);
        if ($fileSizeInMb > 500) {
            // Mark video as failed if it exceeds size limit
            $this->video->status = 'failed';
            $this->video->save();
            return;
        }

        // Move the video to the final folder
        $newPath = 'videos/' . basename($videoPath);
        Storage::disk('public')->move($videoPath, $newPath);

        // Update the path in the database
        $this->video->video_path = $newPath;

        // More robust duration detection
        $getID3 = new getID3();

        // Enable certain modules for better format support
        $getID3->option_md5_data = true;
        $getID3->option_md5_data_source = true;
        $getID3->encoding = 'UTF-8';

        $fileInfo = $getID3->analyze(Storage::disk('public')->path($newPath));

        // Try to get duration from different possible sources
        $duration = 0;

        if (isset($fileInfo['playtime_seconds']) && is_numeric($fileInfo['playtime_seconds'])) {
            $duration = $fileInfo['playtime_seconds'];
        } elseif (isset($fileInfo['video']['playtime_seconds']) && is_numeric($fileInfo['video']['playtime_seconds'])) {
            $duration = $fileInfo['video']['playtime_seconds'];
        } elseif (isset($fileInfo['audio']['playtime_seconds']) && is_numeric($fileInfo['audio']['playtime_seconds'])) {
            $duration = $fileInfo['audio']['playtime_seconds'];
        }

        // If we still don't have duration, try to calculate from other data
        if ($duration == 0 && isset($fileInfo['bitrate']) && isset($fileInfo['filesize'])) {
            // Rough calculation: duration = filesize / bitrate
            if ($fileInfo['bitrate'] > 0) {
                $duration = $fileInfo['filesize'] / ($fileInfo['bitrate'] / 8);
            }
        }

        // Check duration constraint
        if ($duration > 60) {
            // Mark video as failed if it exceeds duration limit
            $this->video->status = 'failed';
            $this->video->save();
            return;
        }

        $this->video->duration = $duration;
        $this->video->status = 'processed'; // Set status to processed instead of keeping approved
        $this->video->save();
    }
}