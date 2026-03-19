<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Category;

class AdditionalServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les prestataires approuvés
        $prestataires = Prestataire::where('is_approved', true)->get();
        
        // Récupérer les catégories
        $plomberieCategory = Category::where('name', 'Plomberie')->first();
        $electriciteCategory = Category::where('name', 'Électricité')->first();
        $informatique = Category::where('name', 'Informatique')->first();
        $graphisme = Category::where('name', 'Graphisme')->first();
        $marketing = Category::where('name', 'Marketing')->first();
        $menuiserie = Category::where('name', 'Menuiserie')->first();
        $peinture = Category::where('name', 'Peinture')->first();
        $maconnerie = Category::where('name', 'Maçonnerie')->first();
        
        // Sous-catégories
        $installationSanitaire = Category::where('name', 'Installation sanitaire')->first();
        $depannagePlomberie = Category::where('name', 'Dépannage plomberie')->first();
        $chauffage = Category::where('name', 'Chauffage')->first();
        $installationElectrique = Category::where('name', 'Installation électrique')->first();
        $depannageElectrique = Category::where('name', 'Dépannage électrique')->first();
        $domotique = Category::where('name', 'Domotique')->first();
        $depannageInformatique = Category::where('name', 'Dépannage informatique')->first();
        $developpementWeb = Category::where('name', 'Développement web')->first();
        $reseaux = Category::where('name', 'Réseaux')->first();
        
        if ($prestataires->isNotEmpty()) {
            // Pour chaque prestataire approuvé, créer des services dans différentes catégories
            foreach ($prestataires as $index => $prestataire) {
                // Services de plomberie
                if ($plomberieCategory && $index % 3 == 0) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Installation de salle de bain complète',
                        'description' => 'Installation complète de salle de bain incluant baignoire, douche, lavabo, WC et tous les raccordements nécessaires.',
                        'price' => 1500.00,
                        'delivery_time' => '1 semaine',
                        'status' => 'active',
                    ]);
                    
                    if ($installationSanitaire) {
                        $service->categories()->attach([$plomberieCategory->id, $installationSanitaire->id]);
                    } else {
                        $service->categories()->attach($plomberieCategory->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Réparation de fuite d\'eau',
                        'description' => 'Détection et réparation de fuites d\'eau dans les canalisations, robinets ou appareils sanitaires.',
                        'price' => 120.00,
                        'delivery_time' => '1 jour',
                        'status' => 'active',
                    ]);
                    
                    if ($depannagePlomberie) {
                        $service->categories()->attach([$plomberieCategory->id, $depannagePlomberie->id]);
                    } else {
                        $service->categories()->attach($plomberieCategory->id);
                    }
                }
                
                // Services d'électricité
                if ($electriciteCategory && $index % 3 == 1) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Installation électrique complète',
                        'description' => 'Installation électrique complète pour maison ou appartement, mise aux normes et certification.',
                        'price' => 2000.00,
                        'delivery_time' => '2 semaines',
                        'status' => 'active',
                    ]);
                    
                    if ($installationElectrique) {
                        $service->categories()->attach([$electriciteCategory->id, $installationElectrique->id]);
                    } else {
                        $service->categories()->attach($electriciteCategory->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Installation de système domotique',
                        'description' => 'Installation et configuration de systèmes domotiques pour contrôler l\'éclairage, le chauffage et les appareils électroniques de votre maison.',
                        'price' => 800.00,
                        'delivery_time' => '3 jours',
                        'status' => 'active',
                    ]);
                    
                    if ($domotique) {
                        $service->categories()->attach([$electriciteCategory->id, $domotique->id]);
                    } else {
                        $service->categories()->attach($electriciteCategory->id);
                    }
                }
                
                // Services informatiques
                if ($informatique && $index % 3 == 2) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Dépannage informatique à domicile',
                        'description' => 'Résolution de problèmes informatiques à domicile : virus, lenteurs, pannes matérielles ou logicielles.',
                        'price' => 80.00,
                        'delivery_time' => '1 jour',
                        'status' => 'active',
                    ]);
                    
                    if ($depannageInformatique) {
                        $service->categories()->attach([$informatique->id, $depannageInformatique->id]);
                    } else {
                        $service->categories()->attach($informatique->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Installation et configuration de réseau domestique',
                        'description' => 'Installation et configuration de réseau Wi-Fi, filaire, NAS et partage de ressources pour particuliers et petites entreprises.',
                        'price' => 150.00,
                        'delivery_time' => '1 jour',
                        'status' => 'active',
                    ]);
                    
                    if ($reseaux) {
                        $service->categories()->attach([$informatique->id, $reseaux->id]);
                    } else {
                        $service->categories()->attach($informatique->id);
                    }
                }
                
                // Services de graphisme
                if ($graphisme && $index % 4 == 0) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Création de logo professionnel',
                        'description' => 'Création d\'un logo unique et professionnel pour votre entreprise, avec plusieurs propositions et révisions.',
                        'price' => 350.00,
                        'delivery_time' => '1 semaine',
                        'status' => 'active',
                    ]);
                    
                    $service->categories()->attach($graphisme->id);
                }
                
                // Services de marketing
                if ($marketing && $index % 4 == 1) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Stratégie de marketing digital',
                        'description' => 'Élaboration d\'une stratégie complète de marketing digital adaptée à votre entreprise et à vos objectifs.',
                        'price' => 600.00,
                        'delivery_time' => '2 semaines',
                        'status' => 'active',
                    ]);
                    
                    $service->categories()->attach($marketing->id);
                }
                
                // Services de menuiserie
                if ($menuiserie && $index % 4 == 2) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Fabrication de meubles sur mesure',
                        'description' => 'Conception et fabrication de meubles sur mesure selon vos besoins et votre espace.',
                        'price' => 1200.00,
                        'delivery_time' => '3 semaines',
                        'status' => 'active',
                    ]);
                    
                    $service->categories()->attach($menuiserie->id);
                }
                
                // Services de peinture
                if ($peinture && $index % 4 == 3) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Peinture intérieure complète',
                        'description' => 'Service de peinture intérieure pour appartement ou maison, préparation des surfaces et finition soignée.',
                        'price' => 25.00, // prix au m²
                        'delivery_time' => 'Variable selon surface',
                        'status' => 'active',
                    ]);
                    
                    $service->categories()->attach($peinture->id);
                }
                
                // Services de maçonnerie
                if ($maconnerie && $index % 5 == 0) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Construction de mur de clôture',
                        'description' => 'Construction de mur de clôture en parpaings, briques ou pierres selon vos préférences.',
                        'price' => 180.00, // prix au mètre linéaire
                        'delivery_time' => 'Variable selon longueur',
                        'status' => 'active',
                    ]);
                    
                    $service->categories()->attach($maconnerie->id);
                }
            }
        }
    }
}
