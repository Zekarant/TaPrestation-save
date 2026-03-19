<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UrgentSale;
use App\Models\Prestataire;
use App\Models\Category;
use Illuminate\Support\Str;

class EnhancedUrgentSalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer les prestataires et catégories
        $prestataires = Prestataire::all();
        $categories = Category::whereNotNull('parent_id')->get(); // Sous-catégories uniquement

        if ($prestataires->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Aucun prestataire ou catégorie trouvé. Veuillez d\'abord exécuter les seeders correspondants.');
            return;
        }

        // Données d'annonces urgentes plus diversifiées
        $urgentSalesData = [
            // Véhicules
            [
                'title' => 'Vélo de montagne Trek',
                'description' => 'Vélo de montagne Trek Fuel EX 9.9 en excellent état. Suspension Fox Factory, transmission Shimano XT. Utilisation occasionnelle, entretien régulier.',
                'price' => 2500.00,
                'condition' => 'excellent',
                'category_name' => 'Vélo / Scooter',
                'city' => 'Paris',
                'postal_code' => '75001',
                'phone' => '06 12 34 56 78'
            ],
            [
                'title' => 'Voiture citadine Renault Clio',
                'description' => 'Renault Clio 4 Energy Intens 5 portes, année 2020, 35 000 km. Entretien complet fait, contrôle technique OK. Prix négociable pour vente rapide.',
                'price' => 12500.00,
                'condition' => 'good',
                'category_name' => 'Citadine',
                'city' => 'Lyon',
                'postal_code' => '69001',
                'phone' => '06 23 45 67 89'
            ],
            [
                'title' => 'SUV Peugeot 3008',
                'description' => 'Peugeot 3008 GT Line 1.5 BlueHDi 130 ch, année 2019, 45 000 km. Jantes 18", toit panoramique, système de navigation. Très bon état général.',
                'price' => 18900.00,
                'condition' => 'excellent',
                'category_name' => 'SUV / 4x4',
                'city' => 'Marseille',
                'postal_code' => '13001',
                'phone' => '06 34 56 78 90'
            ],
            
            // Électronique
            [
                'title' => 'Smartphone iPhone 13 Pro',
                'description' => 'iPhone 13 Pro 256 Go, état neuf, boîte d\'origine complète. Couleur Graphite. Garantie Apple encore valide. Changement de téléphone.',
                'price' => 850.00,
                'condition' => 'excellent',
                'category_name' => 'Smartphones',
                'city' => 'Bordeaux',
                'postal_code' => '33000',
                'phone' => '06 45 67 89 01'
            ],
            [
                'title' => 'Ordinateur portable MacBook Pro',
                'description' => 'MacBook Pro 13" M1 2020, 16 Go RAM, 512 Go SSD. Utilisé pour le travail à domicile, parfait état. Vendu avec chargeur d\'origine.',
                'price' => 1200.00,
                'condition' => 'good',
                'category_name' => 'Ordinateurs portables',
                'city' => 'Paris',
                'postal_code' => '75002',
                'phone' => '06 56 78 90 12'
            ],
            [
                'title' => 'Téléviseur Samsung 55 pouces',
                'description' => 'Téléviseur Samsung QLED 4K 55 pouces, Smart TV avec toutes les applications. Montage mural inclus. Neuf sous emballage plastique.',
                'price' => 750.00,
                'condition' => 'excellent',
                'category_name' => 'Téléviseurs',
                'city' => 'Lyon',
                'postal_code' => '69002',
                'phone' => '06 67 89 01 23'
            ],
            
            // Meubles
            [
                'title' => 'Canapé 3 places en cuir',
                'description' => 'Canapé 3 places en véritable cuir noir, style contemporain. Très bon état, nettoyé professionnellement. Dimensions : 200 x 90 x 85 cm.',
                'price' => 450.00,
                'condition' => 'good',
                'category_name' => 'Canapés',
                'city' => 'Marseille',
                'postal_code' => '13002',
                'phone' => '06 78 90 12 34'
            ],
            [
                'title' => 'Table à manger en chêne',
                'description' => 'Table à manger extensible en chêne massif, 6 places. Avec rallonges, pieds en métal noir. Style industriel. Parfaite pour les grandes tablées.',
                'price' => 380.00,
                'condition' => 'excellent',
                'category_name' => 'Tables',
                'city' => 'Bordeaux',
                'postal_code' => '33001',
                'phone' => '06 89 01 23 45'
            ],
            
            // Vêtements
            [
                'title' => 'Veste en cuir moto',
                'description' => 'Veste en cuir moto taille M, marque Alpinestars. Portée quelques fois seulement, entretien fait. Coupe ajustée, fermeture éclair YKK. Noir mat.',
                'price' => 180.00,
                'condition' => 'excellent',
                'category_name' => 'Vêtements pour hommes',
                'city' => 'Paris',
                'postal_code' => '75003',
                'phone' => '06 90 12 34 56'
            ],
            [
                'title' => 'Robe de soirée',
                'description' => 'Robe de soirée taille 38, marque Zara. Robe bustier en tulle rose poudré avec application de paillettes. Portée une seule fois à un mariage.',
                'price' => 45.00,
                'condition' => 'excellent',
                'category_name' => 'Vêtements pour femmes',
                'city' => 'Lyon',
                'postal_code' => '69003',
                'phone' => '06 01 23 45 67'
            ],
            
            // Loisirs
            [
                'title' => 'Tente de camping 4 personnes',
                'description' => 'Tente de camping 4 saisons pour 4 personnes, marque Quechua. Imperméable, avec vestibule, moustiquaire. Utilisée 3 fois seulement. Parfaite pour weekend.',
                'price' => 120.00,
                'condition' => 'good',
                'category_name' => 'Camping',
                'city' => 'Marseille',
                'postal_code' => '13003',
                'phone' => '06 12 34 56 78'
            ],
            [
                'title' => 'Guitare électrique Fender',
                'description' => 'Guitare électrique Fender Stratocaster, corps en aulne, manche en érable. Micros Seymour Duncan. Étui rigide inclus. Entretien régulier, accord parfaite.',
                'price' => 650.00,
                'condition' => 'excellent',
                'category_name' => 'Instruments de musique',
                'city' => 'Bordeaux',
                'postal_code' => '33002',
                'phone' => '06 23 45 67 89'
            ],
            
            // Équipements de sport
            [
                'title' => 'VTT Specialized',
                'description' => 'VTT Specialized Rockhopper Expert 29, taille M. Suspension RockShox, transmission Shimano Deore. Entretien complet récent. Roues neuves.',
                'price' => 1800.00,
                'condition' => 'good',
                'category_name' => 'Vélo / Scooter',
                'city' => 'Paris',
                'postal_code' => '75004',
                'phone' => '06 34 56 78 90'
            ],
            [
                'title' => 'Raquette de tennis Wilson',
                'description' => 'Raquette de tennis Wilson Pro Staff RF97 Autograph, signature Roger Federer. Taille adulte, cordage Luxilon ALU Power Rough. Utilisée peu, en parfait état.',
                'price' => 220.00,
                'condition' => 'excellent',
                'category_name' => 'Tennis',
                'city' => 'Lyon',
                'postal_code' => '69004',
                'phone' => '06 45 67 89 01'
            ],
            
            // Matériel professionnel
            [
                'title' => 'Perceuse sans fil Bosch',
                'description' => 'Perceuse-visseuse sans fil Bosch Professional 18V, 2 batteries lithium-ion. Boîte de rangement incluse. Utilisée pour bricolage maison, peu.',
                'price' => 110.00,
                'condition' => 'good',
                'category_name' => 'Outils électriques',
                'city' => 'Marseille',
                'postal_code' => '13004',
                'phone' => '06 56 78 90 12'
            ],
            [
                'title' => 'Scie circulaire Makita',
                'description' => 'Scie circulaire Makita 2000W avec guide laser. Lame de 190mm, protection anti-rebond. Utilisée pour projets de rénovation. État très bon.',
                'price' => 180.00,
                'condition' => 'good',
                'category_name' => 'Scies',
                'city' => 'Bordeaux',
                'postal_code' => '33003',
                'phone' => '06 67 89 01 23'
            ],
            
            // Animaux
            [
                'title' => 'Aquarium complet 200L',
                'description' => 'Aquarium complet 200 litres avec filtration, éclairage LED, chauffage. Idéal pour débutants. Ensemble comprenant décors, substrat, poissons.',
                'price' => 350.00,
                'condition' => 'good',
                'category_name' => 'Poissons',
                'city' => 'Paris',
                'postal_code' => '75005',
                'phone' => '06 78 90 12 34'
            ],
            [
                'title' => 'Cage à oiseaux',
                'description' => 'Grande cage à oiseaux en bois et métal, dimensions 120x60x180cm. Avec perchoirs, mangeoires, jouets. Parfaite pour perroquets ou perruches.',
                'price' => 180.00,
                'condition' => 'excellent',
                'category_name' => 'Oiseaux',
                'city' => 'Lyon',
                'postal_code' => '69005',
                'phone' => '06 89 01 23 45'
            ],
            
            // Immobilier
            [
                'title' => 'Chambre à louer',
                'description' => 'Chambre meublée à louer dans appartement 3 pièces. Quartier Montmartre, proche métro. Inclus : lit, bureau, chaise, placard. Charges comprises.',
                'price' => 650.00,
                'condition' => 'good',
                'category_name' => 'Location de chambres',
                'city' => 'Paris',
                'postal_code' => '75018'
            ],
            [
                'title' => 'Colocation studio',
                'description' => 'Studio à partager avec une personne. Quartier Latin, proche universités. Inclus : meubles, électroménager, internet. Charges comprises.',
                'price' => 450.00,
                'condition' => 'fair',
                'category_name' => 'Colocations',
                'city' => 'Paris',
                'postal_code' => '75005'
            ]
        ];

        // Créer les annonces urgentes
        foreach ($urgentSalesData as $data) {
            // Trouver la catégorie
            $category = $categories->where('name', $data['category_name'])->first();
            if (!$category) {
                $category = $categories->random();
            }

            // Sélectionner un prestataire aléatoire
            $prestataire = $prestataires->random();

            // Créer l'annonce urgente
            $urgentSale = UrgentSale::firstOrCreate(
                ['title' => $data['title'], 'prestataire_id' => $prestataire->id],
                [
                    'prestataire_id' => $prestataire->id,
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'condition' => $data['condition'],
                    'location' => $data['city'] . ', ' . $data['postal_code'] . ', France',
                    'views_count' => rand(0, 100),
                    'contact_count' => rand(0, 20)
                ]
            );

            // Assigner la catégorie
            if ($category) {
                $urgentSale->update([
                    'category_id' => $category->id
                ]);
            }
        }

        $this->command->info('Annonces urgentes créées avec succès!');
    }
}