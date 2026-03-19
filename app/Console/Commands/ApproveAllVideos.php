<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;

class ApproveAllVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:approve-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve all existing videos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $updatedCount = Video::where('status', 'pending')->update(['status' => 'approved']);
        $this->info($updatedCount . ' videos have been approved.');
        return 0;
    }
}