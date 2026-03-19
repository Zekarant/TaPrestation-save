<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryTypesSeeder extends Seeder
{
    /**
     * Catégories spécifiques par type
     */
    public function run(): void
    {
        // === CATÉGORIES SERVICES (Prestations) ===
        $serviceCategories = [
            'Beauté & Bien-être' => [
                'Coiffure', 'Maquillage', 'Massage', 'Manucure & Pédicure', 'Soins du visage', 'Épilation'
            ],
            'Événementiel' => [
                'Traiteur', 'DJ & Animation', 'Photographe', 'Décoration', 'Wedding planner', 'Location de salle'
            ],
            'Artisanat & Création' => [
                'Couture & Retouche', 'Bijouterie', 'Poterie', 'Peinture', 'Création florale', 'Personnalisation'
            ],
            'Services à domicile' => [
                'Ménage', 'Jardinage', 'Bricolage', 'Garde d\'enfants', 'Aide à la personne', 'Cuisine à domicile'
            ],
            'Cours & Formation' => [
                'Musique', 'Langues', 'Sport & Fitness', 'Cuisine', 'Informatique', 'Soutien scolaire'
            ],
            'Santé & Paramédical' => [
                'Kinésithérapeute', 'Ostéopathe', 'Nutritionniste', 'Psychologue', 'Infirmier', 'Coach sportif'
            ],
            'Services aux entreprises' => [
                'Comptabilité', 'Secrétariat', 'Marketing', 'Traduction', 'Conseil juridique', 'Design graphique'
            ],
            'Transport & Logistique' => [
                'Déménagement', 'Livraison', 'Chauffeur VTC', 'Transport de marchandises', 'Coursier'
            ],
        ];

        // === CATÉGORIES ÉQUIPEMENTS (Location) ===
        $equipmentCategories = [
            'Outillage & Bricolage' => [
                'Perceuse & Visseuse', 'Scie électrique', 'Ponceuse', 'Compresseur', 'Échafaudage', 'Bétonnière'
            ],
            'Jardinage & Extérieur' => [
                'Tondeuse', 'Tronçonneuse', 'Taille-haie', 'Motoculteur', 'Nettoyeur haute pression', 'Souffleur'
            ],
            'Événementiel & Fêtes' => [
                'Barnums & Tentes', 'Tables & Chaises', 'Sono & Éclairage', 'Machine à fumée', 'Vaisselle', 'Photobooth'
            ],
            'Électroménager' => [
                'Réfrigérateur', 'Congélateur', 'Machine à laver', 'Four professionnel', 'Robot cuisine'
            ],
            'Audiovisuel & Technologie' => [
                'Vidéoprojecteur', 'Écran géant', 'Caméra', 'Appareil photo', 'Drone', 'Console de jeu'
            ],
            'Sport & Loisirs' => [
                'Vélo', 'Kayak', 'Ski', 'Paddle', 'Camping & Tente', 'Équipement de plongée'
            ],
            'Véhicules & Transport' => [
                'Remorque', 'Utilitaire', 'Vélo cargo', 'Scooter', 'Porte-vélos'
            ],
            'Bébé & Enfants' => [
                'Poussette', 'Siège auto', 'Lit parapluie', 'Chaise haute', 'Jouets'
            ],
            'Médical & Santé' => [
                'Fauteuil roulant', 'Lit médicalisé', 'Concentrateur oxygène', 'Béquilles', 'Déambulateur'
            ],
        ];

        // === CATÉGORIES ANNONCES (Ventes style Le Bon Coin) ===
        $saleCategories = [
            'Véhicules' => [
                'Voitures', 'Motos', 'Vélos', 'Camping-cars', 'Bateaux', 'Pièces détachées'
            ],
            'Immobilier' => [
                'Ventes immobilières', 'Locations', 'Colocations', 'Bureaux & Commerces'
            ],
            'High-Tech' => [
                'Téléphones', 'Ordinateurs', 'Tablettes', 'Consoles & Jeux vidéo', 'Photo & Vidéo', 'Audio'
            ],
            'Maison & Jardin' => [
                'Ameublement', 'Électroménager', 'Décoration', 'Bricolage', 'Jardinage', 'Piscine'
            ],
            'Mode & Accessoires' => [
                'Vêtements femme', 'Vêtements homme', 'Chaussures', 'Montres & Bijoux', 'Sacs', 'Accessoires'
            ],
            'Loisirs & Sports' => [
                'Vélos & Trottinettes', 'Sports d\'hiver', 'Sports nautiques', 'Fitness', 'Instruments musique', 'Livres & BD'
            ],
            'Famille & Bébé' => [
                'Puériculture', 'Vêtements bébé', 'Jouets', 'Équipement enfant'
            ],
            'Animaux' => [
                'Animaux', 'Accessoires animaux', 'Alimentation animale'
            ],
            'Emploi & Services' => [
                'Offres d\'emploi', 'Cours particuliers', 'Services aux entreprises'
            ],
            'Autres' => [
                'Collections', 'Antiquités', 'Art', 'Divers'
            ],
        ];

        // Insérer les catégories de services
        $this->insertCategories($serviceCategories, 'service');
        
        // Insérer les catégories d'équipements
        $this->insertCategories($equipmentCategories, 'equipment');
        
        // Insérer les catégories de ventes
        $this->insertCategories($saleCategories, 'sale');

        $this->command->info('✅ Catégories créées pour services, équipements et ventes !');
    }

    private function insertCategories(array $categories, string $type): void
    {
        foreach ($categories as $parentName => $children) {
            // Vérifier si la catégorie parent existe déjà
            $existingParent = DB::table('categories')
                ->where('name', $parentName)
                ->where('type', $type)
                ->first();

            if ($existingParent) {
                $parentId = $existingParent->id;
            } else {
                $parentId = DB::table('categories')->insertGetId([
                    'name' => $parentName,
                    'type' => $type,
                    'parent_id' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Insérer les sous-catégories
            foreach ($children as $childName) {
                $exists = DB::table('categories')
                    ->where('name', $childName)
                    ->where('parent_id', $parentId)
                    ->where('type', $type)
                    ->exists();

                if (!$exists) {
                    DB::table('categories')->insert([
                        'name' => $childName,
                        'type' => $type,
                        'parent_id' => $parentId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
