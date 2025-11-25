<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Désactiver les contraintes de clé étrangère pour éviter les erreurs de truncate
        Schema::disableForeignKeyConstraints();

        // Appel des seeders
        $this->call(RoleSeeder::class);
        $this->call([
            UsersTableSeeder::class,
            NewCategoriesSeeder::class,
            NewEquipmentCategoriesSeeder::class,
            EnhancedEquipmentTableSeeder::class,
            EnhancedEquipmentRentalSeeder::class,
            SkillsTableSeeder::class,
            EnhancedServicesTableSeeder::class,
            AdditionalServicesTableSeeder::class,
            MoreServicesTableSeeder::class,
            UrgentSalesTableSeeder::class,
            EnhancedUrgentSalesTableSeeder::class,
            NotificationsTableSeeder::class,
            MessagesTableSeeder::class,
            EnhancedBookingsTableSeeder::class,
            CompletedBookingsSeeder::class,
            HaythamPrestataireSeeder::class,
            ApprovedPrestataireSeeder::class,
            TestPrestataireSeeder::class,
            TestPrestataireBookingsSeeder::class,
            VideoSeeder::class,
        ]);

        // Réactiver les contraintes
        Schema::enableForeignKeyConstraints();
    }
}
