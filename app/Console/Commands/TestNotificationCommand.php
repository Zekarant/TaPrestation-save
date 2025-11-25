<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Models\Video;

class TestNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the prestataire notification system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing notification system...');
        
        // Create a prestataire
        $prestataireUser = User::factory()->prestataire()->create();
        $prestataire = Prestataire::factory()->create([
            'user_id' => $prestataireUser->id,
            'company_name' => 'Test Company',
            'is_approved' => true
        ]);

        // Create a client who follows the prestataire
        $clientUser = User::factory()->client()->create();
        $client = Client::factory()->create(['user_id' => $clientUser->id]);
        $client->followedPrestataires()->attach($prestataire->id);

        $this->info("Created prestataire: " . $prestataire->company_name);
        $this->info("Created client: " . $clientUser->name);
        $this->info("Client is now following prestataire");
        
        // Check followers count
        $followersCount = $prestataire->followers->count();
        $this->info("Number of followers: " . $followersCount);

        // Test 1: Create a new service
        $this->info("\n--- Testing Service Notification ---");
        $service = Service::factory()->create([
            'prestataire_id' => $prestataire->id,
            'title' => 'Test Service'
        ]);

        $this->info("Created service: " . $service->title);

        // Check if notification was created
        $notifications = $clientUser->notifications;
        $serviceNotifications = $notifications->where('data.item_type', 'service')->count();
        $this->info("Number of service notifications for client: " . $serviceNotifications);

        if ($serviceNotifications > 0) {
            $notification = $notifications->where('data.item_type', 'service')->first();
            $this->info("Notification message: " . $notification->data['message']);
            $this->info("SUCCESS: Service notification system is working!");
        } else {
            $this->error("ERROR: No service notification was created");
        }

        // Test 2: Create new equipment
        $this->info("\n--- Testing Equipment Notification ---");
        $equipment = Equipment::factory()->create([
            'prestataire_id' => $prestataire->id,
            'name' => 'Test Equipment'
        ]);

        $this->info("Created equipment: " . $equipment->name);

        // Check if notification was created
        $notifications = $clientUser->fresh()->notifications;
        $equipmentNotifications = $notifications->where('data.item_type', 'equipment')->count();
        $this->info("Number of equipment notifications for client: " . $equipmentNotifications);

        if ($equipmentNotifications > 0) {
            $notification = $notifications->where('data.item_type', 'equipment')->first();
            $this->info("Notification message: " . $notification->data['message']);
            $this->info("SUCCESS: Equipment notification system is working!");
        } else {
            $this->error("ERROR: No equipment notification was created");
        }

        // Test 3: Create a new urgent sale
        $this->info("\n--- Testing Urgent Sale Notification ---");
        $urgentSale = UrgentSale::factory()->create([
            'prestataire_id' => $prestataire->id,
            'title' => 'Test Urgent Sale'
        ]);

        $this->info("Created urgent sale: " . $urgentSale->title);

        // Check if notification was created
        $notifications = $clientUser->fresh()->notifications;
        $urgentSaleNotifications = $notifications->where('data.item_type', 'urgent_sale')->count();
        $this->info("Number of urgent sale notifications for client: " . $urgentSaleNotifications);

        if ($urgentSaleNotifications > 0) {
            $notification = $notifications->where('data.item_type', 'urgent_sale')->first();
            $this->info("Notification message: " . $notification->data['message']);
            $this->info("SUCCESS: Urgent sale notification system is working!");
        } else {
            $this->error("ERROR: No urgent sale notification was created");
        }

        // Test 4: Create a new video
        $this->info("\n--- Testing Video Notification ---");
        $video = Video::factory()->create([
            'prestataire_id' => $prestataire->id,
            'title' => 'Test Video'
        ]);

        $this->info("Created video: " . $video->title);

        // Check if notification was created
        $notifications = $clientUser->fresh()->notifications;
        $videoNotifications = $notifications->where('data.item_type', 'video')->count();
        $this->info("Number of video notifications for client: " . $videoNotifications);

        if ($videoNotifications > 0) {
            $notification = $notifications->where('data.item_type', 'video')->first();
            $this->info("Notification message: " . $notification->data['message']);
            $this->info("SUCCESS: Video notification system is working!");
        } else {
            $this->error("ERROR: No video notification was created");
        }

        // Summary
        $totalNotifications = $clientUser->fresh()->notifications->count();
        $this->info("\n--- Summary ---");
        $this->info("Total notifications created: " . $totalNotifications);
        $this->info("Expected: 4");
        
        if ($totalNotifications >= 4) {
            $this->info("SUCCESS: All notification systems are working!");
        } else {
            $this->error("ERROR: Some notification systems are not working");
        }

        // Clean up
        $service->delete();
        $equipment->delete();
        $urgentSale->delete();
        $video->delete();
        $client->followedPrestataires()->detach($prestataire->id);
        $clientUser->delete();
        $prestataire->delete();
        $prestataireUser->delete();
        
        return 0;
    }
}