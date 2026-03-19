<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter 'like_new' à l'ENUM condition
        DB::statement("ALTER TABLE urgent_sales MODIFY COLUMN `condition` ENUM('new', 'like_new', 'excellent', 'very_good', 'good', 'fair', 'poor', 'used') NOT NULL DEFAULT 'good'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convertir les like_new en excellent avant de retirer la valeur
        DB::table('urgent_sales')->where('condition', 'like_new')->update(['condition' => 'excellent']);
        
        DB::statement("ALTER TABLE urgent_sales MODIFY COLUMN `condition` ENUM('new', 'excellent', 'very_good', 'good', 'fair', 'poor', 'used') NOT NULL DEFAULT 'good'");
    }
};
