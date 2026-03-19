<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EquipmentRentalRequest;
use App\Models\User;
use App\Notifications\NewEquipmentRentalRequestNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class TestEquipmentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-equipment-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test equipment rental request notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing equipment rental notification...');
        
        // Get the most recent equipment rental request
        $rentalRequest = EquipmentRentalRequest::with(['equipment.prestataire.user', 'client.user'])->latest()->first();
        
        if (!$rentalRequest) {
            $this->error('No rental requests found');
            return 1;
        }
        
        $this->info("Found rental request ID: " . $rentalRequest->id);
        $this->info("Equipment: " . $rentalRequest->equipment->name);
        $this->info("Client: " . $rentalRequest->client->user->name);
        $this->info("Prestataire user exists: " . ($rentalRequest->equipment->prestataire->user ? 'Yes' : 'No'));
        
        if ($rentalRequest->equipment->prestataire->user) {
            try {
                // Try to send notification
                $user = $rentalRequest->equipment->prestataire->user;
                $this->info("Sending notification to user ID: " . $user->id);
                
                // Create notification and check its data
                $notification = new NewEquipmentRentalRequestNotification($rentalRequest);
                $dataArray = $notification->toArray($user);
                
                $this->info("Notification data:");
                foreach ($dataArray as $key => $value) {
                    $this->info("  $key: " . (is_string($value) ? $value : json_encode($value)));
                }
                
                // Send notification directly without queue
                $user->notify($notification);
                
                $this->info("Notification sent successfully!");
                return 0;
            } catch (\Exception $e) {
                $this->error("Error sending notification: " . $e->getMessage());
                Log::error('Test equipment notification error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return 1;
            }
        } else {
            $this->error("No prestataire user found");
            return 1;
        }
    }
}