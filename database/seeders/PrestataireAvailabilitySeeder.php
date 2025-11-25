<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prestataire;
use App\Models\PrestataireAvailability;

class PrestataireAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les prestataires approuvés
        $prestataires = Prestataire::where('is_approved', true)->get();
        
        foreach ($prestataires as $prestataire) {
            // Vérifier si le prestataire a déjà des disponibilités
            if ($prestataire->availabilities()->count() === 0) {
                // Créer des disponibilités par défaut pour chaque jour de la semaine
                $availabilities = [
                    // Lundi (1)
                    [
                        'prestataire_id' => $prestataire->id,
                        'day_of_week' => 1,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'slot_duration' => 60, // 1 heure
                        'break_start_time' => '12:00',
                        'break_end_time' => '13:00',
                        'is_active' => true,
                    ],
                    // Mardi (2)
                    [
                        'prestataire_id' => $prestataire->id,
                        'day_of_week' => 2,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'slot_duration' => 60,
                        'break_start_time' => '12:00',
                        'break_end_time' => '13:00',
                        'is_active' => true,
                    ],
                    // Mercredi (3)
                    [
                        'prestataire_id' => $prestataire->id,
                        'day_of_week' => 3,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'slot_duration' => 60,
                        'break_start_time' => '12:00',
                        'break_end_time' => '13:00',
                        'is_active' => true,
                    ],
                    // Jeudi (4)
                    [
                        'prestataire_id' => $prestataire->id,
                        'day_of_week' => 4,
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'slot_duration' => 60,
                        'break_start_time' => '12:00',
                        'break_end_time' => '13:00',
                        'is_active' => true,
                    ],
                    // Vendredi (5)
                    [
                        'prestataire_id' => $prestataire->id,
                        'day_of_week' => 5,
                        'start_time' => '09:00',
                        'end_time' => '16:00',
                        'slot_duration' => 60,
                        'break_start_time' => '12:00',
                        'break_end_time' => '13:00',
                        'is_active' => true,
                    ],
                    // Samedi (6) - horaires réduits
                    [
                        'prestataire_id' => $prestataire->id,
                        'day_of_week' => 6,
                        'start_time' => '10:00',
                        'end_time' => '14:00',
                        'slot_duration' => 60,
                        'break_start_time' => null,
                        'break_end_time' => null,
                        'is_active' => true,
                    ],
                    // Dimanche (0) - fermé
                    [
                        'prestataire_id' => $prestataire->id,
                        'day_of_week' => 0,
                        'start_time' => null,
                        'end_time' => null,
                        'slot_duration' => 60,
                        'break_start_time' => null,
                        'break_end_time' => null,
                        'is_active' => false,
                    ],
                ];
                
                foreach ($availabilities as $availability) {
                    PrestataireAvailability::create($availability);
                }
                
                $this->command->info("Disponibilités créées pour le prestataire: {$prestataire->company_name}");
            }
        }
    }
}