<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewEquipmentCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Inclure les données des catégories
        $categoriesData = require base_path('categories_data.php');
        
        // Vider les tables existantes
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EquipmentCategory::truncate();
        DB::table('equipment_category_equipment')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $sortOrder = 1;
        
        // Créer les nouvelles catégories d'équipement à partir du fichier categories_data.php
        foreach ($categoriesData as $categoryName => $subcategories) {
            // Créer la catégorie principale
            $parentCategory = EquipmentCategory::firstOrCreate(
                ['name' => $categoryName],
                [
                    'slug' => Str::slug($categoryName),
                    'description' => 'Catégorie d\'équipement ' . $categoryName,
                    'parent_id' => null,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                    'featured' => false,
                    'equipment_count' => 0,
                ]
            );
            
            $subSortOrder = 1;
            
            // Créer les sous-catégories
            foreach ($subcategories as $subcategoryName) {
                EquipmentCategory::firstOrCreate(
                    ['name' => $subcategoryName],
                    [
                        'slug' => Str::slug($subcategoryName),
                        'description' => 'Sous-catégorie d\'équipement de ' . $categoryName,
                        'parent_id' => $parentCategory->id,
                        'sort_order' => $subSortOrder,
                        'is_active' => true,
                        'featured' => false,
                        'equipment_count' => 0,
                    ]
                );
                
                $subSortOrder++;
            }
            
            $sortOrder++;
        }
        
        $this->command->info('Nouvelles catégories d\'équipement créées avec succès!');
    }
}