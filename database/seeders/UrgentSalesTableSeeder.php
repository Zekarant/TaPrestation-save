<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UrgentSale;
use App\Models\Prestataire;
use App\Models\User;
use Carbon\Carbon;

class UrgentSalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des prestataires de test si nécessaire
        $prestataires = $this->createTestPrestataires();
        
        // Créer des annonces de test
        $this->createTestUrgentSales($prestataires);
    }
    
    private function createTestPrestataires()
    {
        $prestataires = [];
        
        $prestatairesData = [
            [
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.benali@example.com',
                'company_name' => 'TechSolutions Maroc',
                'description' => 'Spécialiste en équipements informatiques et électroniques',
                'phone' => '06 12 34 56 78',
                'address' => '45 Avenue Mohammed V, Casablanca',
                'location' => 'Casablanca'
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@example.com',
                'company_name' => 'Mobilier Pro',
                'description' => 'Vente de mobilier de bureau et équipements professionnels',
                'phone' => '06 98 76 54 32',
                'address' => '12 Rue Allal Ben Abdellah, Rabat',
                'location' => 'Rabat'
            ],
            [
                'name' => 'Youssef Alami',
                'email' => 'youssef.alami@example.com',
                'company_name' => 'Équipements Industriels',
                'description' => 'Matériel industriel et outils professionnels',
                'phone' => '06 55 44 33 22',
                'address' => '78 Boulevard Zerktouni, Marrakech',
                'location' => 'Marrakech'
            ]
        ];
        
        foreach ($prestatairesData as $prestataireData) {
            $user = User::firstOrCreate(
                ['email' => $prestataireData['email']],
                [
                    'name' => $prestataireData['name'],
                    'email' => $prestataireData['email'],
                    'password' => bcrypt('password'),
                    'role' => 'prestataire',
                    'email_verified_at' => now()
                ]
            );
            
            $prestataire = Prestataire::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'company_name' => $prestataireData['company_name'],
                    'description' => $prestataireData['description'],
                    'phone' => $prestataireData['phone'],
                    'address' => $prestataireData['address'],
                    'is_approved' => true,
                    'is_active' => true
                ]
            );
            
            $prestataires[] = $prestataire;
        }
        
        return $prestataires;
    }
    
    private function createTestUrgentSales($prestataires)
    {
        $urgentSalesData = [
            [
                'title' => 'Ordinateurs portables Dell - Liquidation stock',
                'description' => 'Lot de 15 ordinateurs portables Dell Latitude 7420. Processeur Intel i5, 8GB RAM, SSD 256GB. Parfait état, garantie 6 mois. Cause: fermeture bureau.',
                'price' => 450.00,
                'condition' => 'very_good',
                'quantity' => 15,
                'location' => 'Casablanca',
                'status' => 'active',
                'photos' => ['laptop1.jpg', 'laptop2.jpg', 'laptop3.jpg'],
                'prestataire_index' => 0
            ],
            [
                'title' => 'Mobilier de bureau complet - Déménagement urgent',
                'description' => 'Ensemble mobilier bureau: 8 bureaux, 12 chaises ergonomiques, 4 armoires, 2 tables de réunion. Excellent état, bois massif. Vente rapide cause déménagement.',
                'price' => 1200.00,
                'condition' => 'excellent',
                'quantity' => 1,
                'location' => 'Rabat',
                'status' => 'active',
                'photos' => ['bureau1.jpg', 'bureau2.jpg'],
                'prestataire_index' => 1
            ],
            [
                'title' => 'Imprimantes multifonctions HP - Fin de contrat',
                'description' => '5 imprimantes HP LaserJet Pro MFP M428fdw. Impression, scan, copie, fax. Très bon état, peu utilisées. Cartouches incluses.',
                'price' => 280.00,
                'condition' => 'very_good',
                'quantity' => 5,
                'location' => 'Fès',
                'status' => 'active',
                'photos' => ['printer1.jpg', 'printer2.jpg'],
                'prestataire_index' => 0
            ],
            [
                'title' => 'Outils électroportatifs Bosch - Liquidation atelier',
                'description' => 'Lot complet outils Bosch: perceuses, visseuses, scies, ponceuses. État neuf à très bon. Mallettes et accessoires inclus. Liquidation atelier.',
                'price' => 850.00,
                'condition' => 'excellent',
                'quantity' => 1,
                'location' => 'Tanger',
                'status' => 'active',
                'photos' => ['tools1.jpg', 'tools2.jpg', 'tools3.jpg'],
                'prestataire_index' => 2
            ],
            [
                'title' => 'Écrans Dell 24 pouces - Renouvellement parc',
                'description' => '20 écrans Dell P2414H 24 pouces Full HD. Excellente qualité image, pivotants, réglables en hauteur. Cause: passage aux écrans 4K.',
                'price' => 120.00,
                'condition' => 'very_good',
                'quantity' => 20,
                'location' => 'Rabat',
                'status' => 'active',
                'photos' => ['screen1.jpg', 'screen2.jpg'],
                'prestataire_index' => 1
            ],
            [
                'title' => 'Serveur Dell PowerEdge - Mise à niveau infrastructure',
                'description' => 'Serveur Dell PowerEdge R740. 2x Intel Xeon, 64GB RAM, 4x 1TB SSD. Parfait pour PME. Vente cause migration cloud.',
                'price' => 2500.00,
                'condition' => 'very_good',
                'quantity' => 1,
                'location' => 'Casablanca',
                'status' => 'active',
                'photos' => ['server1.jpg', 'server2.jpg'],
                'prestataire_index' => 0
            ],
            [
                'title' => 'Matériel de soudure professionnel - Cessation activité',
                'description' => 'Poste de soudure MIG/MAG, chalumeau, masques automatiques, électrodes. Matériel professionnel peu utilisé. Vente rapide.',
                'price' => 1800.00,
                'condition' => 'very_good',
                'quantity' => 1,
                'location' => 'Marrakech',
                'status' => 'active',
                'photos' => ['soudure1.jpg', 'soudure2.jpg'],
                'prestataire_index' => 2
            ],
            [
                'title' => 'Téléphones IP Cisco - Changement système',
                'description' => '25 téléphones IP Cisco 7841. VoIP, écran couleur, qualité audio HD. Parfait état, configuration incluse.',
                'price' => 95.00,
                'condition' => 'very_good',
                'quantity' => 25,
                'location' => 'Agadir',
                'status' => 'active',
                'photos' => ['phone1.jpg', 'phone2.jpg'],
                'prestataire_index' => 1
            ]
        ];
        
        foreach ($urgentSalesData as $saleData) {
            $prestataire = $prestataires[$saleData['prestataire_index']];
            unset($saleData['prestataire_index']);
            
            UrgentSale::create(array_merge($saleData, [
                'prestataire_id' => $prestataire->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 7))
            ]));
        }
    }
}