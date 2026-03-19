<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('reviews') && !Schema::hasColumn('reviews', 'deleted_at')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('client_reviews') && !Schema::hasColumn('client_reviews', 'deleted_at')) {
            Schema::table('client_reviews', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'deleted_at')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('client_reviews') && Schema::hasColumn('client_reviews', 'deleted_at')) {
            Schema::table('client_reviews', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
