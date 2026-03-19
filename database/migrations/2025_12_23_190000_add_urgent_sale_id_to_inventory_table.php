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
        Schema::table('inventory', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory', 'urgent_sale_id')) {
                $table->foreignId('urgent_sale_id')->nullable()->after('user_id')->constrained('urgent_sales')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            if (Schema::hasColumn('inventory', 'urgent_sale_id')) {
                $table->dropForeign(['urgent_sale_id']);
                $table->dropColumn('urgent_sale_id');
            }
        });
    }
};
