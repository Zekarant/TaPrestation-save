<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Message;
use Carbon\Carbon;

class MessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les utilisateurs existants
        $clients = User::where('role', 'client')->get();
        $prestataires = User::where('role', 'prestataire')->get();
        
        if ($clients->isEmpty() || $prestataires->isEmpty()) {
            $this->command->info('Aucun client ou prestataire trouvé. Veuillez d\'abord exécuter UsersTableSeeder.');
            return;
        }
        
        // Conversation 1: Client 1 avec Prestataire 1
        $client1 = $clients->where('email', 'client1@example.com')->first();
        $prestataire1 = $prestataires->where('email', 'prestataire1@example.com')->first();
        
        if ($client1 && $prestataire1) {
            $messages1 = [
                [
                    'sender_id' => $client1->id,
                    'receiver_id' => $prestataire1->id,
                    'content' => 'Bonjour, j\'ai vu votre profil et je suis intéressé par vos services de développement web.',
                    'created_at' => Carbon::now()->subDays(3)->subHours(2),
                    'read_at' => Carbon::now()->subDays(3)->subHours(1),
                ],
                [
                    'sender_id' => $prestataire1->id,
                    'receiver_id' => $client1->id,
                    'content' => 'Bonjour ! Merci pour votre message. Je serais ravi de discuter de votre projet. Pouvez-vous me donner plus de détails ?',
                    'created_at' => Carbon::now()->subDays(3)->subHour(),
                    'read_at' => Carbon::now()->subDays(3)->subMinutes(30),
                ],
                [
                    'sender_id' => $client1->id,
                    'receiver_id' => $prestataire1->id,
                    'content' => 'Je souhaite créer un site e-commerce pour ma boutique. J\'ai besoin d\'un design moderne et d\'une interface utilisateur intuitive.',
                    'created_at' => Carbon::now()->subDays(3)->subMinutes(25),
                    'read_at' => Carbon::now()->subDays(3)->subMinutes(10),
                ],
                [
                    'sender_id' => $prestataire1->id,
                    'receiver_id' => $client1->id,
                    'content' => 'Parfait ! J\'ai de l\'expérience avec les sites e-commerce. Quel est votre budget et votre délai ?',
                    'created_at' => Carbon::now()->subDays(3)->subMinutes(5),
                    'read_at' => null, // Message non lu
                ],
            ];
            
            foreach ($messages1 as $messageData) {
                Message::create($messageData);
            }
        }
        
        // Conversation 2: Client 2 avec Prestataire 4
        $client2 = $clients->where('email', 'client2@example.com')->first();
        $prestataire4 = $prestataires->where('email', 'prestataire4@example.com')->first();
        
        if ($client2 && $prestataire4) {
            $messages2 = [
                [
                    'sender_id' => $client2->id,
                    'receiver_id' => $prestataire4->id,
                    'content' => 'Salut ! J\'ai besoin d\'aide pour optimiser mon système informatique d\'entreprise.',
                    'created_at' => Carbon::now()->subDays(1)->subHours(4),
                    'read_at' => Carbon::now()->subDays(1)->subHours(3),
                ],
                [
                    'sender_id' => $prestataire4->id,
                    'receiver_id' => $client2->id,
                    'content' => 'Bonjour ! Je peux certainement vous aider avec l\'optimisation de votre système. Quels sont les problèmes que vous rencontrez actuellement ?',
                    'created_at' => Carbon::now()->subDays(1)->subHours(3),
                    'read_at' => Carbon::now()->subDays(1)->subHours(2),
                ],
                [
                    'sender_id' => $client2->id,
                    'receiver_id' => $prestataire4->id,
                    'content' => 'Notre réseau est lent et nous avons des problèmes de sécurité. Nous avons aussi besoin de migrer vers le cloud.',
                    'created_at' => Carbon::now()->subDays(1)->subHours(2),
                    'read_at' => Carbon::now()->subDays(1)->subHour(),
                ],
                [
                    'sender_id' => $prestataire4->id,
                    'receiver_id' => $client2->id,
                    'content' => 'Je comprends. Je peux faire un audit complet de votre infrastructure et proposer des solutions. Quand pouvons-nous planifier une réunion ?',
                    'created_at' => Carbon::now()->subDays(1)->subMinutes(30),
                    'read_at' => Carbon::now()->subMinutes(45),
                ],
                [
                    'sender_id' => $client2->id,
                    'receiver_id' => $prestataire4->id,
                    'content' => 'Parfait ! Je suis disponible demain après-midi. Pouvez-vous me faire un devis approximatif ?',
                    'created_at' => Carbon::now()->subMinutes(30),
                    'read_at' => null, // Message non lu
                ],
            ];
            
            foreach ($messages2 as $messageData) {
                Message::create($messageData);
            }
        }
        
        // Conversation 3: Client 3 avec Prestataire 3
        $client3 = $clients->where('email', 'client3@example.com')->first();
        $prestataire3 = $prestataires->where('email', 'prestataire3@example.com')->first();
        
        if ($client3 && $prestataire3) {
            $messages3 = [
                [
                    'sender_id' => $client3->id,
                    'receiver_id' => $prestataire3->id,
                    'content' => 'Bonjour, j\'organise un événement et j\'ai besoin d\'un photographe professionnel.',
                    'created_at' => Carbon::now()->subHours(6),
                    'read_at' => Carbon::now()->subHours(5),
                ],
                [
                    'sender_id' => $prestataire3->id,
                    'receiver_id' => $client3->id,
                    'content' => 'Bonjour ! Je serais intéressé par votre événement. De quel type d\'événement s\'agit-il et quand aura-t-il lieu ?',
                    'created_at' => Carbon::now()->subHours(5),
                    'read_at' => Carbon::now()->subHours(4),
                ],
                [
                    'sender_id' => $client3->id,
                    'receiver_id' => $prestataire3->id,
                    'content' => 'C\'est un mariage qui aura lieu le mois prochain à Marseille. Nous cherchons quelqu\'un pour couvrir la cérémonie et la réception.',
                    'created_at' => Carbon::now()->subHours(4),
                    'read_at' => Carbon::now()->subHours(3),
                ],
                [
                    'sender_id' => $prestataire3->id,
                    'receiver_id' => $client3->id,
                    'content' => 'Magnifique ! J\'adore photographier les mariages. Je peux vous envoyer mon portfolio et mes tarifs. Avez-vous une date précise ?',
                    'created_at' => Carbon::now()->subHours(3),
                    'read_at' => null, // Message non lu
                ],
            ];
            
            foreach ($messages3 as $messageData) {
                Message::create($messageData);
            }
        }
        
        // Messages supplémentaires pour tester différents scénarios
        // Conversation courte entre Client 1 et Prestataire 4
        if ($client1 && $prestataire4) {
            Message::create([
                'sender_id' => $client1->id,
                'receiver_id' => $prestataire4->id,
                'content' => 'Bonjour, êtes-vous disponible pour un petit projet de consultation ?',
                'created_at' => Carbon::now()->subMinutes(15),
                'read_at' => null,
            ]);
        }
        
        // Message de Prestataire 1 vers Client 2
        if ($prestataire1 && $client2) {
            Message::create([
                'sender_id' => $prestataire1->id,
                'receiver_id' => $client2->id,
                'content' => 'Bonjour ! J\'ai vu que vous cherchez des services de développement. Je peux vous aider !',
                'created_at' => Carbon::now()->subHours(2),
                'read_at' => Carbon::now()->subMinutes(90),
            ]);
        }
        
        $this->command->info('Messages de test créés avec succès !');
    }
}