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
        Schema::table('urgent_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('urgent_sales', 'reserved_quantity')) {
                $table->integer('reserved_quantity')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('urgent_sales', 'sold_quantity')) {
                $table->integer('sold_quantity')->default(0)->after('reserved_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urgent_sales', function (Blueprint $table) {
            $table->dropColumn(['reserved_quantity', 'sold_quantity']);
        });
    }
};
