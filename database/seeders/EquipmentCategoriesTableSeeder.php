<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentCategory;

class EquipmentCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Création des catégories principales d'équipement
        $batiment = EquipmentCategory::firstOrCreate(['name' => 'Bâtiment et Construction'], [
            'name' => 'Bâtiment et Construction',
            'slug' => 'batiment-construction',
            'description' => 'Équipements pour le bâtiment et la construction',
            'icon' => 'fas fa-hard-hat',
            'color' => '#FF6B35',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $jardinage = EquipmentCategory::firstOrCreate(['name' => 'Jardinage et Espaces Verts'], [
            'name' => 'Jardinage et Espaces Verts',
            'slug' => 'jardinage-espaces-verts',
            'description' => 'Équipements pour le jardinage et l\'entretien des espaces verts',
            'icon' => 'fas fa-seedling',
            'color' => '#4CAF50',
            'is_active' => true,
            'sort_order' => 2
        ]);

        $nettoyage = EquipmentCategory::firstOrCreate(['name' => 'Nettoyage et Entretien'], [
            'name' => 'Nettoyage et Entretien',
            'slug' => 'nettoyage-entretien',
            'description' => 'Équipements de nettoyage et d\'entretien',
            'icon' => 'fas fa-broom',
            'color' => '#2196F3',
            'is_active' => true,
            'sort_order' => 3
        ]);

        $transport = EquipmentCategory::firstOrCreate(['name' => 'Transport et Manutention'], [
            'name' => 'Transport et Manutention',
            'slug' => 'transport-manutention',
            'description' => 'Équipements de transport et de manutention',
            'icon' => 'fas fa-truck',
            'color' => '#FF9800',
            'is_active' => true,
            'sort_order' => 4
        ]);

        $evenementiel = EquipmentCategory::firstOrCreate(['name' => 'Événementiel'], [
            'name' => 'Événementiel',
            'slug' => 'evenementiel',
            'description' => 'Équipements pour événements et réceptions',
            'icon' => 'fas fa-glass-cheers',
            'color' => '#E91E63',
            'is_active' => true,
            'sort_order' => 5
        ]);

        $audiovisuel = EquipmentCategory::firstOrCreate(['name' => 'Audiovisuel'], [
            'name' => 'Audiovisuel',
            'slug' => 'audiovisuel',
            'description' => 'Équipements audiovisuels et de sonorisation',
            'icon' => 'fas fa-video',
            'color' => '#9C27B0',
            'is_active' => true,
            'sort_order' => 6
        ]);

        // Sous-catégories pour Bâtiment et Construction
        EquipmentCategory::firstOrCreate(['name' => 'Outils électriques'], [
            'name' => 'Outils électriques',
            'slug' => 'outils-electriques',
            'description' => 'Perceuses, scies, ponceuses, etc.',
            'parent_id' => $batiment->id,
            'is_active' => true,
            'sort_order' => 1
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Échafaudages'], [
            'name' => 'Échafaudages',
            'slug' => 'echafaudages',
            'description' => 'Échafaudages et équipements de hauteur',
            'parent_id' => $batiment->id,
            'is_active' => true,
            'sort_order' => 2
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Bétonnières'], [
            'name' => 'Bétonnières',
            'slug' => 'betonnieres',
            'description' => 'Bétonnières et équipements de malaxage',
            'parent_id' => $batiment->id,
            'is_active' => true,
            'sort_order' => 3
        ]);

        // Sous-catégories pour Jardinage
        EquipmentCategory::firstOrCreate(['name' => 'Tondeuses'], [
            'name' => 'Tondeuses',
            'slug' => 'tondeuses',
            'description' => 'Tondeuses à gazon et robots de tonte',
            'parent_id' => $jardinage->id,
            'is_active' => true,
            'sort_order' => 1
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Taille-haies'], [
            'name' => 'Taille-haies',
            'slug' => 'taille-haies',
            'description' => 'Taille-haies électriques et thermiques',
            'parent_id' => $jardinage->id,
            'is_active' => true,
            'sort_order' => 2
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Souffleurs'], [
            'name' => 'Souffleurs',
            'slug' => 'souffleurs',
            'description' => 'Souffleurs et aspirateurs de feuilles',
            'parent_id' => $jardinage->id,
            'is_active' => true,
            'sort_order' => 3
        ]);

        // Sous-catégories pour Nettoyage
        EquipmentCategory::firstOrCreate(['name' => 'Nettoyeurs haute pression'], [
            'name' => 'Nettoyeurs haute pression',
            'slug' => 'nettoyeurs-haute-pression',
            'description' => 'Nettoyeurs haute pression et accessoires',
            'parent_id' => $nettoyage->id,
            'is_active' => true,
            'sort_order' => 1
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Aspirateurs industriels'], [
            'name' => 'Aspirateurs industriels',
            'slug' => 'aspirateurs-industriels',
            'description' => 'Aspirateurs industriels et de chantier',
            'parent_id' => $nettoyage->id,
            'is_active' => true,
            'sort_order' => 2
        ]);

        // Sous-catégories pour Transport
        EquipmentCategory::firstOrCreate(['name' => 'Diables et sangles'], [
            'name' => 'Diables et sangles',
            'slug' => 'diables-sangles',
            'description' => 'Diables, sangles et équipements de portage',
            'parent_id' => $transport->id,
            'is_active' => true,
            'sort_order' => 1
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Remorques'], [
            'name' => 'Remorques',
            'slug' => 'remorques',
            'description' => 'Remorques et plateaux de transport',
            'parent_id' => $transport->id,
            'is_active' => true,
            'sort_order' => 2
        ]);

        // Sous-catégories pour Événementiel
        EquipmentCategory::firstOrCreate(['name' => 'Tentes et barnums'], [
            'name' => 'Tentes et barnums',
            'slug' => 'tentes-barnums',
            'description' => 'Tentes, barnums et structures temporaires',
            'parent_id' => $evenementiel->id,
            'is_active' => true,
            'sort_order' => 1
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Tables et chaises'], [
            'name' => 'Tables et chaises',
            'slug' => 'tables-chaises',
            'description' => 'Mobilier pour événements',
            'parent_id' => $evenementiel->id,
            'is_active' => true,
            'sort_order' => 2
        ]);

        // Sous-catégories pour Audiovisuel
        EquipmentCategory::firstOrCreate(['name' => 'Sonorisation'], [
            'name' => 'Sonorisation',
            'slug' => 'sonorisation',
            'description' => 'Enceintes, micros et équipements audio',
            'parent_id' => $audiovisuel->id,
            'is_active' => true,
            'sort_order' => 1
        ]);

        EquipmentCategory::firstOrCreate(['name' => 'Éclairage'], [
            'name' => 'Éclairage',
            'slug' => 'eclairage',
            'description' => 'Projecteurs et équipements d\'éclairage',
            'parent_id' => $audiovisuel->id,
            'is_active' => true,
            'sort_order' => 2
        ]);

        $this->command->info('Catégories d\'équipement créées avec succès!');
    }
}