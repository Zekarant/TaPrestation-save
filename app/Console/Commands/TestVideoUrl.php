<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Command;

class TestVideoUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:video-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test video URL generation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $video = Video::first();
        
        if ($video) {
            $this->info("Video ID: " . $video->id);
            $this->info("Video Title: " . $video->title);
            $this->info("Video Path: " . $video->video_path);
            $this->info("Video URL: " . $video->video_url);
            
            // Test if file exists
            $this->info("File exists: " . (file_exists(storage_path('app/public/' . $video->video_path)) ? 'Yes' : 'No'));
        } else {
            $this->error("No videos found in the database.");
        }
        
        return 0;
    }
}