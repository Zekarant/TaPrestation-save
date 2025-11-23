<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;

class EnhancedBookingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer les services, clients et prestataires existants
        $services = Service::where('status', 'active')->get();
        $clients = Client::all();
        $prestataires = Prestataire::all();

        if ($services->isEmpty() || $clients->isEmpty() || $prestataires->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders pour Service, Client et Prestataire.');
            return;
        }

        $this->command->info('Création des réservations de test...');

        // Créer des réservations avec différents statuts et périodes
        foreach ($services->take(10) as $service) {
            $client = $clients->random();
            $prestataire = $prestataires->where('id', $service->prestataire_id)->first() ?? $prestataires->random();

            // Réservation confirmée (future)
            $this->createBooking($service, $client, $prestataire, 'confirmed', 
                Carbon::now()->addDays(rand(5, 15)), 
                Carbon::now()->addDays(rand(16, 25))
            );

            // Réservation active (en cours)
            $this->createBooking($service, $client, $prestataire, 'confirmed', 
                Carbon::now()->subDays(rand(1, 3)), 
                Carbon::now()->addDays(rand(2, 7))
            );

            // Réservation terminée (passée)
            $this->createBooking($service, $client, $prestataire, 'completed', 
                Carbon::now()->subDays(rand(20, 30)), 
                Carbon::now()->subDays(rand(10, 15))
            );

            // Réservation annulée
            $this->createBooking($service, $client, $prestataire, 'cancelled', 
                Carbon::now()->addDays(rand(10, 20)), 
                Carbon::now()->addDays(rand(21, 30))
            );
        }

        // Créer des réservations supplémentaires pour plus de diversité
        for ($i = 0; $i < 15; $i++) {
            $service = $services->random();
            $client = $clients->random();
            $prestataire = $prestataires->where('id', $service->prestataire_id)->first() ?? $prestataires->random();

            $statuses = ['confirmed', 'completed', 'cancelled'];
            $status = $statuses[array_rand($statuses)];

            $startDate = Carbon::now()->subDays(rand(0, 30))->addDays(rand(0, 30));
            $endDate = $startDate->copy()->addDays(rand(1, 14));

            $this->createBooking($service, $client, $prestataire, $status, $startDate, $endDate);
        }

        $this->command->info('Réservations créées avec succès!');
    }

    /**
     * Créer une réservation
     */
    private function createBooking(Service $service, Client $client, Prestataire $prestataire, string $status, Carbon $startDate, Carbon $endDate)
    {
        $booking = Booking::create([
            'service_id' => $service->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'booking_number' => 'BOOK-' . date('Y') . '-' . str_pad(Booking::count() + 1, 6, '0', STR_PAD_LEFT),
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'total_price' => ($service->price ?? 100) * ($startDate->diffInDays($endDate) + 1),
            'status' => $status,
            'client_notes' => 'Réservation créée par seeder'
        ]);

        $this->command->info("Réservation créée: {$booking->booking_number} - Status: {$status} - Dates: {$startDate->format('d/m/Y')} au {$endDate->format('d/m/Y')}");
    }
}