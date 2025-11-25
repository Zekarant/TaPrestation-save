<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Prestataire;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class EnhancedEquipmentTableSeeder extends Seeder
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

        // Données d'équipements d'exemple - Élargi avec plus de variétés
        $equipmentData = [
            // Outils électriques
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
                'name' => 'Ponceuse excentrique Festool',
                'description' => 'Ponceuse excentrique professionnelle pour finitions parfaites sur bois et peinture.',
                'daily_rate' => 30.00,
                'weekly_rate' => 160.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Ponceuses'
            ],
            [
                'name' => 'Visseuse à chocs DeWalt',
                'description' => 'Visseuse à chocs puissante pour vissages rapides et efficaces. Couple élevé et autonomie optimale.',
                'daily_rate' => 20.00,
                'weekly_rate' => 110.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Visseuses'
            ],
            
            // Échafaudages et plateformes
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
                'name' => 'Plateforme de travail élévatrice',
                'description' => 'Plateforme de travail à hauteur variable jusqu\'à 8m. Stabilité optimale et sécurité renforcée.',
                'daily_rate' => 120.00,
                'weekly_rate' => 700.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Échafaudages'
            ],
            
            // Bétonnières et malaxeurs
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
                'name' => 'Malaxeur industriel 500L',
                'description' => 'Malaxeur industriel 500 litres pour gros volumes de béton. Moteur thermique puissant.',
                'daily_rate' => 85.00,
                'weekly_rate' => 480.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Bétonnières'
            ],
            
            // Jardinage et extérieur
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
                'name' => 'Tronçonneuse électrique Stihl',
                'description' => 'Tronçonneuse électrique professionnelle pour découpes précises. Faible niveau sonore.',
                'daily_rate' => 35.00,
                'weekly_rate' => 190.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Tronçonneuses'
            ],
            [
                'name' => 'Souffleur thermique Kawasaki',
                'description' => 'Souffleur thermique puissant pour nettoyage de feuilles et débris. 230 km/h de vitesse d\'air.',
                'daily_rate' => 25.00,
                'weekly_rate' => 140.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Souffleurs'
            ],
            
            // Nettoyage et entretien
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
                'name' => 'Aspirateur industriel Nilfisk',
                'description' => 'Aspirateur industriel pour poussières et liquides. Filtre HEPA et grand volume de récupération.',
                'daily_rate' => 22.00,
                'weekly_rate' => 120.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Aspirateurs industriels'
            ],
            
            // Transport et manutention
            [
                'name' => 'Remorque basculante 750kg',
                'description' => 'Remorque basculante charge utile 750kg, dimensions plateau 200x125cm.',
                'daily_rate' => 40.00,
                'weekly_rate' => 220.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Remorques'
            ],
            [
                'name' => 'Diable à plateaux 300kg',
                'description' => 'Diable à plateaux pour transport de charges lourdes. Capacité 300kg, roues pneumatiques.',
                'daily_rate' => 15.00,
                'weekly_rate' => 80.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Diables et sangles'
            ],
            
            // Événementiel
            [
                'name' => 'Tente de réception 4x6m',
                'description' => 'Tente de réception blanche 4x6m avec toit imperméable. Idéale pour mariages et événements.',
                'daily_rate' => 90.00,
                'weekly_rate' => 500.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Tentes et barnums'
            ],
            [
                'name' => 'Table pliante 180x75cm',
                'description' => 'Table pliante blanche pour événements. Structure en aluminium, plateau stratifié.',
                'daily_rate' => 8.00,
                'weekly_rate' => 45.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Tables et chaises'
            ],
            
            // Audiovisuel
            [
                'name' => 'Enceinte Bluetooth JBL',
                'description' => 'Enceinte Bluetooth JBL Flip 5 pour événements. Son puissant et autonomie 12h.',
                'daily_rate' => 18.00,
                'weekly_rate' => 100.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Sonorisation'
            ],
            [
                'name' => 'Projecteur LED 1000 lumens',
                'description' => 'Projecteur LED professionnel 1000 lumens. Réglage d\'angle et télécommande incluse.',
                'daily_rate' => 25.00,
                'weekly_rate' => 140.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Éclairage'
            ],
            
            // Ajout d'équipements supplémentaires pour plus de diversité
            [
                'name' => 'Tronçonneuse thermique Husqvarna',
                'description' => 'Tronçonneuse thermique professionnelle 55cc. Barre 45cm, démarrage facile.',
                'daily_rate' => 45.00,
                'weekly_rate' => 240.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Tronçonneuses'
            ],
            [
                'name' => 'Décapeur thermique Einhell',
                'description' => 'Décapeur thermique 2000W pour décollage de peinture et revêtements.',
                'daily_rate' => 22.00,
                'weekly_rate' => 120.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Décapeurs'
            ],
            [
                'name' => 'Meuleuse angulaire Bosch',
                'description' => 'Meuleuse angulaire 230mm, 2000W. Pour meulage, découpe et polissage.',
                'daily_rate' => 28.00,
                'weekly_rate' => 150.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Meuleuses'
            ],
            [
                'name' => 'Compresseur 500L Fiac',
                'description' => 'Compresseur à vis 500L, 3000W. Pour outillage pneumatique et gonflage.',
                'daily_rate' => 42.00,
                'weekly_rate' => 230.00,
                'condition' => 'good',
                'availability_status' => 'available',
                'category_name' => 'Compresseurs'
            ],
            [
                'name' => 'Scie sauteuse Bosch',
                'description' => 'Scie sauteuse 700W avec système de contrôle orbital. Pour découpes courbes.',
                'daily_rate' => 18.00,
                'weekly_rate' => 95.00,
                'condition' => 'excellent',
                'availability_status' => 'available',
                'category_name' => 'Scies'
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
                    'city' => ['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Toulouse'][array_rand(['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Toulouse'])],
                    'postal_code' => ['75001', '69001', '13001', '33000', '31000'][array_rand(['75001', '69001', '13001', '33000', '31000'])],
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
