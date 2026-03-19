<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Category;

class EnhancedServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer les prestataires
        $prestataires = Prestataire::all();
        
        // Récupérer les catégories
        $categories = Category::whereNotNull('parent_id')->get(); // Sous-catégories uniquement

        if ($prestataires->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Aucun prestataire ou catégorie trouvé. Veuillez d\'abord exécuter les seeders correspondants.');
            return;
        }

        // Données de services plus diversifiées
        $serviceData = [
            // Développement Web
            [
                'title' => 'Création de site web responsive',
                'description' => 'Je crée des sites web modernes, responsives et optimisés pour le SEO. Utilisation des dernières technologies (HTML5, CSS3, JavaScript, Laravel).',
                'price' => 500.00,
                'delivery_time' => '2 semaines',
                'status' => 'active',
                'category_name' => 'Développement Web',
                'city' => 'Paris',
                'postal_code' => '75001'
            ],
            [
                'title' => 'Développement d\'application web sur mesure',
                'description' => 'Développement d\'applications web personnalisées selon vos besoins spécifiques. Utilisation de frameworks modernes comme Laravel, Vue.js ou React.',
                'price' => 1200.00,
                'delivery_time' => '1 mois',
                'status' => 'active',
                'category_name' => 'Développement Web',
                'city' => 'Lyon',
                'postal_code' => '69001'
            ],
            [
                'title' => 'Intégration de maquette design',
                'description' => 'Transformation de vos maquettes PSD, Figma ou XD en code HTML/CSS responsive et optimisé.',
                'price' => 300.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'Développement Web',
                'city' => 'Marseille',
                'postal_code' => '13001'
            ],
            [
                'title' => 'Optimisation de performance web',
                'description' => 'Analyse complète de votre site web et mise en œuvre de solutions pour améliorer sa vitesse, son référencement et sa conversion.',
                'price' => 400.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'Développement Web',
                'city' => 'Bordeaux',
                'postal_code' => '33000'
            ],
            
            // Design Graphique
            [
                'title' => 'Création de logo professionnel',
                'description' => 'Design de logo unique et mémorable pour votre entreprise. Plusieurs concepts proposés, fichiers sources inclus.',
                'price' => 150.00,
                'delivery_time' => '5 jours',
                'status' => 'active',
                'category_name' => 'Design Graphique',
                'city' => 'Paris',
                'postal_code' => '75002'
            ],
            [
                'title' => 'Charte graphique d\'entreprise',
                'description' => 'Création complète de votre charte graphique : logo, palette de couleurs, typographie, éléments visuels.',
                'price' => 400.00,
                'delivery_time' => '2 semaines',
                'status' => 'active',
                'category_name' => 'Design Graphique',
                'city' => 'Lyon',
                'postal_code' => '69002'
            ],
            [
                'title' => 'Flyers et dépliants',
                'description' => 'Design de supports de communication imprimés : flyers, dépliants, brochures, cartes de visite.',
                'price' => 80.00,
                'delivery_time' => '3 jours',
                'status' => 'active',
                'category_name' => 'Design Graphique',
                'city' => 'Marseille',
                'postal_code' => '13002'
            ],
            
            // Rédaction et Contenu
            [
                'title' => 'Rédaction d\'articles SEO',
                'description' => 'Rédaction d\'articles optimisés pour le référencement naturel. Recherche de mots-clés et structuration selon les bonnes pratiques SEO.',
                'price' => 80.00,
                'delivery_time' => '3 jours',
                'status' => 'active',
                'category_name' => 'Rédaction et Contenu',
                'city' => 'Bordeaux',
                'postal_code' => '33001'
            ],
            [
                'title' => 'Traduction de contenu FR/EN',
                'description' => 'Traduction professionnelle de vos contenus du français vers l\'anglais ou inversement. Respect du ton et adaptation culturelle.',
                'price' => 0.10,
                'delivery_time' => '5 jours',
                'status' => 'active',
                'category_name' => 'Rédaction et Contenu',
                'city' => 'Paris',
                'postal_code' => '75003'
            ],
            [
                'title' => 'Contenu pour réseaux sociaux',
                'description' => 'Création de contenu engageant pour vos réseaux sociaux : Facebook, Instagram, LinkedIn, Twitter.',
                'price' => 150.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'Rédaction et Contenu',
                'city' => 'Lyon',
                'postal_code' => '69003'
            ],
            
            // Marketing Digital
            [
                'title' => 'Gestion de campagne Google Ads',
                'description' => 'Création, gestion et optimisation de campagnes publicitaires Google Ads pour maximiser votre ROI.',
                'price' => 300.00,
                'delivery_time' => '1 mois',
                'status' => 'active',
                'category_name' => 'Marketing Digital',
                'city' => 'Marseille',
                'postal_code' => '13003'
            ],
            [
                'title' => 'Stratégie de contenu pour réseaux sociaux',
                'description' => 'Développement d\'une stratégie complète de contenu pour vos réseaux sociaux avec calendrier éditorial.',
                'price' => 250.00,
                'delivery_time' => '2 semaines',
                'status' => 'active',
                'category_name' => 'Marketing Digital',
                'city' => 'Bordeaux',
                'postal_code' => '33002'
            ],
            
            // Consulting
            [
                'title' => 'Consultation en stratégie d\'entreprise',
                'description' => 'Analyse complète de votre entreprise et élaboration d\'une stratégie de développement adaptée à vos objectifs et au marché.',
                'price' => 1500.00,
                'delivery_time' => '2 semaines',
                'status' => 'active',
                'category_name' => 'Consulting',
                'city' => 'Paris',
                'postal_code' => '75004'
            ],
            [
                'title' => 'Audit et optimisation de performance web',
                'description' => 'Analyse complète de votre site web et mise en œuvre de solutions pour améliorer sa vitesse, son référencement et sa conversion.',
                'price' => 800.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'Consulting',
                'city' => 'Lyon',
                'postal_code' => '69004'
            ],
            
            // Développement Mobile
            [
                'title' => 'Développement d\'applications mobiles',
                'description' => 'Création d\'applications mobiles natives pour iOS et Android avec une expérience utilisateur optimale et des fonctionnalités personnalisées.',
                'price' => 2500.00,
                'delivery_time' => '6 semaines',
                'status' => 'active',
                'category_name' => 'Développement Mobile',
                'city' => 'Marseille',
                'postal_code' => '13004'
            ],
            
            // Plomberie
            [
                'title' => 'Dépannage de fuites d\'eau',
                'description' => 'Intervention rapide pour réparer vos fuites d\'eau. Détection de fuites invisibles, réparation de canalisations.',
                'price' => 80.00,
                'delivery_time' => '24h',
                'status' => 'active',
                'category_name' => 'Dépannage de fuites',
                'city' => 'Bordeaux',
                'postal_code' => '33003'
            ],
            [
                'title' => 'Installation de ballon d\'eau chaude',
                'description' => 'Installation professionnelle de ballon d\'eau chaude électrique ou thermodynamique. Remplacement et mise en service.',
                'price' => 300.00,
                'delivery_time' => '3 jours',
                'status' => 'active',
                'category_name' => 'Installation de ballon d\'eau chaude',
                'city' => 'Paris',
                'postal_code' => '75005'
            ],
            
            // Électricité
            [
                'title' => 'Mise aux normes électriques',
                'description' => 'Mise aux normes complète de votre installation électrique. Remplacement du tableau électrique, mise en conformité.',
                'price' => 500.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'Mise aux normes',
                'city' => 'Lyon',
                'postal_code' => '69005'
            ],
            
            // Jardinage
            [
                'title' => 'Tonte de pelouse',
                'description' => 'Tonte de pelouse régulière pour particuliers et professionnels. Entretien de jardin, débroussaillage.',
                'price' => 40.00,
                'delivery_time' => '2 jours',
                'status' => 'active',
                'category_name' => 'Tonte de pelouse',
                'city' => 'Marseille',
                'postal_code' => '13005'
            ],
            [
                'title' => 'Taille de haies',
                'description' => 'Taille de haies et arbustes pour maintenir votre jardin en bon état. Travaux de taille de formation et d\'entretien.',
                'price' => 60.00,
                'delivery_time' => '3 jours',
                'status' => 'active',
                'category_name' => 'Taille de haies',
                'city' => 'Bordeaux',
                'postal_code' => '33004'
            ],
            
            // Peinture
            [
                'title' => 'Peinture intérieure',
                'description' => 'Peinture intérieure de qualité pour vos murs et plafonds. Préparation des surfaces, finitions parfaites.',
                'price' => 25.00,
                'delivery_time' => '3 jours',
                'status' => 'active',
                'category_name' => 'Peinture intérieure',
                'city' => 'Paris',
                'postal_code' => '75006'
            ],
            
            // Maçonnerie
            [
                'title' => 'Construction de murs',
                'description' => 'Construction de murs en parpaings, briques ou pierres. Travaux de maçonnerie pour extensions, clôtures.',
                'price' => 80.00,
                'delivery_time' => '1 semaine',
                'status' => 'active',
                'category_name' => 'Construction de murs',
                'city' => 'Lyon',
                'postal_code' => '69006'
            ],
            
            // Nettoyage
            [
                'title' => 'Nettoyage de vitres',
                'description' => 'Nettoyage professionnel de vitres intérieures et extérieures. Nettoyage de façades vitrées, hauteur comprise.',
                'price' => 30.00,
                'delivery_time' => '2 jours',
                'status' => 'active',
                'category_name' => 'Nettoyage de vitres',
                'city' => 'Marseille',
                'postal_code' => '13006'
            ],
            
            // Transport
            [
                'title' => 'Déménagement',
                'description' => 'Service de déménagement complet pour particuliers et professionnels. Emballage, transport, déballage.',
                'price' => 200.00,
                'delivery_time' => '1 jour',
                'status' => 'active',
                'category_name' => 'Déménagement',
                'city' => 'Bordeaux',
                'postal_code' => '33005'
            ],
            
            // Événementiel
            [
                'title' => 'Organisation de mariages',
                'description' => 'Organisation complète de votre mariage : lieu, traiteur, décoration, animation, coordination du jour J.',
                'price' => 2000.00,
                'delivery_time' => '3 mois',
                'status' => 'active',
                'category_name' => 'Organisation de mariages',
                'city' => 'Paris',
                'postal_code' => '75007'
            ]
        ];

        // Créer les services
        foreach ($serviceData as $data) {
            // Trouver la catégorie
            $category = $categories->where('name', $data['category_name'])->first();
            if (!$category) {
                $category = $categories->random();
            }

            // Sélectionner un prestataire aléatoire
            $prestataire = $prestataires->random();

            // Créer le service
            $service = Service::firstOrCreate(
                ['title' => $data['title'], 'prestataire_id' => $prestataire->id],
                [
                    'prestataire_id' => $prestataire->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'delivery_time' => $data['delivery_time'],
                    'status' => $data['status'],
                    'city' => $data['city'],
                    'postal_code' => $data['postal_code']
                ]
            );

            // Assigner la catégorie
            if ($category) {
                $service->categories()->syncWithoutDetaching([$category->id]);
            }
        }

        $this->command->info('Services créés avec succès!');
    }
}