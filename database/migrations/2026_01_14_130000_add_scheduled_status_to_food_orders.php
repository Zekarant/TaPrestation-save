<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute le statut 'scheduled' pour les commandes planifiées acceptées
     */
    public function up(): void
    {
        // Modifier l'ENUM pour ajouter 'scheduled'
        DB::statement("ALTER TABLE food_orders MODIFY COLUMN status ENUM(
            'pending',
            'accepted',
            'scheduled',
            'rejected',
            'preparing',
            'ready',
            'delivered',
            'completed',
            'cancelled'
        ) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les commandes scheduled en accepted avant de supprimer le statut
        DB::table('food_orders')
            ->where('status', 'scheduled')
            ->update(['status' => 'accepted']);
            
        DB::statement("ALTER TABLE food_orders MODIFY COLUMN status ENUM(
            'pending',
            'accepted',
            'rejected',
            'preparing',
            'ready',
            'delivered',
            'completed',
            'cancelled'
        ) DEFAULT 'pending'");
    }
};
