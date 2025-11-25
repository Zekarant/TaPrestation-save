<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\Category;
use App\Models\PrestataireAvailability;
use Illuminate\Support\Facades\Hash;

class TestPrestataireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the existing prestataire user or create if not exists
        $user = User::firstOrCreate([
            'email' => 'prestataire1@example.com'
        ], [
            'name' => 'Prestataire Test',
            'email' => 'prestataire1@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'prestataire',
        ]);

        // Get or create the prestataire profile
        $prestataire = Prestataire::firstOrCreate([
            'user_id' => $user->id
        ], [
            'user_id' => $user->id,
            'company_name' => 'Test Company',
            'description' => 'Test prestataire for development and testing purposes.',
            'phone' => '+33123456789',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'postal_code' => '75001',
            'country' => 'France',
            'service_radius_km' => 50,
            'website' => 'https://test-prestataire.com',
            'years_experience' => 5,
            'is_approved' => true,
            'is_verified' => true,
            'approved_at' => now(),
            'last_active_at' => now(),
        ]);

        // Create test services for this prestataire
        $categories = Category::limit(5)->get();
        
        if ($categories->count() > 0) {
            foreach (range(1, 3) as $index) {
                $service = Service::firstOrCreate([
                    'prestataire_id' => $prestataire->id,
                    'title' => "Test Service {$index}",
                ], [
                    'prestataire_id' => $prestataire->id,
                    'title' => "Test Service {$index}",
                    'description' => "This is a test service #{$index} for development purposes.",
                    'price' => rand(50, 200),
                ]);
                
                // Attach a random category to the service
                $category = $categories->random();
                $service->categories()->syncWithoutDetaching([$category->id]);
            }
        }

        // Create test availability for this prestataire
        // Monday to Friday, 9AM to 5PM
        foreach (range(1, 5) as $day) {
            PrestataireAvailability::firstOrCreate([
                'prestataire_id' => $prestataire->id,
                'day_of_week' => $day,
            ], [
                'prestataire_id' => $prestataire->id,
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_active' => true,
            ]);
        }

        // Weekend availability (limited)
        PrestataireAvailability::firstOrCreate([
            'prestataire_id' => $prestataire->id,
            'day_of_week' => 6, // Saturday
        ], [
            'prestataire_id' => $prestataire->id,
            'day_of_week' => 6,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'is_active' => true,
        ]);

        $this->command->info('Test prestataire data seeded successfully!');
    }
}