<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_drivers', function (Blueprint $table) {
            // Parrainage
            if (!Schema::hasColumn('delivery_drivers', 'sponsor_prestataire_id')) {
                $table->foreignId('sponsor_prestataire_id')->nullable()->constrained('prestataires')->nullOnDelete();
            }
            if (!Schema::hasColumn('delivery_drivers', 'sponsor_code')) {
                $table->string('sponsor_code', 20)->nullable()->unique();
            }
            if (!Schema::hasColumn('delivery_drivers', 'sponsored_at')) {
                $table->timestamp('sponsored_at')->nullable();
            }
            
            // Vérification progressive
            if (!Schema::hasColumn('delivery_drivers', 'trust_level')) {
                $table->enum('trust_level', ['probation', 'verified', 'trusted', 'suspended'])->default('probation');
            }
            if (!Schema::hasColumn('delivery_drivers', 'daily_limit')) {
                $table->unsignedInteger('daily_limit')->default(3); // Max commandes/jour en probation
            }
            if (!Schema::hasColumn('delivery_drivers', 'max_order_amount')) {
                $table->decimal('max_order_amount', 10, 2)->default(50.00); // Max montant en probation
            }
            if (!Schema::hasColumn('delivery_drivers', 'probation_deliveries_count')) {
                $table->unsignedInteger('probation_deliveries_count')->default(0);
            }
            if (!Schema::hasColumn('delivery_drivers', 'suspended_reason')) {
                $table->string('suspended_reason')->nullable();
            }
            if (!Schema::hasColumn('delivery_drivers', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_drivers', function (Blueprint $table) {
            $columns = [
                'sponsor_prestataire_id', 'sponsor_code', 'sponsored_at',
                'trust_level', 'daily_limit', 'max_order_amount', 
                'probation_deliveries_count', 'suspended_reason', 'suspended_at'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('delivery_drivers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
