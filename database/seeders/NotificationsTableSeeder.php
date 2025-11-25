<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Booking;
use App\Notifications\NewMessageNotification;
use App\Notifications\NewOfferNotification;
use App\Notifications\OfferAcceptedNotification;
use App\Notifications\OfferRejectedNotification;
use App\Notifications\NewReviewNotification;
use App\Notifications\PrestataireApprovedNotification;
use App\Notifications\AnnouncementStatusNotification;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\MissionCompletedNotification;
use App\Notifications\NewBookingNotification;
use App\Notifications\NewEquipmentRentalRequestNotification;
use Carbon\Carbon;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider les notifications existantes
        Notification::truncate();
        
        // Récupération des utilisateurs
        $clients = User::where('role', 'client')->get();
        $prestataires = User::where('role', 'prestataire')->get();
        $admin = User::where('role', 'administrateur')->first();
        
        // Récupération des données pour les notifications
        $bookings = Booking::all();
        
        $this->command->info('Création de notifications de test...');
        
        // Notifications pour les clients
        foreach ($clients->take(5) as $index => $client) {
            $this->createClientNotifications($client, $index, $prestataires, $bookings);
        }
        
        // Notifications pour les prestataires
        foreach ($prestataires->take(5) as $index => $prestataire) {
            $this->createPrestataireNotifications($prestataire, $index, $clients, $bookings);
        }
        
        // Notifications pour l'administrateur
        if ($admin) {
            $this->createAdminNotifications($admin, $clients, $prestataires);
        }
        
        $this->command->info('Notifications de test créées avec succès !');
    }
    
    /**
     * Créer des notifications pour un client
     */
    private function createClientNotifications($client, $index, $prestataires, $bookings)
    {
        // 1. Notification de bienvenue
        Notification::create([
            'type' => 'welcome',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $client->id,
            'data' => [
                'title' => 'Bienvenue sur TaPrestation !',
                'message' => 'Merci de vous être inscrit sur notre plateforme. Découvrez nos prestataires qualifiés et publiez vos demandes.',
                'type' => 'welcome'
            ],
            'read_at' => $index === 0 ? null : Carbon::now()->subDays(rand(1, 5)),
            'created_at' => Carbon::now()->subDays(rand(5, 10)),
            'updated_at' => Carbon::now()->subDays(rand(1, 5)),
        ]);
        
        // 2. Message reçu
        if (!$prestataires->isEmpty()) {
            $randomPrestataire = $prestataires->random();
            Notification::create([
                'type' => NewMessageNotification::class,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $client->id,
                'data' => [
                    'title' => 'Nouveau message',
                    'message' => 'Vous avez reçu un nouveau message de ' . $randomPrestataire->name . '. Consultez votre messagerie pour y répondre.',
                    'sender_name' => $randomPrestataire->name,
                    'sender_id' => $randomPrestataire->id,
                    'url' => '/messages',
                    'type' => 'new_message'
                ],
                'read_at' => $index % 3 === 0 ? null : Carbon::now()->subHours(rand(1, 24)),
                'created_at' => Carbon::now()->subDays(rand(1, 3)),
                'updated_at' => Carbon::now()->subHours(rand(1, 24)),
            ]);
        }
        
        // 3. Réservation confirmée
        if (!$prestataires->isEmpty()) {
            $randomPrestataire = $prestataires->random();
            Notification::create([
                'type' => BookingConfirmedNotification::class,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $client->id,
                'data' => [
                    'title' => 'Réservation confirmée',
                    'message' => 'Votre réservation avec ' . $randomPrestataire->name . ' a été confirmée pour le ' . Carbon::now()->addDays(rand(1, 14))->format('d/m/Y') . '.',
                    'prestataire_name' => $randomPrestataire->name,
                    'booking_date' => Carbon::now()->addDays(rand(1, 14))->format('d/m/Y'),
                    'url' => '/client/bookings',
                    'type' => 'booking_confirmed'
                ],
                'read_at' => $index % 4 === 0 ? null : Carbon::now()->subHours(rand(2, 48)),
                'created_at' => Carbon::now()->subHours(rand(2, 48)),
                'updated_at' => Carbon::now()->subHours(rand(2, 48)),
            ]);
        }
        
        // 4. Mission terminée
        if (!$prestataires->isEmpty() && $index < 3) {
            $randomPrestataire = $prestataires->random();
            Notification::create([
                'type' => MissionCompletedNotification::class,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $client->id,
                'data' => [
                    'title' => 'Mission terminée',
                    'message' => $randomPrestataire->name . ' a marqué votre mission comme terminée. N\'oubliez pas de laisser un avis !',
                    'prestataire_name' => $randomPrestataire->name,
                    'url' => '/client/missions',
                    'type' => 'mission_completed'
                ],
                'read_at' => null,
                'created_at' => Carbon::now()->subHours(rand(1, 12)),
                'updated_at' => Carbon::now()->subHours(rand(1, 12)),
            ]);
        }
    }
    
    /**
     * Créer des notifications pour un prestataire
     */
    private function createPrestataireNotifications($prestataire, $index, $clients, $bookings)
    {
        // 1. Notification de bienvenue
        Notification::create([
            'type' => 'welcome',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $prestataire->id,
            'data' => [
                'title' => 'Bienvenue chez TaPrestation !',
                'message' => 'Merci de rejoindre notre communauté de prestataires. Complétez votre profil pour recevoir vos premières demandes.',
                'type' => 'welcome'
            ],
            'read_at' => $index === 0 ? null : Carbon::now()->subDays(rand(1, 5)),
            'created_at' => Carbon::now()->subDays(rand(5, 10)),
            'updated_at' => Carbon::now()->subDays(rand(1, 5)),
        ]);
        
        // 2. Nouvelle demande de service
        if (!$clients->isEmpty()) {
            $randomClient = $clients->random();
            $services = ['Développement web', 'Design graphique', 'Plomberie', 'Électricité', 'Jardinage', 'Nettoyage'];
            $service = $services[array_rand($services)];
            
            Notification::create([
                'type' => NewEquipmentRentalRequestNotification::class,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $prestataire->id,
                'data' => [
                    'title' => 'Nouvelle demande de service',
                    'message' => 'Une nouvelle demande pour "' . $service . '" de ' . $randomClient->name . ' correspond à vos compétences. Budget: ' . (300 + $index * 150) . '€',
                    'client_name' => $randomClient->name,
                    'service_title' => $service,
                    'budget' => 300 + $index * 150,
                    'url' => '/prestataire/requests',
                    'type' => 'new_request'
                ],
                'read_at' => $index % 2 === 0 ? null : Carbon::now()->subDays(rand(1, 3)),
                'created_at' => Carbon::now()->subDays(rand(1, 7)),
                'updated_at' => Carbon::now()->subDays(rand(1, 3)),
            ]);
        }
        
        // 3. Nouvelle évaluation
        if (!$clients->isEmpty() && $index < 3) {
            $randomClient = $clients->random();
            Notification::create([
                'type' => NewReviewNotification::class,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $prestataire->id,
                'data' => [
                    'title' => 'Nouvelle évaluation',
                    'message' => $randomClient->name . ' vous a laissé une évaluation. Note: ' . (4 + $index % 2) . '/5',
                    'client_name' => $randomClient->name,
                    'rating' => 4 + $index % 2,
                    'url' => '/prestataire/reviews',
                    'type' => 'new_review'
                ],
                'read_at' => $index % 2 === 0 ? null : Carbon::now()->subHours(rand(1, 24)),
                'created_at' => Carbon::now()->subDays(rand(1, 3)),
                'updated_at' => Carbon::now()->subHours(rand(1, 24)),
            ]);
        }
        
        // 4. Réservation reçue
        if (!$clients->isEmpty()) {
            $randomClient = $clients->random();
            Notification::create([
                'type' => NewBookingNotification::class,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $prestataire->id,
                'data' => [
                    'title' => 'Nouvelle réservation',
                    'message' => $randomClient->name . ' a réservé vos services pour le ' . Carbon::now()->addDays(rand(2, 10))->format('d/m/Y') . '.',
                    'client_name' => $randomClient->name,
                    'booking_date' => Carbon::now()->addDays(rand(2, 10))->format('d/m/Y'),
                    'url' => '/prestataire/bookings',
                    'type' => 'new_booking'
                ],
                'read_at' => $index % 3 === 0 ? null : Carbon::now()->subHours(rand(1, 12)),
                'created_at' => Carbon::now()->subHours(rand(1, 12)),
                'updated_at' => Carbon::now()->subHours(rand(1, 12)),
            ]);
        }
        
        // 5. Compte approuvé
        if ($index === 0) {
            Notification::create([
                'type' => PrestataireApprovedNotification::class,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $prestataire->id,
                'data' => [
                    'title' => 'Compte approuvé',
                    'message' => 'Félicitations ! Votre compte prestataire a été approuvé. Vous pouvez maintenant recevoir des demandes de service.',
                    'url' => '/prestataire/dashboard',
                    'type' => 'account_approved'
                ],
                'read_at' => null,
                'created_at' => Carbon::now()->subDays(rand(1, 3)),
                'updated_at' => Carbon::now()->subDays(rand(1, 3)),
            ]);
        }
    }
    
    /**
     * Créer des notifications pour l'administrateur
     */
    private function createAdminNotifications($admin, $clients, $prestataires)
    {
        // 1. Nouvel utilisateur inscrit
        if (!$clients->isEmpty()) {
            $randomClient = $clients->random();
            Notification::create([
                'type' => 'new_user',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $admin->id,
                'data' => [
                    'title' => 'Nouvel utilisateur',
                    'message' => 'Un nouvel utilisateur (' . $randomClient->name . ') s\'est inscrit sur la plateforme.',
                    'user_name' => $randomClient->name,
                    'user_type' => 'client',
                    'url' => '/admin/users',
                    'type' => 'new_user'
                ],
                'read_at' => null,
                'created_at' => Carbon::now()->subHours(rand(1, 24)),
                'updated_at' => Carbon::now()->subHours(rand(1, 24)),
            ]);
        }
        
        // 2. Nouveau prestataire inscrit
        if (!$prestataires->isEmpty()) {
            $randomPrestataire = $prestataires->random();
            Notification::create([
                'type' => 'new_user',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $admin->id,
                'data' => [
                    'title' => 'Nouveau prestataire',
                    'message' => 'Un nouveau prestataire (' . $randomPrestataire->name . ') s\'est inscrit sur la plateforme.',
                    'user_name' => $randomPrestataire->name,
                    'user_type' => 'prestataire',
                    'url' => '/admin/users',
                    'type' => 'new_user'
                ],
                'read_at' => null,
                'created_at' => Carbon::now()->subHours(rand(1, 12)),
                'updated_at' => Carbon::now()->subHours(rand(1, 12)),
            ]);
        }
        
        // 3. Signalement d'annonce
        Notification::create([
            'type' => 'report',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $admin->id,
            'data' => [
                'title' => 'Nouveau signalement',
                'message' => 'Une annonce a été signalée par un utilisateur. Veuillez vérifier le contenu signalé.',
                'report_type' => 'urgent_sale',
                'url' => '/admin/reports',
                'type' => 'new_report'
            ],
            'read_at' => null,
            'created_at' => Carbon::now()->subHours(rand(2, 6)),
            'updated_at' => Carbon::now()->subHours(rand(2, 6)),
        ]);
    }
}