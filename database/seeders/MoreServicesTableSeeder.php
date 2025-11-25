<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Category;

class MoreServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les prestataires approuvés
        $prestataires = Prestataire::where('is_approved', true)->get();
        
        // Récupérer les catégories principales
        $plomberieCategory = Category::where('name', 'Plomberie')->first();
        $electriciteCategory = Category::where('name', 'Électricité')->first();
        $informatique = Category::where('name', 'Informatique')->first();
        $graphisme = Category::where('name', 'Graphisme')->first();
        $marketing = Category::where('name', 'Marketing')->first();
        $menuiserie = Category::where('name', 'Menuiserie')->first();
        $peinture = Category::where('name', 'Peinture')->first();
        $maconnerie = Category::where('name', 'Maçonnerie')->first();
        
        // Récupérer les sous-catégories
        $installationSanitaire = Category::where('name', 'Installation sanitaire')->first();
        $depannagePlomberie = Category::where('name', 'Dépannage plomberie')->first();
        $chauffage = Category::where('name', 'Chauffage')->first();
        $installationElectrique = Category::where('name', 'Installation électrique')->first();
        $depannageElectrique = Category::where('name', 'Dépannage électrique')->first();
        $domotique = Category::where('name', 'Domotique')->first();
        $depannageInformatique = Category::where('name', 'Dépannage informatique')->first();
        $developpementWeb = Category::where('name', 'Développement web')->first();
        $reseaux = Category::where('name', 'Réseaux')->first();
        $marketingDigital = Category::where('name', 'Marketing digital')->first();
        $seo = Category::where('name', 'SEO')->first();
        $menuiserieInterieure = Category::where('name', 'Menuiserie intérieure')->first();
        $menuiserieExterieure = Category::where('name', 'Menuiserie extérieure')->first();
        $peintureInterieure = Category::where('name', 'Peinture intérieure')->first();
        $peintureExterieure = Category::where('name', 'Peinture extérieure')->first();
        $construction = Category::where('name', 'Construction')->first();
        
        if ($prestataires->isNotEmpty()) {
            // Pour chaque prestataire approuvé, créer des services dans différentes catégories
            foreach ($prestataires as $index => $prestataire) {
                // Nouveaux services de plomberie
                if ($plomberieCategory && $index % 3 == 0) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Installation de système de chauffage',
                        'description' => 'Installation de systèmes de chauffage central, radiateurs, plancher chauffant ou pompe à chaleur.',
                        'price' => 2500.00,
                        'delivery_time' => '1 semaine',
                        'status' => 'active',
                    ]);
                    
                    if ($chauffage) {
                        $service->categories()->attach([$plomberieCategory->id, $chauffage->id]);
                    } else {
                        $service->categories()->attach($plomberieCategory->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Débouchage de canalisations',
                        'description' => 'Débouchage de canalisations bouchées avec équipement professionnel, intervention rapide.',
                        'price' => 90.00,
                        'delivery_time' => 'Même jour',
                        'status' => 'active',
                    ]);
                    
                    if ($depannagePlomberie) {
                        $service->categories()->attach([$plomberieCategory->id, $depannagePlomberie->id]);
                    } else {
                        $service->categories()->attach($plomberieCategory->id);
                    }
                }
                
                // Nouveaux services d'électricité
                if ($electriciteCategory && $index % 3 == 1) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Rénovation électrique complète',
                        'description' => 'Rénovation complète du système électrique pour mise aux normes et sécurité optimale.',
                        'price' => 3000.00,
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
                        'title' => 'Installation de bornes de recharge véhicules électriques',
                        'description' => 'Installation de bornes de recharge pour véhicules électriques à domicile ou en entreprise.',
                        'price' => 1200.00,
                        'delivery_time' => '3 jours',
                        'status' => 'active',
                    ]);
                    
                    if ($installationElectrique) {
                        $service->categories()->attach([$electriciteCategory->id, $installationElectrique->id]);
                    } else {
                        $service->categories()->attach($electriciteCategory->id);
                    }
                }
                
                // Nouveaux services informatiques
                if ($informatique && $index % 3 == 2) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Création de site e-commerce',
                        'description' => 'Création complète de site e-commerce avec gestion des produits, paiement en ligne et administration.',
                        'price' => 2500.00,
                        'delivery_time' => '3 semaines',
                        'status' => 'active',
                    ]);
                    
                    if ($developpementWeb) {
                        $service->categories()->attach([$informatique->id, $developpementWeb->id]);
                    } else {
                        $service->categories()->attach($informatique->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Sécurisation de réseau d\'entreprise',
                        'description' => 'Audit et mise en place de solutions de sécurité pour réseaux d\'entreprise.',
                        'price' => 1800.00,
                        'delivery_time' => '1 semaine',
                        'status' => 'active',
                    ]);
                    
                    if ($reseaux) {
                        $service->categories()->attach([$informatique->id, $reseaux->id]);
                    } else {
                        $service->categories()->attach($informatique->id);
                    }
                }
                
                // Nouveaux services de graphisme
                if ($graphisme && $index % 4 == 0) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Création d\'identité visuelle complète',
                        'description' => 'Création d\'une identité visuelle complète pour votre entreprise : logo, charte graphique, papeterie.',
                        'price' => 1200.00,
                        'delivery_time' => '2 semaines',
                        'status' => 'active',
                    ]);
                    
                    $service->categories()->attach($graphisme->id);
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Création de packaging produit',
                        'description' => 'Conception et création de packaging pour vos produits, design attractif et fonctionnel.',
                        'price' => 800.00,
                        'delivery_time' => '10 jours',
                        'status' => 'active',
                    ]);
                    
                    $service->categories()->attach($graphisme->id);
                }
                
                // Nouveaux services de marketing
                if ($marketing && $index % 4 == 1) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Campagne publicitaire sur réseaux sociaux',
                        'description' => 'Création et gestion de campagnes publicitaires ciblées sur les réseaux sociaux.',
                        'price' => 500.00,
                        'delivery_time' => '1 semaine',
                        'status' => 'active',
                    ]);
                    
                    if ($marketingDigital) {
                        $service->categories()->attach([$marketing->id, $marketingDigital->id]);
                    } else {
                        $service->categories()->attach($marketing->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Optimisation SEO de site web',
                        'description' => 'Audit et optimisation SEO complète de votre site web pour améliorer son positionnement dans les moteurs de recherche.',
                        'price' => 900.00,
                        'delivery_time' => '2 semaines',
                        'status' => 'active',
                    ]);
                    
                    if ($seo) {
                        $service->categories()->attach([$marketing->id, $seo->id]);
                    } else {
                        $service->categories()->attach($marketing->id);
                    }
                }
                
                // Nouveaux services de menuiserie
                if ($menuiserie && $index % 4 == 2) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Installation de cuisine sur mesure',
                        'description' => 'Conception, fabrication et installation de cuisine entièrement sur mesure selon vos besoins et votre espace.',
                        'price' => 5000.00,
                        'delivery_time' => '1 mois',
                        'status' => 'active',
                    ]);
                    
                    if ($menuiserieInterieure) {
                        $service->categories()->attach([$menuiserie->id, $menuiserieInterieure->id]);
                    } else {
                        $service->categories()->attach($menuiserie->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Construction de terrasse en bois',
                        'description' => 'Construction de terrasse en bois sur mesure, traitement et finition inclus.',
                        'price' => 180.00, // prix au m²
                        'delivery_time' => '1 semaine',
                        'status' => 'active',
                    ]);
                    
                    if ($menuiserieExterieure) {
                        $service->categories()->attach([$menuiserie->id, $menuiserieExterieure->id]);
                    } else {
                        $service->categories()->attach($menuiserie->id);
                    }
                }
                
                // Nouveaux services de peinture
                if ($peinture && $index % 4 == 3) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Peinture décorative et effets spéciaux',
                        'description' => 'Réalisation de peintures décoratives avec effets spéciaux : tadelakt, béton ciré, patines, etc.',
                        'price' => 45.00, // prix au m²
                        'delivery_time' => 'Variable selon surface',
                        'status' => 'active',
                    ]);
                    
                    if ($peintureInterieure) {
                        $service->categories()->attach([$peinture->id, $peintureInterieure->id]);
                    } else {
                        $service->categories()->attach($peinture->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Ravalement de façade',
                        'description' => 'Ravalement complet de façade : nettoyage, réparation, peinture ou enduit.',
                        'price' => 35.00, // prix au m²
                        'delivery_time' => 'Variable selon surface',
                        'status' => 'active',
                    ]);
                    
                    if ($peintureExterieure) {
                        $service->categories()->attach([$peinture->id, $peintureExterieure->id]);
                    } else {
                        $service->categories()->attach($peinture->id);
                    }
                }
                
                // Nouveaux services de maçonnerie
                if ($maconnerie && $index % 5 == 0) {
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Construction d\'extension de maison',
                        'description' => 'Construction d\'extension de maison clé en main : fondations, murs, toiture, isolation, finitions.',
                        'price' => 1500.00, // prix au m²
                        'delivery_time' => '2-3 mois',
                        'status' => 'active',
                    ]);
                    
                    if ($construction) {
                        $service->categories()->attach([$maconnerie->id, $construction->id]);
                    } else {
                        $service->categories()->attach($maconnerie->id);
                    }
                    
                    $service = Service::create([
                        'prestataire_id' => $prestataire->id,
                        'title' => 'Création d\'allée de jardin en pavés',
                        'description' => 'Création d\'allée de jardin en pavés, préparation du terrain, pose et finitions.',
                        'price' => 120.00, // prix au m²
                        'delivery_time' => '1 semaine',
                        'status' => 'active',
                    ]);
                    
                    if ($construction) {
                        $service->categories()->attach([$maconnerie->id, $construction->id]);
                    } else {
                        $service->categories()->attach($maconnerie->id);
                    }
                }
            }
        }
    }
}