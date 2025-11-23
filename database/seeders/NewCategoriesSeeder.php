<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class NewCategoriesSeeder extends Seeder
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
        // Use database-agnostic approach for truncating tables
        DB::table('category_service')->delete();
        DB::table('service_category')->delete();
        Category::truncate();
        
        // Créer les nouvelles catégories à partir du fichier categories_data.php
        foreach ($categoriesData as $categoryName => $subcategories) {
            // Créer la catégorie principale
            $parentCategory = Category::firstOrCreate(
                ['name' => $categoryName],
                [
                    'description' => 'Catégorie ' . $categoryName,
                    'parent_id' => null,
                ]
            );
            
            // Créer les sous-catégories
            foreach ($subcategories as $subcategoryName) {
                Category::firstOrCreate(
                    ['name' => $subcategoryName],
                    [
                        'description' => 'Sous-catégorie de ' . $categoryName,
                        'parent_id' => $parentCategory->id,
                    ]
                );
            }
        }
        
        $this->command->info('Nouvelles catégories créées avec succès!');
    }
}