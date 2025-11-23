<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;

class TestPrestataireBookingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the test prestataire
        $prestataireUser = User::where('email', 'prestataire1@example.com')->first();
        
        if (!$prestataireUser) {
            $this->command->info('Prestataire with email prestataire1@example.com not found. Please run UsersTableSeeder first.');
            return;
        }
        
        $prestataire = $prestataireUser->prestataire;
        
        if (!$prestataire) {
            $this->command->info('Prestataire profile not found for user prestataire1@example.com');
            return;
        }
        
        // Get or create a test client
        $clientUser = User::firstOrCreate([
            'email' => 'testclient@example.com'
        ], [
            'name' => 'Test Client',
            'email' => 'testclient@example.com',
            'password' => bcrypt('Password@123'),
            'role' => 'client',
        ]);
        
        $client = Client::firstOrCreate([
            'user_id' => $clientUser->id
        ], [
            'user_id' => $clientUser->id,
            'location' => 'Test Client Location',
        ]);
        
        // Get services for this prestataire
        $services = $prestataire->services;
        
        if ($services->isEmpty()) {
            $this->command->info('No services found for prestataire. Please run TestPrestataireSeeder first.');
            return;
        }
        
        // Create test bookings
        foreach (range(1, 5) as $index) {
            $service = $services->random();
            
            $startDate = now()->addDays(rand(1, 30));
            $endDate = $startDate->copy()->addHours(2); // Default to 2 hours
            
            $booking = Booking::firstOrCreate([
                'client_id' => $client->id,
                'prestataire_id' => $prestataire->id,
                'service_id' => $service->id,
                'start_datetime' => $startDate,
            ], [
                'client_id' => $client->id,
                'prestataire_id' => $prestataire->id,
                'service_id' => $service->id,
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
                'status' => ['pending', 'confirmed', 'completed', 'cancelled'][array_rand(['pending', 'confirmed', 'completed', 'cancelled'])],
                'total_price' => $service->price,
                'client_notes' => "Test booking #{$index}",
                'booking_number' => 'BK' . now()->format('Ymd') . rand(1000, 9999),
            ]);
            
            // Create a review for completed bookings
            if ($booking->status === 'completed' && rand(0, 1)) {
                Review::firstOrCreate([
                    'booking_id' => $booking->id,
                ], [
                    'booking_id' => $booking->id,
                    'client_id' => $client->id,
                    'prestataire_id' => $prestataire->id,
                    'rating' => rand(3, 5),
                    'comment' => "This is a test review for booking #{$index}. Great service!",
                    'is_approved' => true,
                ]);
            }
        }
        
        // Update prestataire stats
        $prestataire->update([
            'total_reviews' => $prestataire->reviews()->count(),
            'rating_average' => $prestataire->reviews()->avg('rating') ?? 0,
            'total_projects' => $prestataire->bookings()->count(),
        ]);
        
        $this->command->info('Test prestataire bookings and reviews seeded successfully!');
    }
}