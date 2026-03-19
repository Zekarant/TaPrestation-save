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
        Schema::table('client_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('client_reviews', 'punctuality')) {
                $table->enum('punctuality', ['excellent', 'good', 'average', 'poor'])->nullable()->after('comment');
            }
            if (!Schema::hasColumn('client_reviews', 'communication')) {
                $table->enum('communication', ['excellent', 'good', 'average', 'poor'])->nullable()->after('punctuality');
            }
            if (!Schema::hasColumn('client_reviews', 'respect')) {
                $table->enum('respect', ['excellent', 'good', 'average', 'poor'])->nullable()->after('communication');
            }
            if (!Schema::hasColumn('client_reviews', 'would_work_again')) {
                $table->boolean('would_work_again')->default(true)->after('respect');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_reviews', function (Blueprint $table) {
            $table->dropColumn(['punctuality', 'communication', 'respect', 'would_work_again']);
        });
    }
};
