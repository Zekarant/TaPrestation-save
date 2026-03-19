<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('prestataires') || !Schema::hasColumn('prestataires', 'approved_by')) {
            return;
        }

        // Drop existing FK if present (it is NO ACTION in production and blocks user deletion)
        try {
            Schema::table('prestataires', function (Blueprint $table) {
                $table->dropForeign(['approved_by']);
            });
        } catch (Throwable $e) {
            // Ignore if it doesn't exist or is named differently
        }

        Schema::table('prestataires', function (Blueprint $table) {
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('prestataires') || !Schema::hasColumn('prestataires', 'approved_by')) {
            return;
        }

        try {
            Schema::table('prestataires', function (Blueprint $table) {
                $table->dropForeign(['approved_by']);
            });
        } catch (Throwable $e) {
            // Ignore
        }

        // NOTE: We don't recreate the previous NO ACTION FK in down()
        // because the safe behavior is nullOnDelete().
    }
};
