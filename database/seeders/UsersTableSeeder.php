<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création d'un administrateur
        $admin = User::firstOrCreate(['email' => 'admin@taprestation.com'],[
            'name' => 'Admin Test',
            'email' => 'admin@taprestation.com',
            'password' => Hash::make('Password@123'),
            'role' => 'administrateur',
        ]);
        
        // Création d'un second administrateur
        $admin2 = User::firstOrCreate(['email' => 'admin2@taprestation.com'],[
            'name' => 'Admin Système',
            'email' => 'admin2@taprestation.com',
            'password' => Hash::make('Password@123'),
            'role' => 'administrateur',
        ]);
        
        // Création de clients
        $client1 = User::firstOrCreate(['email' => 'client1@example.com'],[
            'name' => 'Client Test 1',
            'email' => 'client1@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'client',
        ]);
        
        Client::firstOrCreate(['user_id' => $client1->id],[
            'user_id' => $client1->id,
            'avatar' => null,
            'location' => 'Paris, France',
        ]);
        
        $client2 = User::firstOrCreate(['email' => 'client2@example.com'],[
            'name' => 'Client Test 2',
            'email' => 'client2@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'client',
        ]);
        
        $client3 = User::firstOrCreate(['email' => 'client3@example.com'],[
            'name' => 'Client Test 3',
            'email' => 'client3@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'client',
        ]);
        
        Client::firstOrCreate(['user_id' => $client2->id],[
            'user_id' => $client2->id,
            'avatar' => null,
            'location' => 'Lyon, France',
        ]);

        Client::firstOrCreate(['user_id' => $client3->id],[
            'user_id' => $client3->id,
            'avatar' => null,
            'location' => 'Marseille, France',
        ]);
        
        // Création de prestataires
        $prestataire1 = User::firstOrCreate(['email' => 'prestataire1@example.com'],[
            'name' => 'Prestataire Approuvé',
            'email' => 'prestataire1@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'prestataire',
        ]);
        
        Prestataire::firstOrCreate(['user_id' => $prestataire1->id],[
            'user_id' => $prestataire1->id,
            'company_name' => 'WebDev Pro',
            'description' => "Développeur web avec 5 ans d'expérience dans la création de sites web et d'applications.",
            'phone' => '+33123456789',
            'address' => '123 Rue de la Tech',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'France',
            'service_radius_km' => 50,
            'website' => "https://portfolio-prestataire1.com",
            'years_experience' => 5,
            'hourly_rate_min' => 40.00,
            'hourly_rate_max' => 60.00,
            'rating_average' => 4.8,
            'total_reviews' => 25,
            'total_projects' => 45,
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);
        
        $prestataire2 = User::firstOrCreate(['email' => 'prestataire2@example.com'],[
            'name' => 'Prestataire En Attente',
            'email' => 'prestataire2@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'prestataire',
        ]);
        
        Prestataire::firstOrCreate(['user_id' => $prestataire2->id],[
            'user_id' => $prestataire2->id,
            'company_name' => 'Plume & Style',
            'description' => "Rédacteur professionnel spécialisé dans la création de contenu pour sites web et blogs.",
            'phone' => '+33987654321',
            'address' => '456 Avenue des Lettres',
            'city' => 'Lyon',
            'postal_code' => '69001',
            'country' => 'France',
            'service_radius_km' => 100,
            'website' => "https://portfolio-prestataire2.com",
            'years_experience' => 3,
            'hourly_rate_min' => 25.00,
            'hourly_rate_max' => 35.00,
            'rating_average' => 4.5,
            'total_reviews' => 12,
            'total_projects' => 28,
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);
        
        $prestataire3 = User::firstOrCreate(['email' => 'prestataire3@example.com'],[
            'name' => 'Prestataire En Attente 2',
            'email' => 'prestataire3@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'prestataire',
        ]);
        
        // Création d'un prestataire supplémentaire
        $prestataire4 = User::firstOrCreate(['email' => 'prestataire4@example.com'],[
            'name' => 'Prestataire Expert',
            'email' => 'prestataire4@example.com',
            'password' => Hash::make('Password@123'),
            'role' => 'prestataire',
        ]);
        
        Prestataire::firstOrCreate(['user_id' => $prestataire4->id],[
            'user_id' => $prestataire4->id,
            'company_name' => 'Expert Solutions',
            'description' => "Consultant en informatique avec 10 ans d'expérience dans le développement de solutions d'entreprise.",
            'phone' => '+33678901234',
            'address' => '101 Rue de l\'Innovation',
            'city' => 'Bordeaux',
            'postal_code' => '33000',
            'country' => 'France',
            'service_radius_km' => 80,
            'website' => "https://portfolio-prestataire4.com",
            'years_experience' => 10,
            'hourly_rate_min' => 60.00,
            'hourly_rate_max' => 90.00,
            'rating_average' => 4.9,
            'total_reviews' => 30,
            'total_projects' => 55,
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);
        
        Prestataire::firstOrCreate(['user_id' => $prestataire3->id],[
            'user_id' => $prestataire3->id,
            'company_name' => 'Visual Studio Pro',
            'description' => "Photographe et vidéaste avec une expérience de 7 ans dans la création de contenu visuel.",
            'phone' => '+33456789123',
            'address' => '789 Boulevard Créatif',
            'city' => 'Marseille',
            'postal_code' => '13001',
            'country' => 'France',
            'service_radius_km' => 30,
            'website' => "https://portfolio-prestataire3.com",
            'years_experience' => 7,
            'hourly_rate_min' => 40.00,
            'hourly_rate_max' => 50.00,
            'rating_average' => 4.9,
            'total_reviews' => 18,
            'total_projects' => 35,
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }
}