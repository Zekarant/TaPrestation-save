<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Models\Category;
use Illuminate\Support\Str;

class ApprovedPrestataireSeeder extends Seeder
{
    /**
     * Run the database seeds to create 15 services, 15 equipment items, 
     * and 15 urgent sales for the approved prestataire.
     *
     * @return void
     */
    public function run()
    {
        // Find the approved prestataire (Haytham)
        $prestataireUser = User::where('email', 'Haythamprestataire@gmail.com')->first();
        
        if (!$prestataireUser || !$prestataireUser->prestataire) {
            $this->command->error('Approved prestataire with email Haythamprestataire@gmail.com not found!');
            return;
        }
        
        $prestataire = $prestataireUser->prestataire;
        
        // Get categories for services and equipment
        $categories = Category::whereNotNull('parent_id')->get(); // Subcategories only
        
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run category seeders first.');
            return;
        }
        
        // Create 15 services
        $this->createServices($prestataire, $categories);
        
        // Create 15 equipment items
        $this->createEquipment($prestataire, $categories);
        
        // Create 15 urgent sales
        $this->createUrgentSales($prestataire, $categories);
        
        $this->command->info('Successfully created 15 services, 15 equipment items, and 15 urgent sales for the approved prestataire!');
        $this->command->info("Services created: " . $prestataire->services()->count());
        $this->command->info("Equipment created: " . $prestataire->equipments()->count());
        $this->command->info("Urgent Sales created: " . $prestataire->urgentSales()->count());
    }
    
    /**
     * Create 15 services for the prestataire
     */
    private function createServices($prestataire, $categories)
    {
        $serviceData = [
            [
                'title' => 'Développement d\'application web Laravel',
                'description' => 'Création d\'applications web sur mesure avec le framework Laravel. Architecture MVC, sécurité, performance et maintenabilité.',
                'price' => 1500.00,
                'delivery_time' => '2-3 semaines',
                'status' => 'active',
                'category_name' => 'Développement Web'
            ],
            [
                'title' => 'Développement mobile React Native',
                'description' => 'Applications mobiles cross-platform pour iOS et Android avec React Native. Interface utilisateur fluide et performante.',
                'price' => 2500.00,
                'delivery_time' => '4-6 semaines',
                'status' => 'active',
                'category_name' => 'Développement Mobile'
            ],
            [
                'title' => 'Création de site e-commerce',
                'description' => 'Boutique en ligne complète avec système de paiement, gestion des stocks, dashboard administrateur et SEO optimisé.',
                'price' => 3000.00,
                'delivery_time' => '4-5 semaines',
                'status' => 'active',
                'category_name' => 'E-commerce'
            ],
            [
                'title' => 'Refonte de site web existant',
                'description' => 'Modernisation de sites web existants avec technologies modernes, responsive design et amélioration des performances.',
                'price' => 1200.00,
                'delivery_time' => '2-3 semaines',
                'status' => 'active',
                'category_name' => 'Développement Web'
            ],
            [
                'title' => 'API RESTful personnalisée',
                'description' => 'Développement d\'API RESTful sécurisées avec Laravel. Documentation Swagger incluse, authentification JWT ou OAuth.',
                'price' => 1800.00,
                'delivery_time' => '3-4 semaines',
                'status' => 'active',
                'category_name' => 'Développement Backend'
            ],
            [
                'title' => 'Intégration de maquettes Figma',
                'description' => 'Transformation de maquettes Figma en code HTML/CSS/JavaScript responsive. Respect des spécifications de design.',
                'price' => 500.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'Intégration Web'
            ],
            [
                'title' => 'Optimisation SEO et performance',
                'description' => 'Audit complet de votre site web et optimisations pour améliorer le référencement naturel et la vitesse de chargement.',
                'price' => 800.00,
                'delivery_time' => '1-2 semaines',
                'status' => 'active',
                'category_name' => 'SEO'
            ],
            [
                'title' => 'Développement d\'application Vue.js',
                'description' => 'Applications web modernes avec Vue.js 3, Composition API, Vuex et Router. Interface utilisateur interactive et performante.',
                'price' => 2000.00,
                'delivery_time' => '3-4 semaines',
                'status' => 'active',
                'category_name' => 'Développement Frontend'
            ],
            [
                'title' => 'Migration de base de données',
                'description' => 'Migration de données entre différents systèmes de gestion de bases de données. Scripts personnalisés et validation des données.',
                'price' => 1000.00,
                'delivery_time' => '1-2 semaines',
                'status' => 'active',
                'category_name' => 'Administration de bases de données'
            ],
            [
                'title' => 'Développement de plugin WordPress',
                'description' => 'Plugins WordPress personnalisés selon vos besoins spécifiques. Respect des bonnes pratiques de développement WordPress.',
                'price' => 900.00,
                'delivery_time' => '2-3 semaines',
                'status' => 'active',
                'category_name' => 'WordPress'
            ],
            [
                'title' => 'Audit de sécurité web',
                'description' => 'Audit de sécurité complet de vos applications web. Identification des vulnérabilités et recommandations de correction.',
                'price' => 1200.00,
                'delivery_time' => '1-2 semaines',
                'status' => 'active',
                'category_name' => 'Sécurité Web'
            ],
            [
                'title' => 'Formation développement Laravel',
                'description' => 'Formation personnalisée en développement Laravel pour votre équipe. Sessions pratiques avec exemples réels.',
                'price' => 1500.00,
                'delivery_time' => '2-3 semaines',
                'status' => 'active',
                'category_name' => 'Formation'
            ],
            [
                'title' => 'Développement d\'application Node.js',
                'description' => 'Applications backend avec Node.js, Express et MongoDB. APIs RESTful, WebSockets, microservices.',
                'price' => 2200.00,
                'delivery_time' => '3-4 semaines',
                'status' => 'active',
                'category_name' => 'Développement Backend'
            ],
            [
                'title' => 'Création de dashboard administrateur',
                'description' => 'Interface d\'administration complète avec Laravel Nova ou Backpack. Gestion des utilisateurs, permissions et données.',
                'price' => 1800.00,
                'delivery_time' => '3-4 semaines',
                'status' => 'active',
                'category_name' => 'Développement Backend'
            ],
            [
                'title' => 'Intégration de système de paiement',
                'description' => 'Intégration de passerelles de paiement sécurisées (Stripe, PayPal, etc.) dans vos applications web ou mobiles.',
                'price' => 700.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'E-commerce'
            ]
        ];
        
        foreach ($serviceData as $data) {
            // Find the category
            $category = $categories->where('name', $data['category_name'])->first();
            if (!$category) {
                $category = $categories->random();
            }
            
            // Create the service
            $service = Service::firstOrCreate(
                ['title' => $data['title'], 'prestataire_id' => $prestataire->id],
                [
                    'prestataire_id' => $prestataire->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'delivery_time' => $data['delivery_time'],
                    'status' => $data['status'],
                    'city' => $prestataire->city,
                    'postal_code' => $prestataire->postal_code,
                    'address' => $prestataire->address
                ]
            );
            
            // Assign the category
            if ($category) {
                $service->categories()->syncWithoutDetaching([$category->id]);
            }
        }
        
        $this->command->info('15 services created successfully!');
    }
    
    /**
     * Create 15 equipment items for the prestataire
     */
    private function createEquipment($prestataire, $categories)
    {
        $equipmentData = [
            [
                'name' => 'MacBook Pro 16" M2 Pro',
                'description' => 'MacBook Pro 16 pouces avec puce M2 Pro, 32GB RAM, 1TB SSD. Parfait pour le développement et le design. Excellent état.',
                'daily_rate' => 80.00,
                'weekly_rate' => 500.00,
                'condition' => 'excellent',
                'category_name' => 'Ordinateurs portables'
            ],
            [
                'name' => 'Station de travail Dell Precision',
                'description' => 'Station de travail haute performance Dell Precision 7760. Intel i9, 64GB RAM, RTX A4000. Idéale pour le rendu 3D et développement.',
                'daily_rate' => 120.00,
                'weekly_rate' => 750.00,
                'condition' => 'good',
                'category_name' => 'Ordinateurs de bureau'
            ],
            [
                'name' => 'Écran 4K Dell UltraSharp 32"',
                'description' => 'Moniteur professionnel 4K 32 pouces Dell UltraSharp. Calibrage colorimétrique précis, parfait pour le design et développement.',
                'daily_rate' => 25.00,
                'weekly_rate' => 150.00,
                'condition' => 'good',
                'category_name' => 'Écrans et moniteurs'
            ],
            [
                'name' => 'iPad Pro 12.9" avec Apple Pencil',
                'description' => 'iPad Pro 12.9 pouces 6ème génération avec Apple Pencil 2. Parfait pour le design graphique et la prise de notes.',
                'daily_rate' => 40.00,
                'weekly_rate' => 250.00,
                'condition' => 'excellent',
                'category_name' => 'Tablettes'
            ],
            [
                'name' => 'Serveur HP ProLiant DL380',
                'description' => 'Serveur HP ProLiant DL380 Gen10. Intel Xeon, 32GB RAM, 2x 1TB SSD. Idéal pour hébergement et développement.',
                'daily_rate' => 60.00,
                'weekly_rate' => 350.00,
                'condition' => 'good',
                'category_name' => 'Serveurs'
            ],
            [
                'name' => 'Imprimante 3D Prusa MK3S',
                'description' => 'Imprimante 3D Prusa MK3S avec écran LCD. Précision 0.05mm, volume d\'impression 250x210x210mm. Parfaite pour prototypage.',
                'daily_rate' => 35.00,
                'weekly_rate' => 200.00,
                'condition' => 'excellent',
                'category_name' => 'Imprimantes 3D'
            ],
            [
                'name' => 'Drone DJI Mavic 3',
                'description' => 'Drone DJI Mavic 3 avec caméra Hasselblad 4/3. 4K HDR vidéo, autonomie 46 minutes. Idéal pour prises de vue aériennes.',
                'daily_rate' => 100.00,
                'weekly_rate' => 600.00,
                'condition' => 'excellent',
                'category_name' => 'Drones'
            ],
            [
                'name' => 'Appareil photo Sony A7R V',
                'description' => 'Appareil photo hybride Sony A7R V avec objectif 24-70mm. 61MP full-frame, stabilisation 5 axes. Pour shootings professionnels.',
                'daily_rate' => 70.00,
                'weekly_rate' => 400.00,
                'condition' => 'excellent',
                'category_name' => 'Appareils photo'
            ],
            [
                'name' => 'Projecteur 4K Epson TW7000',
                'description' => 'Projecteur home cinéma 4K HDR Epson TW7000. 3000 lumens, technologie 3LCD. Pour présentations et événements.',
                'daily_rate' => 50.00,
                'weekly_rate' => 300.00,
                'condition' => 'good',
                'category_name' => 'Vidéoprojecteurs'
            ],
            [
                'name' => 'Oscilloscope Rigol DS1202Z-E',
                'description' => 'Oscilloscope numérique Rigol 200MHz 2 voies. Analyse de signaux électroniques, FFT, déclenchement avancé.',
                'daily_rate' => 45.00,
                'weekly_rate' => 250.00,
                'condition' => 'good',
                'category_name' => 'Oscilloscopes'
            ],
            [
                'name' => 'Générateur de fonctions Siglent SDG1025',
                'description' => 'Générateur de fonctions Siglent 25MHz 2 voies. Formes d\'onde standard et arbitraires, modulation, synchronisation.',
                'daily_rate' => 25.00,
                'weekly_rate' => 150.00,
                'condition' => 'excellent',
                'category_name' => 'Générateurs de fonctions'
            ],
            [
                'name' => 'Multimètre Fluke 87V',
                'description' => 'Multimètre professionnel Fluke 87V. Mesures électriques précises, analyse de signaux, température, fréquence.',
                'daily_rate' => 30.00,
                'weekly_rate' => 180.00,
                'condition' => 'excellent',
                'category_name' => 'Multimètres'
            ],
            [
                'name' => 'Station de soudure Hakko FX-888D',
                'description' => 'Station de soudure à air chaud Hakko FX-888D. Contrôle de température précis, pointe céramique durable.',
                'daily_rate' => 20.00,
                'weekly_rate' => 120.00,
                'condition' => 'good',
                'category_name' => 'Stations de soudure'
            ],
            [
                'name' => 'Analyseur de réseau Keysight N9918A',
                'description' => 'Analyseur de réseau Keysight FieldFox 26.5GHz. Mesures S-paramètres, analyse de câbles et antennes, mode VNA.',
                'daily_rate' => 150.00,
                'weekly_rate' => 900.00,
                'condition' => 'good',
                'category_name' => 'Analyseurs de réseau'
            ],
            [
                'name' => 'Système de visioconférence Logitech MeetUp',
                'description' => 'Système de visioconférence tout-en-un Logitech MeetUp. 4K Ultra HD, omnidirectionnel, plug-and-play.',
                'daily_rate' => 40.00,
                'weekly_rate' => 240.00,
                'condition' => 'excellent',
                'category_name' => 'Systèmes de visioconférence'
            ]
        ];
        
        foreach ($equipmentData as $data) {
            // Find the category
            $category = $categories->where('name', $data['category_name'])->first();
            if (!$category) {
                $category = $categories->random();
            }
            
            // Create the equipment
            $equipment = Equipment::firstOrCreate(
                ['slug' => Str::slug($data['name']), 'prestataire_id' => $prestataire->id],
                [
                    'prestataire_id' => $prestataire->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_per_day' => $data['daily_rate'],
                    'price_per_week' => $data['weekly_rate'],
                    'condition' => $data['condition'],
                    'status' => 'active',
                    'city' => $prestataire->city,
                    'postal_code' => $prestataire->postal_code,
                    'address' => $prestataire->address,
                    'country' => $prestataire->country,
                    'security_deposit' => $data['daily_rate'] * 5, // 5 days deposit
                    'minimum_rental_duration' => 1,
                    'maximum_rental_duration' => 30,
                    'is_available' => true
                ]
            );
            
            // Assign the category
            if ($category && $category->parent_id) {
                // It's a subcategory
                $equipment->update([
                    'category_id' => $category->parent_id,
                    'subcategory_id' => $category->id
                ]);
            } else if ($category) {
                // It's a main category
                $equipment->update([
                    'category_id' => $category->id
                ]);
            }
        }
        
        $this->command->info('15 equipment items created successfully!');
    }
    
    /**
     * Create 15 urgent sales for the prestataire
     */
    private function createUrgentSales($prestataire, $categories)
    {
        $urgentSalesData = [
            [
                'title' => 'MacBook Air M1 13" - Neuf sous emballage',
                'description' => 'MacBook Air M1 13 pouces 256GB, neuf sous emballage plastique. Livré avec chargeur d\'origine. Changement de configuration.',
                'price' => 1050.00,
                'condition' => 'excellent',
                'category_name' => 'Ordinateurs portables',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'iPhone 14 Pro Max 256GB - Boîte d\'origine',
                'description' => 'iPhone 14 Pro Max 256GB Deep Purple, état neuf sous emballage. Garantie Apple encore valide. Cause : passage à Android.',
                'price' => 1150.00,
                'condition' => 'excellent',
                'category_name' => 'Smartphones',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Canon EOS R6 Mark II - Boîte scellée',
                'description' => 'Appareil photo hybride Canon EOS R6 Mark II boîte scellée. Capteur 24.2MP, stabilisation 5 axes, enregistrement 4K.',
                'price' => 2300.00,
                'condition' => 'excellent',
                'category_name' => 'Appareils photo',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Téléviseur LG OLED 65" C2 Series',
                'description' => 'Téléviseur LG OLED 65 pouces C2 Series 4K. WebOS 22, Dolby Vision IQ, AI Sound Pro. Montage mural inclus.',
                'price' => 1850.00,
                'condition' => 'excellent',
                'category_name' => 'Téléviseurs',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Console PlayStation 5 Digital Edition',
                'description' => 'PlayStation 5 Digital Edition sans lecteur disque. État neuf, peu utilisée. Contrôleur DualSense inclus.',
                'price' => 450.00,
                'condition' => 'excellent',
                'category_name' => 'Consoles de jeux',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Vélo électrique Bosch Performance Line',
                'description' => 'Vélo électrique à assistance Bosch Performance Line 500Wh. Cadre aluminium, 10 vitesses, fourche suspendue. Très bon état.',
                'price' => 1950.00,
                'condition' => 'good',
                'category_name' => 'Vélo / Scooter',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Table de ping-pong professionnelle',
                'description' => 'Table de ping-pong professionnelle Stiga Competition. Revêtement ITTF, filet réglable, roulettes. Parfaite pour club ou domicile.',
                'price' => 420.00,
                'condition' => 'excellent',
                'category_name' => 'Sports collectifs',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Tente de réception 6x8m - Occasion',
                'description' => 'Tente de réception blanche 6x8m avec toit imperméable. Idéale pour mariages et événements. Utilisée 3 fois seulement.',
                'price' => 380.00,
                'condition' => 'good',
                'category_name' => 'Tentes et barnums',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Machine à café Saeco Xelsis',
                'description' => 'Machine à café automatique Saeco Xelsis avec broyeur intégré. Personnalisation des boissons, nettoyage automatique. État neuf.',
                'price' => 850.00,
                'condition' => 'excellent',
                'category_name' => 'Cafetières',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Aspirateur Dyson V15 Detect',
                'description' => 'Aspirateur Dyson V15 Detect avec laser et écran LCD. 230 AW de puissance, autonomie 60 min, filtres HEPA. État excellent.',
                'price' => 480.00,
                'condition' => 'excellent',
                'category_name' => 'Aspirateurs',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Chaise de bureau Herman Miller Aeron',
                'description' => 'Chaise de bureau Herman Miller Aeron Size B. Posture ergonomique, respirant, ajustements multiples. Parfaite pour longues heures de travail.',
                'price' => 850.00,
                'condition' => 'good',
                'category_name' => 'Chaises de bureau',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Lave-linge Samsung 10kg - Neuf',
                'description' => 'Lave-linge Samsung WW10T554DAW 10kg, neuf sous emballage. Technologie Eco Bubble, 14 programmes, classe A+++. Livraison incluse.',
                'price' => 520.00,
                'condition' => 'excellent',
                'category_name' => 'Lave-linge',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Réfrigérateur américain LG 600L',
                'description' => 'Réfrigérateur américain LG GS-X257HLHV 600L. No Frost, Multi Air Flow, Door-in-Door. Très bon état, peu utilisé.',
                'price' => 1100.00,
                'condition' => 'good',
                'category_name' => 'Réfrigérateurs',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Tondeuse autoportée John Deere',
                'description' => 'Tondeuse autoportée John Deere X300R 18hp. Largeur de coupe 102cm, transmission hydrostatique, siège confort. Entretien à jour.',
                'price' => 2200.00,
                'condition' => 'good',
                'category_name' => 'Tondeuses',
                'location' => 'Casablanca'
            ],
            [
                'title' => 'Système d\'alarme Tyco 8 zones',
                'description' => 'Système d\'alarme professionnel Tyco 8 zones avec centrale, détecteurs, sirènes. Installation récente, manuel d\'utilisation inclus.',
                'price' => 350.00,
                'condition' => 'excellent',
                'category_name' => 'Systèmes d\'alarme',
                'location' => 'Casablanca'
            ]
        ];
        
        foreach ($urgentSalesData as $data) {
            // Find the category
            $category = $categories->where('name', $data['category_name'])->first();
            if (!$category) {
                $category = $categories->random();
            }
            
            // Create the urgent sale
            $urgentSale = UrgentSale::firstOrCreate(
                ['title' => $data['title'], 'prestataire_id' => $prestataire->id],
                [
                    'prestataire_id' => $prestataire->id,
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'condition' => $data['condition'],
                    'location' => $data['location'] . ', ' . $prestataire->postal_code . ', ' . $prestataire->country,
                    'views_count' => rand(0, 100),
                    'contact_count' => rand(0, 20)
                ]
            );
            
            // Assign the category
            if ($category) {
                $urgentSale->update([
                    'category_id' => $category->id
                ]);
            }
        }
        
        $this->command->info('15 urgent sales created successfully!');
    }
}