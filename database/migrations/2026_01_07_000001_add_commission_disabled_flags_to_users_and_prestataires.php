<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'commission_client_disabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('commission_client_disabled')->default(false)->after('is_online');
            });
        }

        if (Schema::hasTable('prestataires') && !Schema::hasColumn('prestataires', 'commission_prestataire_disabled')) {
            Schema::table('prestataires', function (Blueprint $table) {
                $table->boolean('commission_prestataire_disabled')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'commission_client_disabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('commission_client_disabled');
            });
        }

        if (Schema::hasTable('prestataires') && Schema::hasColumn('prestataires', 'commission_prestataire_disabled')) {
            Schema::table('prestataires', function (Blueprint $table) {
                $table->dropColumn('commission_prestataire_disabled');
            });
        }
    }
};
