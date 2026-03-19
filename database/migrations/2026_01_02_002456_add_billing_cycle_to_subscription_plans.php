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
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'billing_cycle')) {
                $table->enum('billing_cycle', ['weekly', 'monthly', 'quarterly', 'annual'])
                    ->default('monthly')
                    ->after('currency');
            }
            if (!Schema::hasColumn('subscription_plans', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
            if (Schema::hasColumn('subscription_plans', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
