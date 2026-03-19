<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Service;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Models\Booking;
use App\Models\Message;
use App\Models\Category;
use App\Models\Client;
use App\Models\PrestataireAvailability;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HaythamPrestataireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou récupérer l'utilisateur Haytham
        $haythamUser = User::firstOrCreate(
            ['email' => 'Haythamprestataire@gmail.com'],
            [
                'name' => 'Haytham Sebbar',
                'email' => 'Haythamprestataire@gmail.com',
                'password' => Hash::make('Password@123'),
                'role' => 'prestataire',
                'email_verified_at' => now(),
            ]
        );

        // Créer le profil prestataire
        $haythamPrestataire = Prestataire::firstOrCreate(
            ['user_id' => $haythamUser->id],
            [
                'user_id' => $haythamUser->id,
                'company_name' => 'Haytham Tech Solutions',
                'description' => 'Expert en développement web et mobile avec plus de 8 ans d\'expérience. Spécialisé dans Laravel, React, Vue.js et les solutions e-commerce.',
                'phone' => '+212 6 12 34 56 78',
                'address' => '123 Avenue Mohammed V',
                'city' => 'Casablanca',
                'postal_code' => '20000',
                'country' => 'Maroc',
                'service_radius_km' => 100,
                'website' => 'https://haytham-tech.com',
                'years_experience' => 8,
                'hourly_rate_min' => 50.00,
                'hourly_rate_max' => 80.00,
                'rating_average' => 4.9,
                'total_reviews' => 45,
                'total_projects' => 120,
                'is_approved' => true,
                'approved_at' => now(),
                'secteur_activite' => 'Informatique et Technologies',
                'competences' => 'PHP, Laravel, JavaScript, React, Vue.js, Node.js, MySQL, MongoDB',
                'latitude' => 33.5731,
                'longitude' => -7.5898,
            ]
        );

        // Récupérer les catégories
        $webDevCategory = Category::where('name', 'LIKE', '%Développement%web%')->first() 
                         ?? Category::where('name', 'LIKE', '%Informatique%')->first();
        $designCategory = Category::where('name', 'LIKE', '%Design%')->first();
        $marketingCategory = Category::where('name', 'LIKE', '%Marketing%')->first();

        // Créer des services
        $services = [
            [
                'title' => 'Développement de site web e-commerce',
                'description' => 'Création complète de boutiques en ligne avec Laravel et Vue.js. Intégration de systèmes de paiement, gestion des stocks, tableau de bord administrateur.',
                'price' => 2500.00,
                'delivery_time' => '3-4 semaines',
                'status' => 'active',
            ],
            [
                'title' => 'Application mobile React Native',
                'description' => 'Développement d\'applications mobiles cross-platform avec React Native. Interface moderne, performances optimisées, intégration API.',
                'price' => 3000.00,
                'delivery_time' => '4-6 semaines',
                'status' => 'active',
            ],
            [
                'title' => 'Refonte et modernisation de site web',
                'description' => 'Modernisation de sites existants avec les dernières technologies. Amélioration des performances, responsive design, SEO optimisé.',
                'price' => 1500.00,
                'delivery_time' => '2-3 semaines',
                'status' => 'active',
            ],
            [
                'title' => 'Consultation technique et audit',
                'description' => 'Audit technique de vos projets web, recommandations d\'amélioration, consultation sur l\'architecture et les bonnes pratiques.',
                'price' => 500.00,
                'delivery_time' => '3-5 jours',
                'status' => 'active',
            ],
            [
                'title' => 'Formation développement web',
                'description' => 'Formation personnalisée en développement web (Laravel, JavaScript, React). Sessions individuelles ou en groupe.',
                'price' => 800.00,
                'delivery_time' => '1-2 semaines',
                'status' => 'active',
            ],
        ];

        foreach ($services as $serviceData) {
            $service = Service::firstOrCreate(
                [
                    'prestataire_id' => $haythamPrestataire->id,
                    'title' => $serviceData['title']
                ],
                array_merge($serviceData, ['prestataire_id' => $haythamPrestataire->id])
            );
            
            // Attacher la catégorie si elle existe
            if ($webDevCategory && $service->wasRecentlyCreated) {
                $service->categories()->attach($webDevCategory->id);
            }
        }

        // Récupérer les catégories d'équipement
        $informaticCategory = Category::where('name', 'LIKE', '%Informatique%')->first()
                            ?? Category::where('name', 'LIKE', '%Ordinateur%')->first();

        // Créer des équipements
        $equipments = [
            [
                'name' => 'MacBook Pro 16" M2 Pro',
                'slug' => 'macbook-pro-16-m2-pro',
                'description' => 'MacBook Pro 16 pouces avec puce M2 Pro, 32GB RAM, 1TB SSD. Parfait pour le développement et le design. Excellent état.',
                'price_per_day' => 80.00,
                'price_per_week' => 500.00,
                'condition' => 'excellent',
                'status' => 'active',
                'city' => 'Casablanca',
                'country' => 'Maroc',
                'address' => '123 Avenue Mohammed V',
                'postal_code' => '20000',
                'minimum_rental_duration' => 1,
                'maximum_rental_duration' => 30,
            ],
            [
                'name' => 'Station de travail Dell Precision',
                'slug' => 'station-travail-dell-precision',
                'description' => 'Station de travail haute performance Dell Precision 7760. Intel i9, 64GB RAM, RTX A4000. Idéale pour le rendu 3D et développement.',
                'price_per_day' => 120.00,
                'price_per_week' => 750.00,
                'condition' => 'good',
                'status' => 'active',
                'city' => 'Casablanca',
                'country' => 'Maroc',
                'address' => '123 Avenue Mohammed V',
                'postal_code' => '20000',
                'minimum_rental_duration' => 1,
                'maximum_rental_duration' => 30,
            ],
            [
                'name' => 'Écran 4K Dell UltraSharp 32"',
                'slug' => 'ecran-4k-dell-ultrasharp-32',
                'description' => 'Moniteur professionnel 4K 32 pouces Dell UltraSharp. Calibrage colorimétrique précis, parfait pour le design et développement.',
                'price_per_day' => 25.00,
                'price_per_week' => 150.00,
                'condition' => 'good',
                'status' => 'active',
                'city' => 'Casablanca',
                'country' => 'Maroc',
                'address' => '123 Avenue Mohammed V',
                'postal_code' => '20000',
                'minimum_rental_duration' => 1,
                'maximum_rental_duration' => 7,
            ],
        ];

        foreach ($equipments as $equipmentData) {
            $equipment = Equipment::firstOrCreate(
                [
                    'prestataire_id' => $haythamPrestataire->id,
                    'name' => $equipmentData['name']
                ],
                array_merge($equipmentData, [
                    'prestataire_id' => $haythamPrestataire->id,
                    'delivery_fee' => 15.00,
                    'security_deposit' => $equipmentData['price_per_day'] * 7,
                    'is_active' => true,
                ])
            );
            
            // Assigner la catégorie si elle existe
            if ($informaticCategory && $equipment->wasRecentlyCreated) {
                if ($informaticCategory->parent_id) {
                    // C'est une sous-catégorie
                    $equipment->update([
                        'category_id' => $informaticCategory->parent_id,
                        'subcategory_id' => $informaticCategory->id
                    ]);
                } else {
                    // C'est une catégorie principale
                    $equipment->update([
                        'category_id' => $informaticCategory->id
                    ]);
                }
            }
        }

        // Créer des annonces
        $urgentSales = [
            [
                'title' => 'Serveurs HP ProLiant - Liquidation datacenter',
                'description' => 'Lot de 5 serveurs HP ProLiant DL380 Gen10. Intel Xeon, 32GB RAM chacun, disques SSD. Cause fermeture datacenter, vente urgente.',
                'price' => 8500.00,
                'condition' => 'good',
                'quantity' => 5,
                'location' => 'Casablanca',
                'status' => 'active',
                'photos' => ['server1.jpg', 'server2.jpg'],
            ],
            [
                'title' => 'Équipement réseau Cisco - Déménagement',
                'description' => 'Switches Cisco Catalyst, routeurs, points d\'accès WiFi. Matériel professionnel en excellent état. Vente cause déménagement.',
                'price' => 3200.00,
                'condition' => 'excellent',
                'quantity' => 1,
                'location' => 'Casablanca',
                'status' => 'active',
                'photos' => ['cisco1.jpg', 'cisco2.jpg'],
            ],
            [
                'title' => 'Mobilier bureau moderne - Liquidation',
                'description' => 'Bureaux ergonomiques, chaises Herman Miller, armoires de rangement. Mobilier de bureau haut de gamme, excellent état.',
                'price' => 1800.00,
                'condition' => 'excellent',
                'quantity' => 10,
                'location' => 'Casablanca',
                'status' => 'active',
                'photos' => ['mobilier1.jpg', 'mobilier2.jpg'],
            ],
        ];

        foreach ($urgentSales as $saleData) {
            UrgentSale::firstOrCreate(
                [
                    'prestataire_id' => $haythamPrestataire->id,
                    'title' => $saleData['title']
                ],
                array_merge($saleData, ['prestataire_id' => $haythamPrestataire->id])
            );
        }

        // Créer des clients pour les réservations et messages
        $clients = [];
        $clientsData = [
            [
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.benali@example.com',
                'location' => 'Rabat',
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@example.com',
                'location' => 'Casablanca',
            ],
            [
                'name' => 'Youssef Alami',
                'email' => 'youssef.alami@example.com',
                'location' => 'Marrakech',
            ],
        ];

        foreach ($clientsData as $clientData) {
            $clientUser = User::firstOrCreate(
                ['email' => $clientData['email']],
                [
                    'name' => $clientData['name'],
                    'email' => $clientData['email'],
                    'password' => Hash::make('Password@123'),
                    'role' => 'client',
                    'email_verified_at' => now(),
                ]
            );

            $client = Client::firstOrCreate(
                ['user_id' => $clientUser->id],
                [
                    'user_id' => $clientUser->id,
                    'location' => $clientData['location'],
                ]
            );

            $clients[] = $clientUser;
        }

        // Créer des réservations
        $services = Service::where('prestataire_id', $haythamPrestataire->id)->get();
        if ($services->isNotEmpty() && !empty($clients)) {
            $bookings = [
                [
                    'client_id' => $clients[0]->client->id,
                    'service_id' => $services->first()->id,
                    'start_datetime' => Carbon::now()->addDays(7)->setTime(10, 0),
                    'end_datetime' => Carbon::now()->addDays(7)->setTime(12, 0),
                    'status' => 'confirmed',
                    'total_price' => $services->first()->price,
                    'client_notes' => 'Projet de site e-commerce pour boutique de vêtements',
                ],
                [
                    'client_id' => $clients[1]->client->id,
                    'service_id' => $services->skip(1)->first()?->id ?? $services->first()->id,
                    'start_datetime' => Carbon::now()->addDays(14)->setTime(14, 0),
                    'end_datetime' => Carbon::now()->addDays(14)->setTime(16, 0),
                    'status' => 'pending',
                    'total_price' => $services->skip(1)->first()?->price ?? $services->first()->price,
                    'client_notes' => 'Application mobile pour gestion de restaurant',
                ],
                [
                    'client_id' => $clients[2]->client->id,
                    'service_id' => $services->skip(2)->first()?->id ?? $services->first()->id,
                    'start_datetime' => Carbon::now()->subDays(5)->setTime(9, 0),
                    'end_datetime' => Carbon::now()->subDays(5)->setTime(11, 0),
                    'status' => 'completed',
                    'total_price' => $services->skip(2)->first()?->price ?? $services->first()->price,
                    'client_notes' => 'Refonte du site web de l\'entreprise',
                ],
            ];

            foreach ($bookings as $bookingData) {
                if ($bookingData['service_id']) {
                    Booking::firstOrCreate(
                        [
                            'prestataire_id' => $haythamPrestataire->id,
                            'client_id' => $bookingData['client_id'],
                            'service_id' => $bookingData['service_id'],
                            'start_datetime' => $bookingData['start_datetime'],
                        ],
                        array_merge($bookingData, ['prestataire_id' => $haythamPrestataire->id])
                    );
                }
            }
        }

        // Créer des messages
        if (!empty($clients)) {
            $messages = [
                [
                    'sender_id' => $clients[0]->id,
                    'receiver_id' => $haythamUser->id,
                    'content' => 'Bonjour Haytham, je suis intéressé par vos services de développement e-commerce. Pourriez-vous me donner plus d\'informations ?',
                    'created_at' => Carbon::now()->subDays(2),
                    'read_at' => Carbon::now()->subDays(2)->addHours(1),
                ],
                [
                    'sender_id' => $haythamUser->id,
                    'receiver_id' => $clients[0]->id,
                    'content' => 'Bonjour Ahmed ! Merci pour votre message. Je serais ravi de discuter de votre projet e-commerce. Quel type de produits souhaitez-vous vendre ?',
                    'created_at' => Carbon::now()->subDays(2)->addHours(1),
                    'read_at' => Carbon::now()->subDays(2)->addHours(2),
                ],
                [
                    'sender_id' => $clients[1]->id,
                    'receiver_id' => $haythamUser->id,
                    'content' => 'Salut ! J\'ai vu votre profil et j\'aimerais développer une application mobile pour mon restaurant. Êtes-vous disponible ?',
                    'created_at' => Carbon::now()->subDays(1),
                    'read_at' => null,
                ],
                [
                    'sender_id' => $clients[2]->id,
                    'receiver_id' => $haythamUser->id,
                    'content' => 'Excellent travail sur la refonte de notre site ! L\'équipe est très satisfaite du résultat. Merci beaucoup !',
                    'created_at' => Carbon::now()->subDays(3),
                    'read_at' => Carbon::now()->subDays(3)->addMinutes(30),
                ],
                [
                    'sender_id' => $haythamUser->id,
                    'receiver_id' => $clients[2]->id,
                    'content' => 'Merci beaucoup Youssef ! C\'était un plaisir de travailler avec vous. N\'hésitez pas à me recontacter pour vos futurs projets.',
                    'created_at' => Carbon::now()->subDays(3)->addMinutes(30),
                    'read_at' => Carbon::now()->subDays(3)->addHours(1),
                ],
            ];

            foreach ($messages as $messageData) {
                Message::firstOrCreate(
                    [
                        'sender_id' => $messageData['sender_id'],
                        'receiver_id' => $messageData['receiver_id'],
                        'content' => $messageData['content'],
                        'created_at' => $messageData['created_at'],
                    ],
                    $messageData
                );
            }
        }

        // Configurer les disponibilités
        $availabilities = [
            [
                'day_of_week' => 1, // Lundi
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_active' => true,
            ],
            [
                'day_of_week' => 2, // Mardi
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_active' => true,
            ],
            [
                'day_of_week' => 3, // Mercredi
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_active' => true,
            ],
            [
                'day_of_week' => 4, // Jeudi
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_active' => true,
            ],
            [
                'day_of_week' => 5, // Vendredi
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_active' => true,
            ],
            [
                'day_of_week' => 6, // Samedi
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
                'is_active' => true,
            ],
            [
                'day_of_week' => 0, // Dimanche
                'start_time' => null,
                'end_time' => null,
                'is_active' => false,
            ],
        ];

        foreach ($availabilities as $availabilityData) {
            PrestataireAvailability::firstOrCreate(
                [
                    'prestataire_id' => $haythamPrestataire->id,
                    'day_of_week' => $availabilityData['day_of_week'],
                ],
                array_merge($availabilityData, ['prestataire_id' => $haythamPrestataire->id])
            );
        }

        $this->command->info('Données de test créées avec succès pour Haytham Prestataire !');
        $this->command->info('Email: Haythamprestataire@gmail.com');
        $this->command->info('Mot de passe: Password@123');
        $this->command->info('Services créés: ' . $haythamPrestataire->services()->count());
        $this->command->info('Équipements créés: ' . $haythamPrestataire->equipments()->count());
        $this->command->info('Annonces créées: ' . $haythamPrestataire->urgentSales()->count());
        $this->command->info('Réservations créées: ' . $haythamPrestataire->bookings()->count());
    }
}