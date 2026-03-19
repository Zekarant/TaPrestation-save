<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('driver_locations')) {
            Schema::create('driver_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('delivery_drivers')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable(); // Précision GPS en mètres
            $table->decimal('heading', 5, 2)->nullable(); // Direction 0-360
            $table->decimal('speed', 8, 2)->nullable(); // Vitesse km/h
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->timestamps();
            
            $table->index(['driver_id', 'created_at']);
            $table->index(['latitude', 'longitude']);
            });
        }
        
        // Ajouter colonne location_updated_at sur delivery_drivers (si pas déjà présente)
        if (!Schema::hasColumn('delivery_drivers', 'location_updated_at')) {
            Schema::table('delivery_drivers', function (Blueprint $table) {
                $table->timestamp('location_updated_at')->nullable()->after('current_lng');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('delivery_drivers', 'location_updated_at')) {
            Schema::table('delivery_drivers', function (Blueprint $table) {
                $table->dropColumn('location_updated_at');
            });
        }
        Schema::dropIfExists('driver_locations');
    }
};
