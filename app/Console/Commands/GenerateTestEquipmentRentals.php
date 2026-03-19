<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use App\Models\Client;
use App\Models\Prestataire;
use Carbon\Carbon;

class GenerateTestEquipmentRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment-rentals:generate-test-data {--count=10 : Number of test rentals to create} {--prestataire-id= : Specific prestataire ID to use} {--completed-percentage=30 : Percentage of rentals to mark as completed for revenue testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test equipment rentals for revenue calculation testing';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = $this->option('count');
        $prestataireId = $this->option('prestataire-id');
        $completedPercentage = $this->option('completed-percentage');

        // Get prestataires and equipment
        $prestataires = $prestataireId 
            ? Prestataire::where('id', $prestataireId)->get()
            : Prestataire::inRandomOrder()->limit(5)->get();

        if ($prestataires->isEmpty()) {
            $this->error('No prestataires found. Please create some prestataires first.');
            return 1;
        }

        $clients = Client::inRandomOrder()->limit(10)->get();
        if ($clients->isEmpty()) {
            $this->error('No clients found. Please create some clients first.');
            return 1;
        }

        $this->info("Generating {$count} test equipment rentals ({$completedPercentage}% completed for revenue testing)...");

        $createdCount = 0;
        $completedCount = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $prestataire = $prestataires->random();
            $client = $clients->random();
            
            // Get equipment for this prestataire
            $equipment = Equipment::where('prestataire_id', $prestataire->id)
                ->inRandomOrder()
                ->first();
                
            if (!$equipment) {
                // Create equipment if none exists for this prestataire
                $equipment = Equipment::factory()->create([
                    'prestataire_id' => $prestataire->id,
                    'name' => 'Test Equipment ' . uniqid(),
                    'price_per_day' => rand(50, 200),
                    'security_deposit' => rand(100, 500),
                ]);
            }

            // Generate random dates (some in the past for completed rentals, some in the future)
            $shouldComplete = (rand(1, 100) <= $completedPercentage);
            
            if ($shouldComplete) {
                // Create completed rentals in the past
                $startDate = Carbon::now()->subDays(rand(5, 60));
                $endDate = clone $startDate;
                $endDate->addDays(rand(1, 14));
            } else {
                // Create future rentals
                $startDate = Carbon::now()->addDays(rand(1, 30));
                $endDate = clone $startDate;
                $endDate->addDays(rand(1, 14));
            }

            // Create rental request
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

            // Determine status and completion date
            if ($shouldComplete) {
                $status = EquipmentRental::STATUS_COMPLETED;
                $completedAt = $endDate->copy()->addDays(rand(0, 3));
                $completedCount++;
            } else {
                // Randomly choose between confirmed and active status
                $status = (rand(0, 1) == 0) ? EquipmentRental::STATUS_CONFIRMED : EquipmentRental::STATUS_ACTIVE;
                $completedAt = null;
            }

            // Create rental
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
                'payment_status' => EquipmentRental::PAYMENT_FULL_PAID,
                'completed_at' => $completedAt,
                'pickup_address' => $equipment->address ?? 'Test Address'
            ]);

            $this->info("Created rental: {$rental->rental_number} (Status: {$status}, Amount: €{$rental->total_amount}" . ($completedAt ? ", Completed: {$completedAt->format('Y-m-d')}" : "") . ")");
            $createdCount++;
        }

        $this->info("Successfully created {$createdCount} test equipment rentals ({$completedCount} completed)!");
        return 0;
    }
}