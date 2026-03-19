<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Audit 3.9: Ajout des FK manquantes sur les tables critiques.
 *
 * Les FK sont ajoutées avec onDelete('set null') ou onDelete('cascade')
 * selon le contexte métier, et uniquement si la colonne et la table cible existent.
 */
return new class extends Migration {
    public function up(): void
    {
        // Clean orphan references before adding FK constraints
        $this->cleanOrphans('bookings', 'client_id', 'clients');
        $this->cleanOrphans('bookings', 'prestataire_id', 'prestataires');
        $this->cleanOrphans('payment_transactions', 'user_id', 'users');
        $this->cleanOrphans('payment_transactions', 'booking_id', 'bookings');
        $this->cleanOrphans('payment_transactions', 'equipment_rental_id', 'equipment_rental_requests');
        $this->cleanOrphans('payment_transactions', 'food_order_id', 'food_orders');
        $this->cleanOrphans('escrow_transactions', 'client_id', 'clients');
        $this->cleanOrphans('escrow_transactions', 'prestataire_id', 'prestataires');
        $this->cleanOrphans('escrow_disputes', 'opened_by', 'users');

        $fks = [
            ['bookings', 'client_id', 'clients', 'cascade'],
            ['bookings', 'prestataire_id', 'prestataires', 'cascade'],
            ['payment_transactions', 'user_id', 'users', 'cascade'],
            ['payment_transactions', 'booking_id', 'bookings', 'set null'],
            ['payment_transactions', 'equipment_rental_id', 'equipment_rental_requests', 'set null'],
            ['payment_transactions', 'food_order_id', 'food_orders', 'set null'],
            ['escrow_transactions', 'client_id', 'clients', 'cascade'],
            ['escrow_transactions', 'prestataire_id', 'prestataires', 'cascade'],
            ['escrow_disputes', 'escrow_id', 'escrow_transactions', 'cascade'],
            ['escrow_disputes', 'opened_by', 'users', 'cascade'],
        ];

        foreach ($fks as [$table, $column, $refTable, $onDelete]) {
            if (!Schema::hasTable($table) || !Schema::hasTable($refTable))
                continue;
            if (!Schema::hasColumn($table, $column))
                continue;
            if ($this->hasForeignKey($table, $column))
                continue;

            try {
                Schema::table($table, function (Blueprint $blueprint) use ($column, $refTable, $onDelete) {
                    $blueprint->foreign($column)->references('id')->on($refTable)->onDelete($onDelete);
                });
            } catch (\Throwable $e) {
                // Skip FK if type mismatch, duplicate, or other incompatibility
            }
        }
    }

    public function down(): void
    {
        $fks = [
            'bookings' => ['client_id', 'prestataire_id'],
            'payment_transactions' => ['user_id', 'booking_id', 'equipment_rental_id', 'food_order_id'],
            'escrow_transactions' => ['client_id', 'prestataire_id'],
            'escrow_disputes' => ['escrow_id', 'opened_by'],
        ];

        foreach ($fks as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        try {
                            $table->dropForeign([$column]);
                        } catch (\Throwable $e) {
                            // FK may not exist
                        }
                    }
                });
            }
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        $dbName = config('database.connections.mysql.database');
        $fks = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$dbName, $table, $column]
        );
        return count($fks) > 0;
    }

    private function cleanOrphans(string $table, string $column, string $referencedTable): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($referencedTable) || !Schema::hasColumn($table, $column)) {
            return;
        }
        DB::statement("DELETE FROM `$table` WHERE `$column` IS NOT NULL AND `$column` NOT IN (SELECT `id` FROM `$referencedTable`)");
    }
};
