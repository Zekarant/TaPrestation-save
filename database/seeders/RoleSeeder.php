<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::firstOrCreate(['name' => 'client']);
        Role::firstOrCreate(['name' => 'prestataire']);
        Role::firstOrCreate(['name' => 'admin']);
    }
}