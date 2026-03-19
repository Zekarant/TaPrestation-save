<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Prestataire;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class EquipmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Créer le dossier pour les photos d'équipement s'il n'existe pas
        if (!Storage::disk('public')->exists('equipment_photos')) {
            Storage::disk('public')->makeDirectory('equipment_photos');
        }

        // Récupérer les prestataires et catégories
        $prestataires = Prestataire::all();
        $categories = Category::whereNotNull('parent_id')->get(); // Sous-catégories uniquement

        if ($prestataires->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Aucun prestataire ou catégorie trouvé. Veuillez d\'abord exécuter les seeders correspondants.');
            return;
        }

        // Données d'équipements d'exemple
        $equipmentData = [
            [
                'name' => 'Perceuse électrique Bosch',
                'description' => 'Perceuse électrique professionnelle avec mandrin auto-serrant. Idéale pour tous vos travaux de perçage dans le bois, le métal et la maçonnerie.',
                'daily_rate' => 15.00,
                'weekly_rate' => 80.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Perceuses'
            ],
            [
                'name' => 'Scie circulaire Makita',
                'description' => 'Scie circulaire puissante pour découpes précises. Lame de 190mm, guide laser intégré.',
                'daily_rate' => 25.00,
                'weekly_rate' => 140.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Scies'
            ],
            [
                'name' => 'Échafaudage mobile',
                'description' => 'Échafaudage mobile en aluminium, hauteur de travail 4m. Roues avec freins pour faciliter les déplacements.',
                'daily_rate' => 45.00,
                'weekly_rate' => 250.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Échafaudages'
            ],
            [
                'name' => 'Bétonnière 160L',
                'description' => 'Bétonnière électrique 160 litres, moteur 650W. Parfaite pour vos travaux de maçonnerie.',
                'daily_rate' => 35.00,
                'weekly_rate' => 200.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Bétonnières'
            ],
            [
                'name' => 'Tondeuse thermique Honda',
                'description' => 'Tondeuse thermique autopropulsée, largeur de coupe 53cm. Moteur Honda 4 temps.',
                'daily_rate' => 30.00,
                'weekly_rate' => 170.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Tondeuses'
            ],
            [
                'name' => 'Taille-haie électrique',
                'description' => 'Taille-haie électrique 600W, lame double action 60cm. Léger et maniable.',
                'daily_rate' => 20.00,
                'weekly_rate' => 110.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Taille-haies'
            ],
            [
                'name' => 'Nettoyeur haute pression Kärcher',
                'description' => 'Nettoyeur haute pression 140 bars, débit 8L/min. Avec accessoires et enrouleur.',
                'daily_rate' => 28.00,
                'weekly_rate' => 160.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Nettoyeurs haute pression'
            ],
            [
                'name' => 'Remorque basculante 750kg',
                'description' => 'Remorque basculante charge utile 750kg, dimensions plateau 200x125cm.',
                'daily_rate' => 40.00,
                'weekly_rate' => 220.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Remorques'
            ]
        ];

        // Créer les équipements
        foreach ($equipmentData as $data) {
            // Trouver la catégorie
            $category = $categories->where('name', $data['category_name'])->first();
            if (!$category) {
                $category = $categories->random();
            }

            // Créer une photo d'exemple (SVG)
            $photoContent = $this->generateEquipmentSVG($data['name']);
            $photoPath = 'equipment_photos/' . Str::slug($data['name']) . '.svg';
            Storage::disk('public')->put($photoPath, $photoContent);

            // Créer l'équipement
            $equipment = Equipment::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_per_day' => $data['daily_rate'],
                    'price_per_week' => $data['weekly_rate'],
                    'condition' => $data['condition'],
                    'status' => $data['availability_status'] === 'available' ? 'active' : 'inactive',
                    'main_photo' => $photoPath,
                    'photos' => [$photoPath], // Ajouter aussi dans le tableau photos
                    'prestataire_id' => $prestataires->random()->id,
                    'city' => 'Paris',
                    'postal_code' => '75001',
                    'country' => 'France',

                    'security_deposit' => $data['daily_rate'] * 5, // Caution = 5 jours de location
                    'minimum_rental_duration' => 1,
                    'maximum_rental_duration' => 30,
                    'is_available' => true
                ]
            );

            // Assigner la catégorie
            if ($category->parent_id) {
                // C'est une sous-catégorie
                $equipment->update([
                    'category_id' => $category->parent_id,
                    'subcategory_id' => $category->id
                ]);
            } else {
                // C'est une catégorie principale
                $equipment->update([
                    'category_id' => $category->id
                ]);
            }
        }

        $this->command->info('Équipements créés avec succès avec photos d\'exemple!');
    }

    /**
     * Génère une image SVG simple pour l'équipement
     */
    private function generateEquipmentSVG($name)
    {
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];
        $color = $colors[array_rand($colors)];
        
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
  <rect width="400" height="300" fill="#F3F4F6"/>
  <rect x="50" y="50" width="300" height="200" rx="10" fill="' . $color . '" opacity="0.8"/>
  <circle cx="200" cy="120" r="30" fill="white" opacity="0.9"/>
  <text x="200" y="200" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="white" font-weight="bold">' . substr($name, 0, 20) . '</text>
  <text x="200" y="220" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" fill="white">Équipement</text>
</svg>';
    }
}