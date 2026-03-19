<?php

namespace App\Services\Demo;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\FoodProduct;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\UrgentSale;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FrenchMarketplaceDemoSeeder
{
    private const DEMO_EMAIL_DOMAIN = 'demo-fr.example';
    private const DEFAULT_PASSWORD = 'Password@123';

    private FakerGenerator $faker;

    private bool $useRemoteImages = true;

    private array $columnCache = [];

    private array $openverseCache = [];

    private array $imagePathCache = [];

    private array $cityCatalog = [];

    private array $profileMixes = [];

    private array $serviceArchetypes = [];

    private array $equipmentArchetypes = [];

    private array $urgentSaleArchetypes = [];

    private array $foodArchetypes = [];

    public function __construct()
    {
        $this->faker = FakerFactory::create('fr_FR');

        $this->cityCatalog = [
            ['city' => 'Paris', 'postal_code' => '75001', 'latitude' => 48.8566, 'longitude' => 2.3522],
            ['city' => 'Lyon', 'postal_code' => '69001', 'latitude' => 45.7640, 'longitude' => 4.8357],
            ['city' => 'Marseille', 'postal_code' => '13001', 'latitude' => 43.2965, 'longitude' => 5.3698],
            ['city' => 'Bordeaux', 'postal_code' => '33000', 'latitude' => 44.8378, 'longitude' => -0.5792],
            ['city' => 'Toulouse', 'postal_code' => '31000', 'latitude' => 43.6047, 'longitude' => 1.4442],
            ['city' => 'Lille', 'postal_code' => '59000', 'latitude' => 50.6292, 'longitude' => 3.0573],
            ['city' => 'Nantes', 'postal_code' => '44000', 'latitude' => 47.2184, 'longitude' => -1.5536],
            ['city' => 'Nice', 'postal_code' => '06000', 'latitude' => 43.7102, 'longitude' => 7.2620],
            ['city' => 'Montpellier', 'postal_code' => '34000', 'latitude' => 43.6108, 'longitude' => 3.8767],
            ['city' => 'Strasbourg', 'postal_code' => '67000', 'latitude' => 48.5734, 'longitude' => 7.7521],
            ['city' => 'Rennes', 'postal_code' => '35000', 'latitude' => 48.1173, 'longitude' => -1.6778],
            ['city' => 'Grenoble', 'postal_code' => '38000', 'latitude' => 45.1885, 'longitude' => 5.7245],
        ];

        $this->profileMixes = [
            ['service'],
            ['equipment'],
            ['urgent_sale'],
            ['food'],
            ['service', 'equipment'],
            ['equipment', 'urgent_sale'],
            ['service', 'urgent_sale'],
            ['food'],
        ];

        $this->serviceArchetypes = [
            'plomberie' => [
                'label' => 'Plomberie',
                'prestataire_keywords' => ['Plomberie', "Depannage de fuites", "Installation de ballon d'eau chaude"],
                'equipment_key' => 'chantier',
                'urgent_key' => 'bricolage',
                'listings' => [
                    [
                        'title' => "Depannage de fuite et recherche d'origine",
                        'summary' => "Diagnostic rapide, reparation propre et remise en eau dans la journee si besoin.",
                        'price_range' => [79, 180],
                        'delivery_times' => ['2 h', '24 h'],
                        'queries' => ['plumbing repair', 'water pipe', 'wrench'],
                        'category_keywords' => ["Depannage de fuites", 'Reparation de canalisations'],
                    ],
                    [
                        'title' => "Installation de chauffe-eau et ballon d'eau chaude",
                        'summary' => "Pose, remplacement, raccordement et verification de la pression avant remise en service.",
                        'price_range' => [240, 690],
                        'delivery_times' => ['48 h', '3 jours'],
                        'queries' => ['water heater', 'plumbing', 'home repair'],
                        'category_keywords' => ["Installation de ballon d'eau chaude", 'Remplacement de chauffe-eau'],
                    ],
                ],
            ],
            'electricite' => [
                'label' => 'Electricite',
                'prestataire_keywords' => ['Electricite', 'Mise aux normes', 'Depannage electrique'],
                'equipment_key' => 'chantier',
                'urgent_key' => 'high_tech',
                'listings' => [
                    [
                        'title' => 'Depannage electrique et remise en service',
                        'summary' => "Recherche de panne, remise en securite et remplacement des elements defectueux.",
                        'price_range' => [85, 220],
                        'delivery_times' => ['2 h', '24 h'],
                        'queries' => ['electrician', 'electric wiring', 'electric repair'],
                        'category_keywords' => ['Depannage electrique', 'Reparation de prises'],
                    ],
                    [
                        'title' => 'Mise aux normes du tableau et des circuits',
                        'summary' => "Controle complet, tableau propre, etiquetage et conseils d'utilisation.",
                        'price_range' => [320, 980],
                        'delivery_times' => ['3 jours', '1 semaine'],
                        'queries' => ['electrical panel', 'home electricity', 'electrician tools'],
                        'category_keywords' => ['Mise aux normes', 'Remplacement de tableaux'],
                    ],
                ],
            ],
            'jardinage' => [
                'label' => 'Jardinage',
                'prestataire_keywords' => ['Jardinage', 'Tonte de pelouse', 'Taille de haies'],
                'equipment_key' => 'jardin',
                'urgent_key' => 'mobilier',
                'listings' => [
                    [
                        'title' => 'Tonte, bordures et remise en propre du jardin',
                        'summary' => "Passage soigne avec evacuation des dechets verts et conseils d'entretien.",
                        'price_range' => [45, 140],
                        'delivery_times' => ['24 h', '2 jours'],
                        'queries' => ['lawn mower', 'garden maintenance', 'gardening'],
                        'category_keywords' => ['Tonte de pelouse', 'Engazonnement'],
                    ],
                    [
                        'title' => 'Taille de haies et entretien saisonnier',
                        'summary' => "Taille nette, nettoyage du chantier et adaptation selon la saison.",
                        'price_range' => [60, 220],
                        'delivery_times' => ['48 h', '3 jours'],
                        'queries' => ['hedge trimming', 'garden tools', 'gardener'],
                        'category_keywords' => ['Taille de haies', "Elagage d'arbres"],
                    ],
                ],
            ],
            'nettoyage' => [
                'label' => 'Nettoyage',
                'prestataire_keywords' => ['Nettoyage', 'Nettoyage residentiel', 'Nettoyage de vitres'],
                'equipment_key' => 'nettoyage',
                'urgent_key' => 'high_tech',
                'listings' => [
                    [
                        'title' => "Nettoyage d'appartement ou local avant et apres chantier",
                        'summary' => "Intervention complete avec produits adaptes, sols, surfaces et evacuation legere.",
                        'price_range' => [70, 260],
                        'delivery_times' => ['24 h', '2 jours'],
                        'queries' => ['cleaning service', 'vacuum cleaner', 'house cleaning'],
                        'category_keywords' => ['Nettoyage residentiel', 'Nettoyage apres chantier'],
                    ],
                    [
                        'title' => 'Nettoyage de vitres, baies et vitrines',
                        'summary' => 'Finition sans traces pour particuliers, commerces et bureaux.',
                        'price_range' => [35, 140],
                        'delivery_times' => ['24 h', '48 h'],
                        'queries' => ['window cleaning', 'cleaning tools', 'glass cleaning'],
                        'category_keywords' => ['Nettoyage de vitres', 'Entretien de bureaux'],
                    ],
                ],
            ],
            'photographie' => [
                'label' => 'Photographie',
                'prestataire_keywords' => ['Photographie', 'Photographie mariage', 'Photographie produits'],
                'equipment_key' => 'audiovisuel',
                'urgent_key' => 'photo',
                'listings' => [
                    [
                        'title' => 'Reportage photo pour evenement, commerce ou reseaux sociaux',
                        'summary' => 'Prises de vue, tri, retouche legere et livraison web en quelques jours.',
                        'price_range' => [180, 650],
                        'delivery_times' => ['3 jours', '1 semaine'],
                        'queries' => ['photographer camera', 'photo studio', 'event photography'],
                        'category_keywords' => ['Photographie mariage', 'Photographie lifestyle'],
                    ],
                    [
                        'title' => 'Shooting photo produits et e-commerce',
                        'summary' => 'Packshots propres sur fond simple, fichiers optimises pour fiches produits et reseaux.',
                        'price_range' => [120, 480],
                        'delivery_times' => ['72 h', '5 jours'],
                        'queries' => ['product photography', 'camera lens', 'photo studio light'],
                        'category_keywords' => ['Photographie produits', 'Photographie culinaire'],
                    ],
                ],
            ],
            'web' => [
                'label' => 'Developpement web',
                'prestataire_keywords' => ['Informatique', 'Developpement web', 'Maintenance PC'],
                'equipment_key' => 'informatique',
                'urgent_key' => 'high_tech',
                'listings' => [
                    [
                        'title' => 'Creation de site vitrine responsive en francais',
                        'summary' => 'Site moderne, formulaire de contact, optimisation mobile et accompagnement a la mise en ligne.',
                        'price_range' => [420, 1900],
                        'delivery_times' => ['1 semaine', '3 semaines'],
                        'queries' => ['web design laptop', 'coding laptop', 'office computer'],
                        'category_keywords' => ['Developpement web', 'Creation de landing pages'],
                    ],
                    [
                        'title' => 'Maintenance WordPress, correctifs et optimisation',
                        'summary' => 'Sauvegarde, mises a jour, petits correctifs front et acceleration du site existant.',
                        'price_range' => [90, 380],
                        'delivery_times' => ['24 h', '72 h'],
                        'queries' => ['computer maintenance', 'laptop keyboard', 'office desk'],
                        'category_keywords' => ['Maintenance PC', 'Audit de performance'],
                    ],
                ],
            ],
        ];

        $this->equipmentArchetypes = [
            'chantier' => [
                'items' => [
                    [
                        'name' => 'Perceuse Bosch 18V Pro',
                        'description' => 'Perceuse-visseuse pro avec deux batteries et coffret. Parfaite pour chantiers et renovation.',
                        'price_range' => [14, 28],
                        'weekly_range' => [70, 135],
                        'queries' => ['power drill', 'bosch drill', 'construction tools'],
                        'category_keywords' => ['Perceuses'],
                    ],
                    [
                        'name' => 'Scie circulaire Makita 190 mm',
                        'description' => 'Scie circulaire fiable pour coupe bois et panneaux. Guide et lame de rechange inclus.',
                        'price_range' => [19, 34],
                        'weekly_range' => [95, 170],
                        'queries' => ['circular saw', 'makita saw', 'construction tools'],
                        'category_keywords' => ['Scies'],
                    ],
                    [
                        'name' => 'Echafaudage mobile aluminium',
                        'description' => 'Structure legere et stable, facile a deplacer. Ideal facade, peinture et maintenance.',
                        'price_range' => [39, 75],
                        'weekly_range' => [180, 360],
                        'queries' => ['scaffolding', 'construction site', 'aluminium scaffold'],
                        'category_keywords' => ['Echafaudages'],
                    ],
                ],
            ],
            'jardin' => [
                'items' => [
                    [
                        'name' => 'Tondeuse thermique Honda',
                        'description' => 'Tondeuse autopropulsee, demarrage simple et bac de ramassage grande capacite.',
                        'price_range' => [24, 42],
                        'weekly_range' => [120, 210],
                        'queries' => ['lawn mower', 'garden machine', 'garden tools'],
                        'category_keywords' => ['Tondeuses'],
                    ],
                    [
                        'name' => 'Taille-haie electrique 600 W',
                        'description' => "Outil leger et maniable pour haies regulieres et tailles d'entretien.",
                        'price_range' => [16, 28],
                        'weekly_range' => [80, 135],
                        'queries' => ['hedge trimmer', 'garden tools', 'trimming tools'],
                        'category_keywords' => ['Taille-haies'],
                    ],
                    [
                        'name' => 'Nettoyeur de terrasse haute pression',
                        'description' => 'Machine compacte pour terrasses, murets et sols exterieurs avec plusieurs buses.',
                        'price_range' => [22, 36],
                        'weekly_range' => [105, 175],
                        'queries' => ['pressure washer', 'garden cleaning', 'outdoor cleaning'],
                        'category_keywords' => ['Nettoyeurs haute pression'],
                    ],
                ],
            ],
            'nettoyage' => [
                'items' => [
                    [
                        'name' => 'Aspirateur industriel Nilfisk',
                        'description' => 'Aspiration poussiere et eau pour chantier, commerce ou remise en etat apres travaux.',
                        'price_range' => [18, 30],
                        'weekly_range' => [90, 145],
                        'queries' => ['industrial vacuum', 'cleaning equipment', 'vacuum cleaner'],
                        'category_keywords' => ['Aspirateurs industriels'],
                    ],
                    [
                        'name' => 'Nettoyeur haute pression 140 bars',
                        'description' => 'Lavage facades, terrasses, vehicules et mobilier exterieur avec accessoires fournis.',
                        'price_range' => [20, 35],
                        'weekly_range' => [95, 165],
                        'queries' => ['pressure washer', 'cleaning equipment', 'spray washer'],
                        'category_keywords' => ['Nettoyeurs haute pression'],
                    ],
                ],
            ],
            'audiovisuel' => [
                'items' => [
                    [
                        'name' => 'Appareil photo Sony A7 III',
                        'description' => 'Boitier hybride avec batterie supplementaire et carte memoire. Ideal reportage et contenu social.',
                        'price_range' => [45, 80],
                        'weekly_range' => [220, 390],
                        'queries' => ['camera', 'sony camera', 'photography equipment'],
                        'category_keywords' => ['Appareils photo'],
                    ],
                    [
                        'name' => 'Enceinte Bluetooth JBL PartyBox',
                        'description' => 'Sonorisation simple pour anniversaires, showcases et petits evenements.',
                        'price_range' => [22, 48],
                        'weekly_range' => [110, 230],
                        'queries' => ['speaker', 'party speaker', 'audio equipment'],
                        'category_keywords' => ['Sonorisation'],
                    ],
                    [
                        'name' => 'Projecteur LED 1000 lumens',
                        'description' => "Projecteur mobile avec telecommande, support et cables d'alimentation.",
                        'price_range' => [18, 34],
                        'weekly_range' => [85, 160],
                        'queries' => ['projector', 'led projector', 'event equipment'],
                        'category_keywords' => ['Eclairage'],
                    ],
                ],
            ],
            'informatique' => [
                'items' => [
                    [
                        'name' => 'MacBook Pro 14 pouces',
                        'description' => 'Ordinateur portable recent, ideal presentation client, montage leger et bureautique premium.',
                        'price_range' => [38, 72],
                        'weekly_range' => [190, 360],
                        'queries' => ['laptop computer', 'office laptop', 'notebook'],
                        'category_keywords' => ['Ordinateurs portables'],
                    ],
                    [
                        'name' => 'Moniteur 27 pouces USB-C',
                        'description' => 'Ecran net et lumineux avec connectique simple pour bureaux et postes temporaires.',
                        'price_range' => [15, 28],
                        'weekly_range' => [75, 135],
                        'queries' => ['computer monitor', 'office screen', 'desktop setup'],
                        'category_keywords' => ['Moniteurs'],
                    ],
                ],
            ],
        ];

        $this->urgentSaleArchetypes = [
            'bricolage' => [
                'items' => [
                    [
                        'title' => 'Perceuse Bosch sans fil avec coffret',
                        'description' => 'Outil peu utilise, vendu rapidement apres fin de chantier. Batteries et chargeur inclus.',
                        'price_range' => [70, 150],
                        'queries' => ['power drill', 'tool box', 'bosch drill'],
                        'category_keywords' => ['Perceuses', 'Outils electriques'],
                        'condition' => 'good',
                    ],
                    [
                        'title' => 'Scie sauteuse Bosch en tres bon etat',
                        'description' => 'Machine propre, testee et disponible de suite. Ideal bricolage et petits travaux.',
                        'price_range' => [55, 120],
                        'queries' => ['jigsaw tool', 'saw tool', 'construction tools'],
                        'category_keywords' => ['Scies'],
                        'condition' => 'good',
                    ],
                ],
            ],
            'high_tech' => [
                'items' => [
                    [
                        'title' => 'iPhone 14 128 Go facture disponible',
                        'description' => 'Telephone propre, batterie saine, vendu rapidement apres changement de modele.',
                        'price_range' => [420, 690],
                        'queries' => ['smartphone', 'mobile phone', 'iphone'],
                        'category_keywords' => ['Smartphones'],
                        'condition' => 'excellent',
                    ],
                    [
                        'title' => 'MacBook Air M2 pour vente rapide',
                        'description' => "Portable leger, parfait etat, vendu avec chargeur d'origine et housse textile.",
                        'price_range' => [680, 1180],
                        'queries' => ['laptop computer', 'macbook', 'notebook'],
                        'category_keywords' => ['Ordinateurs portables'],
                        'condition' => 'excellent',
                    ],
                    [
                        'title' => 'Televiseur Samsung 55 pouces 4K',
                        'description' => 'Ecran lumineux et fluide, disponible immediatement pour recuperation sur place.',
                        'price_range' => [290, 620],
                        'queries' => ['television', 'smart tv', 'living room tv'],
                        'category_keywords' => ['Televiseurs'],
                        'condition' => 'good',
                    ],
                ],
            ],
            'mobilier' => [
                'items' => [
                    [
                        'title' => 'Canape 3 places tissu beige',
                        'description' => 'Canape propre et confortable, disponible rapidement avant demenagement.',
                        'price_range' => [180, 390],
                        'queries' => ['sofa', 'living room couch', 'furniture'],
                        'category_keywords' => ['Canapes'],
                        'condition' => 'good',
                    ],
                    [
                        'title' => 'Table a manger chene massif',
                        'description' => 'Belle table stable pour 6 personnes, legere patine mais tres bon rendu.',
                        'price_range' => [160, 360],
                        'queries' => ['dining table', 'wood table', 'furniture'],
                        'category_keywords' => ['Tables'],
                        'condition' => 'good',
                    ],
                ],
            ],
            'photo' => [
                'items' => [
                    [
                        'title' => 'Canon EOS R6 avec objectif',
                        'description' => 'Boitier et optique en bon etat, vendu suite a renouvellement du parc photo.',
                        'price_range' => [980, 1650],
                        'queries' => ['camera', 'canon camera', 'photography gear'],
                        'category_keywords' => ['Appareils photo'],
                        'condition' => 'excellent',
                    ],
                    [
                        'title' => 'Drone DJI Mini en coffret',
                        'description' => 'Pack complet avec batteries, helices et sacoche. Vente rapide cette semaine.',
                        'price_range' => [320, 690],
                        'queries' => ['drone', 'camera drone', 'aerial photography'],
                        'category_keywords' => ['Drones'],
                        'condition' => 'good',
                    ],
                ],
            ],
        ];

        $this->foodArchetypes = [
            'pizza' => [
                'products' => [
                    ['name' => 'Pizza Margherita', 'category' => 'pizza', 'price_range' => [10, 14], 'queries' => ['pizza margherita', 'pizza'], 'description' => 'Sauce tomate, mozzarella, basilic frais.'],
                    ['name' => 'Pizza Regina', 'category' => 'pizza', 'price_range' => [12, 16], 'queries' => ['pizza regina', 'pizza ham'], 'description' => 'Jambon, mozzarella, champignons frais.'],
                    ['name' => 'Tiramisu maison', 'category' => 'dessert', 'price_range' => [4, 7], 'queries' => ['tiramisu dessert', 'dessert'], 'description' => 'Creme mascarpone, biscuit cafe et cacao.'],
                    ['name' => 'Limonade artisanale', 'category' => 'boisson', 'price_range' => [2, 4], 'queries' => ['lemonade drink', 'soft drink'], 'description' => 'Boisson fraiche legere et peu sucree.'],
                ],
            ],
            'burger' => [
                'products' => [
                    ['name' => 'Smash burger classique', 'category' => 'plat', 'price_range' => [11, 15], 'queries' => ['burger', 'smash burger'], 'description' => 'Steak facon smash, cheddar, cornichons et sauce maison.'],
                    ['name' => 'Menu burger + frites', 'category' => 'plat', 'price_range' => [14, 19], 'queries' => ['burger fries', 'burger meal'], 'description' => 'Burger signature, frites maison et boisson.'],
                    ['name' => 'Cookie chocolat', 'category' => 'dessert', 'price_range' => [3, 5], 'queries' => ['cookie dessert', 'chocolate cookie'], 'description' => 'Cookie moelleux prepare sur place.'],
                    ['name' => 'The glace maison', 'category' => 'boisson', 'price_range' => [2, 4], 'queries' => ['iced tea drink', 'tea drink'], 'description' => 'The noir, citron et sirop maison.'],
                ],
            ],
            'boulangerie' => [
                'products' => [
                    ['name' => 'Sandwich jambon beurre', 'category' => 'sandwich', 'price_range' => [4, 7], 'queries' => ['ham sandwich', 'bakery sandwich'], 'description' => 'Pain croustillant, beurre doux et jambon blanc.'],
                    ['name' => 'Quiche du jour', 'category' => 'plat', 'price_range' => [5, 8], 'queries' => ['quiche', 'savory pastry'], 'description' => 'Part genereuse avec garniture du jour.'],
                    ['name' => 'Eclair chocolat', 'category' => 'dessert', 'price_range' => [3, 5], 'queries' => ['eclair pastry', 'chocolate pastry'], 'description' => 'Pate a choux, creme et glacage chocolat.'],
                    ['name' => 'Jus pomme artisanal', 'category' => 'boisson', 'price_range' => [2, 4], 'queries' => ['apple juice', 'juice bottle'], 'description' => 'Jus frais en bouteille individuelle.'],
                ],
            ],
            'traiteur' => [
                'products' => [
                    ['name' => 'Couscous poulet legumes', 'category' => 'plat', 'price_range' => [11, 16], 'queries' => ['couscous plate', 'moroccan food'], 'description' => 'Semoule fine, legumes fondants et poulet roti.'],
                    ['name' => 'Brick au thon', 'category' => 'entree', 'price_range' => [3, 6], 'queries' => ['brik pastry', 'savory pastry'], 'description' => 'Feuille croustillante, thon, oeuf et herbes.'],
                    ['name' => 'Makrout miel', 'category' => 'dessert', 'price_range' => [3, 5], 'queries' => ['makrout dessert', 'middle eastern dessert'], 'description' => 'Patisserie semoule et dattes, legerement mielleuse.'],
                    ['name' => 'Citronnade fraiche', 'category' => 'boisson', 'price_range' => [2, 4], 'queries' => ['lemon drink', 'fresh juice'], 'description' => 'Boisson fraiche maison legerement menthee.'],
                ],
            ],
            'sushi' => [
                'products' => [
                    ['name' => 'Maki saumon avocat', 'category' => 'plat', 'price_range' => [8, 13], 'queries' => ['salmon sushi', 'maki sushi'], 'description' => 'Riz vinaigre, saumon et avocat frais.'],
                    ['name' => 'Chirashi saumon', 'category' => 'plat', 'price_range' => [12, 17], 'queries' => ['chirashi bowl', 'salmon bowl'], 'description' => 'Bol de riz, saumon tranche et accompagnements.'],
                    ['name' => 'Gyozas poulet', 'category' => 'entree', 'price_range' => [5, 8], 'queries' => ['gyoza', 'dumplings'], 'description' => 'Raviolis dores et sauce maison.'],
                    ['name' => 'Mochi sesame', 'category' => 'dessert', 'price_range' => [3, 5], 'queries' => ['mochi dessert', 'japanese dessert'], 'description' => 'Dessert moelleux a base de riz gluant.'],
                ],
            ],
        ];
    }

    public function seed(int $count, bool $useRemoteImages = true): array
    {
        $this->useRemoteImages = $useRemoteImages;

        $summary = [
            'profiles_created' => 0,
            'profiles_updated' => 0,
            'services_created' => 0,
            'equipment_created' => 0,
            'urgent_sales_created' => 0,
            'food_products_created' => 0,
        ];

        for ($index = 1; $index <= $count; $index++) {
            $result = $this->createProfile($index);

            if ($result['was_recently_created']) {
                $summary['profiles_created']++;
            } else {
                $summary['profiles_updated']++;
            }

            $summary['services_created'] += $result['services_created'];
            $summary['equipment_created'] += $result['equipment_created'];
            $summary['urgent_sales_created'] += $result['urgent_sales_created'];
            $summary['food_products_created'] += $result['food_products_created'];
        }

        return $summary;
    }

    public function previewExistingDemoData(): array
    {
        $demoUsers = User::withTrashed()
            ->where('email', 'like', '%@' . self::DEMO_EMAIL_DOMAIN)
            ->get(['id']);

        $userIds = $demoUsers->pluck('id');
        $prestataireIds = Prestataire::withTrashed()
            ->whereIn('user_id', $userIds)
            ->pluck('id');

        return [
            'users' => $demoUsers->count(),
            'prestataires' => $prestataireIds->count(),
            'services' => Service::withTrashed()->whereIn('prestataire_id', $prestataireIds)->count(),
            'equipment' => Equipment::withTrashed()->whereIn('prestataire_id', $prestataireIds)->count(),
            'urgent_sales' => UrgentSale::withTrashed()->whereIn('prestataire_id', $prestataireIds)->count(),
            'food_products' => Schema::hasTable('food_products')
                ? FoodProduct::withTrashed()->whereIn('prestataire_id', $prestataireIds)->count()
                : 0,
        ];
    }

    public function clearExistingDemoData(): void
    {
        User::withTrashed()
            ->where('email', 'like', '%@' . self::DEMO_EMAIL_DOMAIN)
            ->get()
            ->each(function (User $user): void {
                $user->forceDelete();
            });

        Storage::disk('public')->deleteDirectory('demo-marketplace');
        File::deleteDirectory(public_path('storage/demo-marketplace'));
        $this->imagePathCache = [];
        $this->openverseCache = [];
    }

    public function createProfile(int $index): array
    {
        $mix = $this->profileMixes[($index - 1) % count($this->profileMixes)];
        $location = $this->cityCatalog[array_rand($this->cityCatalog)];
        $identity = $this->buildIdentity($index);

        $serviceKey = in_array('service', $mix, true) ? $this->randomKey($this->serviceArchetypes) : null;
        $equipmentKey = in_array('equipment', $mix, true) ? $this->resolveEquipmentKey($serviceKey) : null;
        $urgentKey = in_array('urgent_sale', $mix, true) ? $this->resolveUrgentKey($serviceKey, $equipmentKey) : null;
        $foodKey = in_array('food', $mix, true) ? $this->randomKey($this->foodArchetypes) : null;

        $primaryLabel = $foodKey
            ? 'Food'
            : ($serviceKey ? $this->serviceArchetypes[$serviceKey]['label'] : ($equipmentKey ? 'Location equipement' : 'Vente urgente'));

        $companyName = $this->buildCompanyName($identity, $primaryLabel, $foodKey !== null);
        $profileImage = $this->buildAvatar($identity['full_name'], $primaryLabel, $index);

        $primaryCategory = $this->resolvePrestataireCategories($serviceKey, $equipmentKey);

        $user = $this->upsertUser($identity);
        $prestataire = $this->upsertPrestataire(
            $user,
            $location,
            $companyName,
            $profileImage,
            $primaryLabel,
            $mix,
            $primaryCategory,
            $foodKey !== null
        );

        $servicesCreated = $serviceKey ? $this->createServiceListings($prestataire, $location, $this->serviceArchetypes[$serviceKey]) : 0;
        $equipmentCreated = $equipmentKey ? $this->createEquipmentListings($prestataire, $location, $this->equipmentArchetypes[$equipmentKey]) : 0;
        $urgentSalesCreated = $urgentKey ? $this->createUrgentSales($prestataire, $location, $this->urgentSaleArchetypes[$urgentKey]) : 0;
        $foodProductsCreated = $foodKey && Schema::hasTable('food_products')
            ? $this->createFoodProducts($prestataire, $this->foodArchetypes[$foodKey])
            : 0;

        return [
            'was_recently_created' => $user->wasRecentlyCreated,
            'services_created' => $servicesCreated,
            'equipment_created' => $equipmentCreated,
            'urgent_sales_created' => $urgentSalesCreated,
            'food_products_created' => $foodProductsCreated,
        ];
    }

    private function upsertUser(array $identity): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $identity['email']]);

        if ($user->exists && method_exists($user, 'trashed') && $user->trashed()) {
            $user->restore();
        }

        $user->forceFill($this->filterColumns('users', [
            'name' => $identity['full_name'],
            'email' => $identity['email'],
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'role' => 'prestataire',
            'email_verified_at' => now(),
            'avatar' => null,
        ]));

        $user->save();

        return $user;
    }

    private function upsertPrestataire(
        User $user,
        array $location,
        string $companyName,
        string $profileImage,
        string $primaryLabel,
        array $mix,
        array $primaryCategory,
        bool $isFoodProfile
    ): Prestataire {
        $prestataire = Prestataire::withTrashed()->firstOrNew(['user_id' => $user->id]);

        if ($prestataire->exists && method_exists($prestataire, 'trashed') && $prestataire->trashed()) {
            $prestataire->restore();
        }

        $coordinates = $this->cityCoordinates($location);

        $prestataire->fill($this->filterColumns('prestataires', [
            'user_id' => $user->id,
            'company_name' => $companyName,
            'secteur_activite' => $primaryLabel,
            'competences' => implode(', ', array_map([$this, 'mixLabel'], $mix)),
            'category_id' => $primaryCategory['category_id'],
            'subcategory_id' => $primaryCategory['subcategory_id'],
            'description' => $this->prestataireDescription($companyName, $primaryLabel, $location['city'], $isFoodProfile),
            'phone' => $this->demoPhone($user->id ?: random_int(1, 999)),
            'address' => $this->streetAddress($user->id ?: 1),
            'city' => $location['city'],
            'photo' => $profileImage,
            'postal_code' => $location['postal_code'],
            'country' => 'France',
            'service_radius_km' => $isFoodProfile ? 8 : random_int(15, 60),
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'website' => 'https://' . Str::slug($companyName) . '.demo.example',
            'years_experience' => random_int(2, 14),
            'availability_radius' => $isFoodProfile ? 8 : random_int(10, 40),
            'profile_image' => $profileImage,
            'response_time' => random_int(10, 180),
            'completion_rate' => random_int(88, 99),
            'rating_average' => number_format(random_int(40, 50) / 10, 2, '.', ''),
            'total_reviews' => random_int(6, 120),
            'total_projects' => random_int(18, 320),
            'is_approved' => true,
            'is_active' => true,
            'approved_at' => now()->subDays(random_int(3, 180)),
            'last_active_at' => now()->subHours(random_int(1, 48)),
            'verification_status' => 'verified_demo',
            'background_check_status' => 'not_applicable',
            'requires_approval' => false,
            'food_delivery_enabled' => $isFoodProfile,
            'food_pickup_enabled' => true,
            'food_delivery_radius_km' => $isFoodProfile ? random_int(3, 8) : 0,
            'food_delivery_base_fee' => $isFoodProfile ? random_int(200, 450) / 100 : 0,
            'food_delivery_fee_per_km' => $isFoodProfile ? random_int(40, 90) / 100 : 0,
            'food_min_order_delivery' => $isFoodProfile ? random_int(1200, 2200) / 100 : 0,
            'food_min_order_pickup' => $isFoodProfile ? random_int(800, 1500) / 100 : 0,
            'food_free_delivery_above' => $isFoodProfile ? random_int(2500, 4500) / 100 : 0,
            'food_estimated_prep_time' => $isFoodProfile ? random_int(15, 35) : 0,
            'food_delivery_schedule' => $isFoodProfile ? $this->foodSchedule() : null,
            'delivery_mode' => $isFoodProfile ? 'both' : 'internal',
            'auto_assign_drivers' => false,
            'min_driver_rating' => 4.3,
            'email_visible' => false,
            'phone_visible' => true,
        ]));

        $prestataire->save();

        return $prestataire;
    }

    private function createServiceListings(Prestataire $prestataire, array $location, array $archetype): int
    {
        $created = 0;

        foreach ($archetype['listings'] as $listing) {
            $service = Service::firstOrCreate(
                ['prestataire_id' => $prestataire->id, 'title' => $listing['title']],
                $this->filterColumns('services', [
                    'prestataire_id' => $prestataire->id,
                    'title' => $listing['title'],
                    'description' => $listing['summary'] . ' Intervention sur ' . $location['city'] . ' et communes voisines.',
                    'price' => $this->randFloat($listing['price_range'][0], $listing['price_range'][1]),
                    'delivery_time' => $listing['delivery_times'][array_rand($listing['delivery_times'])],
                    'status' => 'active',
                    'reservable' => true,
                    'city' => $location['city'],
                    'postal_code' => $location['postal_code'],
                    'address' => $this->streetAddress($prestataire->id),
                ])
            );

            $categories = $this->findMatchingCategories($listing['category_keywords']);

            if ($categories->isNotEmpty()) {
                $service->categories()->syncWithoutDetaching($categories->pluck('id')->all());
            }

            if (Schema::hasTable('service_images') && !$service->images()->exists()) {
                $imagePath = $this->resolveImagePath('service', $listing['queries'], $listing['title']);

                $service->images()->create([
                    'image_path' => $imagePath,
                    'original_name' => basename($imagePath),
                    'file_size' => $this->fileSize($imagePath),
                    'mime_type' => Str::endsWith($imagePath, '.svg') ? 'image/svg+xml' : 'image/jpeg',
                    'order' => 0,
                ]);
            }

            if ($service->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    private function createEquipmentListings(Prestataire $prestataire, array $location, array $archetype): int
    {
        $created = 0;

        foreach ($archetype['items'] as $offset => $item) {
            $slug = Str::slug($item['name'] . '-' . $prestataire->id . '-' . $offset);
            $category = $this->findMatchingCategories($item['category_keywords'])->first();

            $equipment = Equipment::firstOrCreate(
                ['slug' => $slug],
                $this->filterColumns('equipment', [
                    'prestataire_id' => $prestataire->id,
                    'category_id' => $category?->parent_id ?: $category?->id,
                    'subcategory_id' => $category?->parent_id ? $category->id : null,
                    'name' => $item['name'],
                    'slug' => $slug,
                    'description' => $item['description'],
                    'technical_specifications' => 'Version demo, entretien realise et fonctionnement verifie avant chaque location.',
                    'photos' => [$this->resolveImagePath('equipment', $item['queries'], $item['name'])],
                    'main_photo' => $this->resolveImagePath('equipment', $item['queries'], $item['name']),
                    'price_per_day' => $this->randFloat($item['price_range'][0], $item['price_range'][1]),
                    'price_per_week' => $this->randFloat($item['weekly_range'][0], $item['weekly_range'][1]),
                    'security_deposit' => $this->randFloat(80, 450),
                    'condition' => ['excellent', 'good', 'very_good'][array_rand(['excellent', 'good', 'very_good'])],
                    'status' => 'active',
                    'is_available' => true,
                    'minimum_rental_duration' => 1,
                    'maximum_rental_duration' => 21,
                    'address' => $this->streetAddress($prestataire->id + $offset),
                    'city' => $location['city'],
                    'postal_code' => $location['postal_code'],
                    'country' => 'France',
                    'latitude' => $this->cityCoordinates($location)['latitude'],
                    'longitude' => $this->cityCoordinates($location)['longitude'],
                    'rental_conditions' => "Piece d'identite et caution requises. Retrait sur rendez-vous.",
                    'usage_instructions' => 'Demonstration rapide remise au retrait. Utilisation normale uniquement.',
                    'safety_instructions' => 'Port des EPI conseille selon le materiel.',
                    'included_accessories' => ['chargeur', 'coffret'],
                    'requires_license' => false,
                    'average_rating' => number_format(random_int(40, 50) / 10, 2, '.', ''),
                    'total_reviews' => random_int(2, 35),
                    'total_rentals' => random_int(4, 80),
                    'view_count' => random_int(30, 900),
                ])
            );

            if ($equipment->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    private function createUrgentSales(Prestataire $prestataire, array $location, array $archetype): int
    {
        $created = 0;

        foreach ($archetype['items'] as $offset => $item) {
            $slug = Str::slug($item['title'] . '-' . $prestataire->id . '-' . $offset);
            $category = $this->findMatchingCategories($item['category_keywords'])->first();

            $urgentSale = UrgentSale::firstOrCreate(
                ['slug' => $slug],
                $this->filterColumns('urgent_sales', [
                    'prestataire_id' => $prestataire->id,
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'price' => $this->randFloat($item['price_range'][0], $item['price_range'][1]),
                    'condition' => $item['condition'],
                    'category_id' => $category?->id,
                    'photos' => [$this->resolveImagePath('urgent-sale', $item['queries'], $item['title'])],
                    'quantity' => random_int(1, 3),
                    'location' => $location['city'] . ', ' . $location['postal_code'] . ', France',
                    'status' => 'active',
                    'slug' => $slug,
                    'views_count' => random_int(10, 650),
                    'contact_count' => random_int(1, 45),
                ])
            );

            if ($urgentSale->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    private function createFoodProducts(Prestataire $prestataire, array $archetype): int
    {
        $created = 0;

        foreach ($archetype['products'] as $offset => $product) {
            $foodProduct = FoodProduct::firstOrCreate(
                ['prestataire_id' => $prestataire->id, 'name' => $product['name']],
                $this->filterColumns('food_products', [
                    'prestataire_id' => $prestataire->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $this->randFloat($product['price_range'][0], $product['price_range'][1]),
                    'image' => $this->resolveImagePath('food', $product['queries'], $product['name']),
                    'category' => $product['category'],
                    'is_available' => true,
                    'payment_policy' => 'full_prepay',
                    'deposit_percent' => 100,
                    'preparation_time' => random_int(8, 25),
                    'stock' => random_int(8, 40),
                    'options' => $this->foodOptions($product['category']),
                    'sort_order' => $offset + 1,
                ])
            );

            if ($foodProduct->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    private function resolvePrestataireCategories(?string $serviceKey, ?string $equipmentKey): array
    {
        if ($serviceKey) {
            $category = $this->findMatchingCategories($this->serviceArchetypes[$serviceKey]['prestataire_keywords'])->first();

            if ($category) {
                return [
                    'category_id' => $category->parent_id ?: $category->id,
                    'subcategory_id' => $category->parent_id ? $category->id : null,
                ];
            }
        }

        if ($equipmentKey) {
            $firstItem = $this->equipmentArchetypes[$equipmentKey]['items'][0];
            $category = $this->findMatchingCategories($firstItem['category_keywords'])->first();

            if ($category) {
                return [
                    'category_id' => $category->parent_id ?: $category->id,
                    'subcategory_id' => $category->parent_id ? $category->id : null,
                ];
            }
        }

        $fallback = Category::query()->whereNotNull('parent_id')->inRandomOrder()->first();

        return [
            'category_id' => $fallback?->parent_id ?: $fallback?->id,
            'subcategory_id' => $fallback?->parent_id ? $fallback?->id : null,
        ];
    }

    private function findMatchingCategories(array $keywords): Collection
    {
        $query = Category::query()->where(function ($builder) use ($keywords): void {
            foreach ($keywords as $keyword) {
                $builder->orWhere('name', 'like', '%' . $keyword . '%');
            }
        });

        $categories = $query->limit(3)->get();

        if ($categories->isNotEmpty()) {
            return $categories;
        }

        return Category::query()->whereNotNull('parent_id')->inRandomOrder()->limit(1)->get();
    }

    private function resolveImagePath(string $kind, array $queries, string $title): string
    {
        foreach ($queries as $query) {
            $cacheKey = $kind . '|' . Str::lower($query);

            if (isset($this->imagePathCache[$cacheKey])) {
                return $this->imagePathCache[$cacheKey];
            }

            if ($this->useRemoteImages) {
                $result = $this->fetchOpenverseResult($query);

                if ($result) {
                    $path = 'demo-marketplace/cache/' . Str::slug($kind . '-' . $query) . '.jpg';

                    if (!Storage::disk('public')->exists($path)) {
                        $download = Http::timeout(20)->retry(2, 300)->get($result['thumbnail'] ?? $result['url']);

                        if ($download->successful()) {
                            $this->putDemoAsset($path, $download->body());
                        }
                    }

                    if (Storage::disk('public')->exists($path)) {
                        return $this->imagePathCache[$cacheKey] = $path;
                    }
                }
            }
        }

        $placeholderKey = $kind . '|' . Str::slug($title);

        if (!isset($this->imagePathCache[$placeholderKey])) {
            $path = 'demo-marketplace/cache/' . Str::slug($kind . '-' . $title) . '.svg';
            $this->putDemoAsset($path, $this->placeholderSvg($title, strtoupper($kind)));
            $this->imagePathCache[$placeholderKey] = $path;
        }

        return $this->imagePathCache[$placeholderKey];
    }

    private function fetchOpenverseResult(string $query): ?array
    {
        if (!array_key_exists($query, $this->openverseCache)) {
            $response = Http::timeout(20)->retry(2, 300)->get('https://api.openverse.org/v1/images/', [
                'q' => $query,
                'license' => 'cc0',
                'license_type' => 'commercial',
                'page_size' => 10,
            ]);

            $this->openverseCache[$query] = $response->successful()
                ? collect($response->json('results', []))
                    ->filter(fn (array $result): bool => !($result['mature'] ?? false) && !empty($result['thumbnail'] ?? $result['url'] ?? null))
                    ->values()
                    ->all()
                : [];
        }

        if (empty($this->openverseCache[$query])) {
            return null;
        }

        return $this->openverseCache[$query][array_rand($this->openverseCache[$query])];
    }

    private function buildIdentity(int $index): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $fullName = trim($firstName . ' ' . $lastName);
        $emailSlug = Str::slug(Str::ascii($firstName . '-' . $lastName));

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'email' => $emailSlug . '-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT) . '@' . self::DEMO_EMAIL_DOMAIN,
        ];
    }

    private function buildCompanyName(array $identity, string $label, bool $foodProfile): string
    {
        if ($foodProfile) {
            $patterns = [
                'Chez ' . $identity['first_name'],
                'Maison ' . $identity['last_name'],
                $identity['last_name'] . ' ' . $label,
            ];

            return $patterns[array_rand($patterns)];
        }

        $patterns = [
            $label . ' ' . $identity['last_name'],
            'Atelier ' . $identity['last_name'],
            $identity['first_name'] . ' ' . $label,
        ];

        return $patterns[array_rand($patterns)];
    }

    private function buildAvatar(string $fullName, string $label, int $index): string
    {
        $path = 'demo-marketplace/avatars/' . Str::slug($fullName . '-' . $index) . '.svg';

        if (!Storage::disk('public')->exists($path)) {
            $initials = collect(explode(' ', $fullName))
                ->filter()
                ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
                ->take(2)
                ->implode('');

            $this->putDemoAsset($path, $this->avatarSvg($initials, $label));
        }

        return $path;
    }

    private function avatarSvg(string $initials, string $label): string
    {
        $palette = [
            ['#1f2937', '#f59e0b'],
            ['#0f766e', '#f97316'],
            ['#1d4ed8', '#f43f5e'],
            ['#4c1d95', '#22c55e'],
        ];
        [$bg, $accent] = $palette[array_rand($palette)];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="640" viewBox="0 0 640 640">
  <rect width="640" height="640" fill="{$bg}"/>
  <circle cx="320" cy="240" r="120" fill="{$accent}" opacity="0.9"/>
  <rect x="110" y="380" width="420" height="140" rx="32" fill="#ffffff" opacity="0.12"/>
  <text x="320" y="280" text-anchor="middle" font-family="Arial, sans-serif" font-size="124" font-weight="700" fill="#ffffff">{$initials}</text>
  <text x="320" y="442" text-anchor="middle" font-family="Arial, sans-serif" font-size="32" fill="#ffffff">{$this->svgSafe(Str::limit($label, 26, ''))}</text>
</svg>
SVG;
    }

    private function placeholderSvg(string $title, string $kind): string
    {
        $colors = ['#111827', '#0f766e', '#1d4ed8', '#7c2d12'];
        $accent = $colors[array_rand($colors)];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="960" viewBox="0 0 1280 960">
  <rect width="1280" height="960" fill="#f4f4f5"/>
  <rect x="72" y="72" width="1136" height="816" rx="42" fill="{$accent}" opacity="0.92"/>
  <circle cx="230" cy="220" r="72" fill="#ffffff" opacity="0.22"/>
  <circle cx="1030" cy="760" r="96" fill="#ffffff" opacity="0.12"/>
  <text x="120" y="520" font-family="Arial, sans-serif" font-size="72" font-weight="700" fill="#ffffff">{$this->svgSafe(Str::limit($title, 32, ''))}</text>
  <text x="120" y="610" font-family="Arial, sans-serif" font-size="36" fill="#ffffff">{$this->svgSafe($kind)}</text>
</svg>
SVG;
    }

    private function prestataireDescription(string $companyName, string $label, string $city, bool $isFoodProfile): string
    {
        if ($isFoodProfile) {
            return $companyName . ' propose une offre food de demonstration a ' . $city . ', avec carte, retrait et livraison locale.';
        }

        return $companyName . ' intervient a ' . $city . ' autour de ' . $label . ', avec annonces et disponibilites generees pour la demonstration.';
    }

    private function foodOptions(string $category): array
    {
        return match ($category) {
            'pizza' => ['taille' => ['M', 'L'], 'supplement' => ['mozzarella', 'olives']],
            'plat' => ['boisson' => ['eau', 'soda'], 'sauce' => ['douce', 'epicee']],
            'sandwich' => ['pain' => ['blanc', 'complet'], 'supplement' => ['fromage', 'crudites']],
            default => ['portion' => ['standard']],
        };
    }

    private function foodSchedule(): array
    {
        return [
            'monday' => ['11:30-14:30', '18:30-22:00'],
            'tuesday' => ['11:30-14:30', '18:30-22:00'],
            'wednesday' => ['11:30-14:30', '18:30-22:00'],
            'thursday' => ['11:30-14:30', '18:30-22:00'],
            'friday' => ['11:30-14:30', '18:30-22:30'],
            'saturday' => ['18:30-22:30'],
            'sunday' => ['18:30-21:30'],
        ];
    }

    private function cityCoordinates(array $location): array
    {
        return [
            'latitude' => round($location['latitude'] + (random_int(-20, 20) / 1000), 6),
            'longitude' => round($location['longitude'] + (random_int(-20, 20) / 1000), 6),
        ];
    }

    private function streetAddress(int $seed): string
    {
        $streets = [
            'rue des Artisans',
            'avenue du Marche',
            'boulevard des Ateliers',
            'allee des Services',
            "rue de l'Entrepot",
            'impasse des Saveurs',
            'avenue des Entreprises',
            'rue des Jardins',
            'quai des Materiaux',
            'rue du Commerce',
        ];

        return (($seed % 87) + 1) . ' ' . $streets[$seed % count($streets)];
    }

    private function demoPhone(int $seed): string
    {
        $partA = str_pad((string) (($seed * 7) % 100), 2, '0', STR_PAD_LEFT);
        $partB = str_pad((string) (($seed * 13) % 100), 2, '0', STR_PAD_LEFT);
        $partC = str_pad((string) (($seed * 17) % 100), 2, '0', STR_PAD_LEFT);

        return '09 99 ' . $partA . ' ' . $partB . ' ' . $partC;
    }

    private function mixLabel(string $mix): string
    {
        return match ($mix) {
            'service' => 'service',
            'equipment' => 'equipement',
            'urgent_sale' => 'vente urgente',
            'food' => 'food',
            default => $mix,
        };
    }

    private function resolveEquipmentKey(?string $serviceKey): string
    {
        if ($serviceKey && isset($this->serviceArchetypes[$serviceKey]['equipment_key'])) {
            return $this->serviceArchetypes[$serviceKey]['equipment_key'];
        }

        return $this->randomKey($this->equipmentArchetypes);
    }

    private function resolveUrgentKey(?string $serviceKey, ?string $equipmentKey): string
    {
        if ($serviceKey && isset($this->serviceArchetypes[$serviceKey]['urgent_key'])) {
            return $this->serviceArchetypes[$serviceKey]['urgent_key'];
        }

        return match ($equipmentKey) {
            'audiovisuel' => 'photo',
            'informatique' => 'high_tech',
            'jardin' => 'mobilier',
            default => 'bricolage',
        };
    }

    private function randomKey(array $items): string
    {
        $keys = array_keys($items);

        return $keys[array_rand($keys)];
    }

    private function randFloat(int|float $min, int|float $max): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
    }

    private function fileSize(string $path): int
    {
        return Storage::disk('public')->exists($path)
            ? (int) Storage::disk('public')->size($path)
            : 0;
    }

    private function putDemoAsset(string $path, string $contents): void
    {
        Storage::disk('public')->put($path, $contents);

        $publicPath = public_path('storage/' . str_replace(['\\', '//'], '/', trim($path, '/\\')));
        $directory = dirname($publicPath);

        if (!is_dir($directory)) {
            File::ensureDirectoryExists($directory);
        }

        file_put_contents($publicPath, $contents);
    }

    private function filterColumns(string $table, array $attributes): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        if (!isset($this->columnCache[$table])) {
            $this->columnCache[$table] = array_flip(Schema::getColumnListing($table));
        }

        return array_intersect_key($attributes, $this->columnCache[$table]);
    }

    private function svgSafe(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
