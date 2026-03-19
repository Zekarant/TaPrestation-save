<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasPreorderFlag = Schema::hasColumn('food_products', 'is_preorder_only');
        $hasPreorderDelay = Schema::hasColumn('food_products', 'min_preorder_days');

        if ($hasPreorderFlag && $hasPreorderDelay) {
            return;
        }

        Schema::table('food_products', function (Blueprint $table) use ($hasPreorderFlag, $hasPreorderDelay) {
            if (!$hasPreorderFlag) {
                $table->boolean('is_preorder_only')
                    ->default(false)
                    ->after('is_available');
            }

            if (!$hasPreorderDelay) {
                $table->unsignedSmallInteger('min_preorder_days')
                    ->nullable()
                    ->after('is_preorder_only');
            }
        });
    }

    public function down(): void
    {
        $columnsToDrop = [];

        if (Schema::hasColumn('food_products', 'min_preorder_days')) {
            $columnsToDrop[] = 'min_preorder_days';
        }

        if (Schema::hasColumn('food_products', 'is_preorder_only')) {
            $columnsToDrop[] = 'is_preorder_only';
        }

        if (empty($columnsToDrop)) {
            return;
        }

        Schema::table('food_products', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
    }
};
