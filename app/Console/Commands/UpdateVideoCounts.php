<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;

class UpdateVideoCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:update-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update video counts (views, likes, comments) for all videos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating video counts...');
        
        $videos = Video::all();
        $bar = $this->output->createProgressBar($videos->count());
        $bar->start();
        
        foreach ($videos as $video) {
            $video->update([
                'views_count' => $video->views_count ?? 0,
                'likes_count' => $video->likes()->count(),
                'comments_count' => $video->comments()->count(),
                'shares_count' => $video->shares_count ?? 0,
            ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nVideo counts updated successfully!");
    }
}