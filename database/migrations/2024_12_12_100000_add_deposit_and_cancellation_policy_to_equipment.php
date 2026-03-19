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
        Schema::table('equipment', function (Blueprint $table) {
            // Acompte en pourcentage du prix de location
            if (!Schema::hasColumn('equipment', 'deposit_percentage')) {
                $table->decimal('deposit_percentage', 5, 2)->nullable()->default(0)->after('security_deposit');
            }
            
            // Auto-acceptation si l'acompte est payé
            if (!Schema::hasColumn('equipment', 'auto_accept_on_deposit')) {
                $table->boolean('auto_accept_on_deposit')->default(false)->after('deposit_percentage');
            }
            
            // Heures avant le début où l'annulation avec remboursement est possible
            if (!Schema::hasColumn('equipment', 'cancellation_hours')) {
                $table->integer('cancellation_hours')->default(24)->after('auto_accept_on_deposit');
            }
            
            // Pourcentage de remboursement en cas d'annulation dans les délais
            if (!Schema::hasColumn('equipment', 'cancellation_refund_percentage')) {
                $table->decimal('cancellation_refund_percentage', 5, 2)->default(100)->after('cancellation_hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $columns = ['deposit_percentage', 'auto_accept_on_deposit', 'cancellation_hours', 'cancellation_refund_percentage'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('equipment', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
