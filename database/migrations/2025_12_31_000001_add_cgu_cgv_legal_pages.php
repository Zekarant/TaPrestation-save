<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter les pages CGU et CGV si elles n'existent pas
        $existingCgu = DB::table('legal_pages')->where('slug', 'cgu')->exists();
        $existingCgv = DB::table('legal_pages')->where('slug', 'cgv')->exists();

        if (!$existingCgu) {
            DB::table('legal_pages')->insert([
                'slug' => 'cgu',
                'title' => 'Conditions Générales d\'Utilisation (CGU)',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$existingCgv) {
            DB::table('legal_pages')->insert([
                'slug' => 'cgv',
                'title' => 'Conditions Générales de Vente (CGV)',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('legal_pages')->where('slug', 'cgu')->delete();
        DB::table('legal_pages')->where('slug', 'cgv')->delete();
    }
};
