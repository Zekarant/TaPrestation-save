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
        Schema::table('food_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('food_orders', 'cancelled_by')) {
                $table->string('cancelled_by')->nullable()->after('cancellation_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            if (Schema::hasColumn('food_orders', 'cancelled_by')) {
                $table->dropColumn('cancelled_by');
            }
        });
    }
};
