<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

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
    }
}