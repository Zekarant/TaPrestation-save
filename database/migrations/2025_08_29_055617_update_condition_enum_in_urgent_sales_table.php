<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, expand the enum to include both old and new values
        DB::statement("ALTER TABLE urgent_sales MODIFY COLUMN `condition` ENUM('new', 'good', 'used', 'fair', 'excellent', 'very_good', 'poor') NOT NULL");
        
        // Now update existing data to match new values
        DB::table('urgent_sales')
            ->where('condition', 'new')
            ->update(['condition' => 'excellent']);
            
        DB::table('urgent_sales')
            ->where('condition', 'used')
            ->update(['condition' => 'good']);
            
        // 'good' and 'fair' can remain as they are
        
        // Finally, set the enum to only the new values
        DB::statement("ALTER TABLE urgent_sales MODIFY COLUMN `condition` ENUM('excellent', 'very_good', 'good', 'fair', 'poor') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the data mapping
        DB::table('urgent_sales')
            ->where('condition', 'excellent')
            ->update(['condition' => 'new']);
            
        DB::table('urgent_sales')
            ->where('condition', 'very_good')
            ->update(['condition' => 'good']);
            
        DB::table('urgent_sales')
            ->where('condition', 'poor')
            ->update(['condition' => 'fair']);
            
        // Revert the enum definition
        DB::statement("ALTER TABLE urgent_sales MODIFY COLUMN `condition` ENUM('new', 'good', 'used', 'fair') NOT NULL");
    }
};
