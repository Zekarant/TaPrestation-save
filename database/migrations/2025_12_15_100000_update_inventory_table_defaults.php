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
        Schema::table('inventory', function (Blueprint $table) {
            // Modifier les colonnes existantes pour ajouter des valeurs par défaut
            $table->string('category')->nullable()->default(null)->change();
            $table->integer('quantity')->default(1)->change();
            $table->string('unit')->default('unité')->change();
            $table->decimal('cost_per_unit', 10, 2)->default(0)->change();
            $table->decimal('selling_price', 10, 2)->default(0)->change();
            $table->integer('reorder_level')->default(1)->change();
        });

        // Ajouter les nouvelles colonnes si elles n'existent pas
        if (!Schema::hasColumn('inventory', 'initial_quantity')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->integer('initial_quantity')->nullable()->after('quantity');
            });
        }

        if (!Schema::hasColumn('inventory', 'condition')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->string('condition')->nullable()->after('location');
            });
        }

        if (!Schema::hasColumn('inventory', 'status')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->string('status')->default('available')->after('condition');
            });
        }

        if (!Schema::hasColumn('inventory', 'photos')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->json('photos')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            // Revert changes - drop new columns if they exist
            if (Schema::hasColumn('inventory', 'initial_quantity')) {
                $table->dropColumn('initial_quantity');
            }
            if (Schema::hasColumn('inventory', 'condition')) {
                $table->dropColumn('condition');
            }
            if (Schema::hasColumn('inventory', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('inventory', 'photos')) {
                $table->dropColumn('photos');
            }
        });
    }
};
