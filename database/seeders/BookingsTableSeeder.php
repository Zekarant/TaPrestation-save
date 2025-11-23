<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;

class BookingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prestataires = Prestataire::all();
        $clients = Client::all();
        $services = Service::all();

        if ($prestataires->isEmpty() || $clients->isEmpty() || $services->isEmpty()) {
            $this->command->info('Please seed prestataires, clients, and services first.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            $service = $services->random();
            $client = $clients->random();
            $prestataire = $service->prestataire;

            $start_datetime = Carbon::now()->addDays(rand(-10, 10))->addHours(rand(8, 17));
            $end_datetime = $start_datetime->copy()->addHours(rand(1, 3));

            Booking::create([
                'prestataire_id' => $prestataire->id,
                'client_id' => $client->id,
                'service_id' => $service->id,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'status' => ['pending', 'confirmed', 'cancelled'][array_rand(['pending', 'confirmed', 'cancelled'])],
                'total_price' => $service->price,
            ]);
        }
    }
}
