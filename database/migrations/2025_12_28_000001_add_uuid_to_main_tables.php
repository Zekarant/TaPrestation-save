<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Ajoute une colonne uuid aux tables principales pour sécuriser les URLs
 * Les UUIDs sont natifs à Laravel et très stables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Liste des tables à modifier avec leur clé primaire
        $tables = [
            'users',
            'prestataires', 
            'services',
            'bookings',
            'equipments',
            'urgent_sales',
            'reviews',
            'messages',
            'equipment_rental_requests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->uuid('uuid')->nullable()->after('id');
                    $table->index('uuid');
                });
                
                // Générer des UUIDs pour les enregistrements existants
                \DB::table($table)->whereNull('uuid')->orderBy('id')->chunk(100, function ($records) use ($table) {
                    foreach ($records as $record) {
                        \DB::table($table)->where('id', $record->id)->update(['uuid' => Str::uuid()]);
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'prestataires',
            'services', 
            'bookings',
            'equipments',
            'urgent_sales',
            'reviews',
            'messages',
            'equipment_rental_requests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('uuid');
                });
            }
        }
    }
};
