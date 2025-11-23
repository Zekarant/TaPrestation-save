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
            // Drop the existing foreign key constraint pointing to equipment_categories
            $table->dropForeign(['category_id']);
            
            // Add the correct foreign key constraint pointing to categories table
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urgent_sales', function (Blueprint $table) {
            // Drop the corrected foreign key constraint
            $table->dropForeign(['category_id']);
            
            // Restore the original foreign key constraint pointing to equipment_categories
            $table->foreign('category_id')->references('id')->on('equipment_categories')->onDelete('set null');
        });
    }
};