<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use App\Models\Client;
use App\Models\Prestataire;
use Carbon\Carbon;

class EquipmentRentalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer les équipements, clients et prestataires existants
        $equipments = Equipment::where('status', 'active')->get();
        $clients = Client::all();
        $prestataires = Prestataire::all();

        if ($equipments->isEmpty() || $clients->isEmpty() || $prestataires->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders pour Equipment, Client et Prestataire.');
            return;
        }

        $this->command->info('Création des locations d\'équipement de test...');

        // Créer des locations confirmées et actives pour tester le calendrier
        foreach ($equipments->take(5) as $equipment) {
            $client = $clients->random();
            $prestataire = $prestataires->where('id', $equipment->prestataire_id)->first() ?? $prestataires->random();

            // Location confirmée (future)
            $this->createRental($equipment, $client, $prestataire, 'confirmed', 
                Carbon::now()->addDays(rand(5, 15)), 
                Carbon::now()->addDays(rand(16, 25))
            );

            // Location active (en cours)
            $this->createRental($equipment, $client, $prestataire, 'in_use', 
                Carbon::now()->subDays(rand(1, 3)), 
                Carbon::now()->addDays(rand(2, 7))
            );

            // Location confirmée (dans le futur proche)
            $this->createRental($equipment, $client, $prestataire, 'confirmed', 
                Carbon::now()->addDays(rand(30, 45)), 
                Carbon::now()->addDays(rand(46, 60))
            );
        }

        $this->command->info('Locations d\'équipement créées avec succès!');
    }

    /**
     * Créer une location d'équipement
     */
    private function createRental(Equipment $equipment, Client $client, Prestataire $prestataire, string $status, Carbon $startDate, Carbon $endDate)
    {
        // Créer d'abord une demande de location
        $rentalRequest = EquipmentRentalRequest::create([
            'equipment_id' => $equipment->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'request_number' => 'REQ-' . strtoupper(uniqid()),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $startDate->diffInDays($endDate) + 1,
            'unit_price' => $equipment->price_per_day,
            'total_amount' => $equipment->price_per_day * ($startDate->diffInDays($endDate) + 1),
            'final_amount' => $equipment->price_per_day * ($startDate->diffInDays($endDate) + 1),
            'status' => 'accepted'
        ]);

        // Créer la location
        $rental = EquipmentRental::create([
            'rental_request_id' => $rentalRequest->id,
            'equipment_id' => $equipment->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'rental_number' => 'LOC-' . date('Y') . '-' . str_pad(EquipmentRental::count() + 1, 6, '0', STR_PAD_LEFT),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'planned_duration_days' => $startDate->diffInDays($endDate) + 1,
            'unit_price' => $equipment->price_per_day,
            'base_amount' => $equipment->price_per_day * ($startDate->diffInDays($endDate) + 1),
            'security_deposit' => $equipment->security_deposit ?? ($equipment->price_per_day * 2),
            'total_amount' => $equipment->price_per_day * ($startDate->diffInDays($endDate) + 1),
            'final_amount' => $equipment->price_per_day * ($startDate->diffInDays($endDate) + 1),
            'status' => $status,
            'payment_status' => 'pending',

            'pickup_address' => $equipment->address ?? 'Adresse équipement'
        ]);

        $this->command->info("Location créée: {$rental->rental_number} - Status: {$status} - Dates: {$startDate->format('d/m/Y')} au {$endDate->format('d/m/Y')}");
    }
}