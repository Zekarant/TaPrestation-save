<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('food_orders', 'preparing_at')) {
                $table->datetime('preparing_at')->nullable()->after('accepted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table) {
            $table->dropColumn('preparing_at');
        });
    }
};
