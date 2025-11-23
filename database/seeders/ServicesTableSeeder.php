<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Category;

class ServicesTableSeeder extends Seeder
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
        $webDevCategory = Category::where('name', 'Développement Web')->first();
        $designCategory = Category::where('name', 'Design Graphique')->first() ?? Category::where('name', 'Design')->first();
        $writingCategory = Category::where('name', 'Rédaction et Contenu')->first() ?? Category::where('name', 'Rédaction')->first();
        $marketingCategory = Category::where('name', 'Marketing Digital')->first() ?? Category::where('name', 'Marketing')->first();
        $consultingCategory = Category::where('name', 'Consulting')->first() ?? Category::where('name', 'Conseil')->first();
        $mobileDevCategory = Category::where('name', 'Développement Mobile')->first();
        
        // Créer des services pour les prestataires approuvés
        if ($prestataires->isNotEmpty()) {
            // Services pour le premier prestataire approuvé
            $prestataire1 = $prestataires->where('is_approved', true)->first();
            
            if ($prestataire1) {
                // Service 1
                $service1 = Service::create([
                    'prestataire_id' => $prestataire1->id,
                    'title' => 'Création de site web responsive',
                    'description' => 'Je crée des sites web modernes, responsives et optimisés pour le SEO. Utilisation des dernières technologies (HTML5, CSS3, JavaScript).',
                    'price' => 500.00,
                    'delivery_time' => '2 semaines',
                    'status' => 'active',
                ]);
                
                if ($webDevCategory) {
                    $service1->categories()->attach($webDevCategory->id);
                }
                
                // Service 2
                $service2 = Service::create([
                    'prestataire_id' => $prestataire1->id,
                    'title' => 'Développement d\'application web sur mesure',
                    'description' => 'Développement d\'applications web personnalisées selon vos besoins spécifiques. Utilisation de frameworks modernes comme Laravel, Vue.js ou React.',
                    'price' => 1200.00,
                    'delivery_time' => '1 mois',
                    'status' => 'active',
                ]);
                
                if ($webDevCategory) {
                    $service2->categories()->attach($webDevCategory->id);
                }
                
                // Service 3
                $service3 = Service::create([
                    'prestataire_id' => $prestataire1->id,
                    'title' => 'Intégration de maquette design',
                    'description' => 'Transformation de vos maquettes PSD, Figma ou XD en code HTML/CSS responsive et optimisé.',
                    'price' => 300.00,
                    'delivery_time' => '1 semaine',
                    'status' => 'active',
                ]);
                
                if ($webDevCategory && $designCategory) {
                    $service3->categories()->attach([$webDevCategory->id, $designCategory->id]);
                }
            }
            
            // Créer des services pour le quatrième prestataire (approuvé)
            $prestataire4 = $prestataires->where('is_approved', true)->skip(1)->first();
            
            if ($prestataire4) {
                // Service 1
                $serviceP4_1 = Service::create([
                    'prestataire_id' => $prestataire4->id,
                    'title' => 'Consultation en stratégie d\'entreprise',
                    'description' => 'Analyse complète de votre entreprise et élaboration d\'une stratégie de développement adaptée à vos objectifs et au marché.',
                    'price' => 1500.00,
                    'delivery_time' => '2 semaines',
                    'status' => 'active',
                ]);
                
                if ($consultingCategory) {
                    $serviceP4_1->categories()->attach($consultingCategory->id);
                }
                
                // Service 2
                $serviceP4_2 = Service::create([
                    'prestataire_id' => $prestataire4->id,
                    'title' => 'Développement d\'applications mobiles',
                    'description' => 'Création d\'applications mobiles natives pour iOS et Android avec une expérience utilisateur optimale et des fonctionnalités personnalisées.',
                    'price' => 2500.00,
                    'delivery_time' => '6 semaines',
                    'status' => 'active',
                ]);
                
                if ($mobileDevCategory) {
                    $serviceP4_2->categories()->attach($mobileDevCategory->id);
                }
                
                // Service 3
                $serviceP4_3 = Service::create([
                    'prestataire_id' => $prestataire4->id,
                    'title' => 'Audit et optimisation de performance web',
                    'description' => 'Analyse complète de votre site web et mise en œuvre de solutions pour améliorer sa vitesse, son référencement et sa conversion.',
                    'price' => 800.00,
                    'delivery_time' => '1 semaine',
                    'status' => 'active',
                ]);
                
                if ($webDevCategory && $marketingCategory) {
                    $serviceP4_3->categories()->attach([$webDevCategory->id, $marketingCategory->id]);
                }
            }
            
            // Créer des services pour le deuxième prestataire (en attente)
            $prestataire2 = $prestataires->where('is_approved', false)->first();
            
            if ($prestataire2) {
                // Service 1
                $service4 = Service::create([
                    'prestataire_id' => $prestataire2->id,
                    'title' => 'Rédaction d\'articles SEO',
                    'description' => 'Rédaction d\'articles optimisés pour le référencement naturel. Recherche de mots-clés et structuration selon les bonnes pratiques SEO.',
                    'price' => 80.00,
                    'delivery_time' => '3 jours',
                    'status' => 'pending',
                ]);
                
                if ($writingCategory && $marketingCategory) {
                    $service4->categories()->attach([$writingCategory->id, $marketingCategory->id]);
                }
                
                // Service 2
                $service5 = Service::create([
                    'prestataire_id' => $prestataire2->id,
                    'title' => 'Traduction de contenu FR/EN',
                    'description' => 'Traduction professionnelle de vos contenus du français vers l\'anglais ou inversement. Respect du ton et adaptation culturelle.',
                    'price' => 0.10,
                    'delivery_time' => '5 jours',
                    'status' => 'pending',
                ]);
                
                if ($writingCategory) {
                    $service5->categories()->attach($writingCategory->id);
                }
            }
        }
    }
}