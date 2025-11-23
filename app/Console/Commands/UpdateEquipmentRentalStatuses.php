<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateEquipmentRentalStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentals:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update equipment rental statuses based on current date (mark as in_use, returned, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting equipment rental status updates...');
        
        try {
            $result = Equipment::updateRentalStatuses();
            
            $this->info('Successfully updated rental statuses:');
            $this->info('- Ended rentals processed: ' . $result['ended_rentals']);
            $this->info('- Starting rentals processed: ' . $result['starting_rentals']);
            
            // Log the results
            Log::info('Equipment rental statuses updated', $result);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error updating rental statuses: ' . $e->getMessage());
            Log::error('Error in rentals:update-statuses command', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}