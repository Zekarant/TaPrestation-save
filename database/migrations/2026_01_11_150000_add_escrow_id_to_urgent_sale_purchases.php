<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter escrow_id à urgent_sale_purchases si pas déjà présent
        if (Schema::hasTable('urgent_sale_purchases') && !Schema::hasColumn('urgent_sale_purchases', 'escrow_id')) {
            Schema::table('urgent_sale_purchases', function (Blueprint $table) {
                $table->unsignedBigInteger('escrow_id')->nullable()->after('payment_transaction_id');
                $table->index('escrow_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('urgent_sale_purchases') && Schema::hasColumn('urgent_sale_purchases', 'escrow_id')) {
            Schema::table('urgent_sale_purchases', function (Blueprint $table) {
                $table->dropIndex(['escrow_id']);
                $table->dropColumn('escrow_id');
            });
        }
    }
};
