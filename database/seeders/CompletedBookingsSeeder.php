<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;

class CompletedBookingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all prestataires, clients and services
        $prestataires = Prestataire::all();
        $clients = Client::all();
        $services = Service::all();

        if ($prestataires->isEmpty() || $clients->isEmpty() || $services->isEmpty()) {
            $this->command->info('Please seed prestataires, clients, and services first.');
            return;
        }

        // Create 15 completed bookings with various dates in the past
        for ($i = 0; $i < 15; $i++) {
            $service = $services->random();
            $client = $clients->random();
            $prestataire = $service->prestataire;

            // Generate random dates in the past (between 1 and 60 days ago)
            $daysAgo = rand(1, 60);
            $start_datetime = Carbon::now()->subDays($daysAgo)->setTime(rand(8, 17), rand(0, 59));
            $end_datetime = $start_datetime->copy()->addHours(rand(1, 4));

            Booking::create([
                'prestataire_id' => $prestataire->id,
                'client_id' => $client->id,
                'service_id' => $service->id,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'status' => 'completed',
                'total_price' => $service->price,
                'completed_at' => $end_datetime, // Set completed_at to end_datetime
                'confirmed_at' => $start_datetime->copy()->subHours(rand(1, 24)), // Set confirmed_at to some time before start
            ]);
        }

        $this->command->info('15 completed bookings have been added to the database.');
    }
}