<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // transactions: requêtes fréquentes par status, created_at, prestataire_id
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('status', 20)->change();
            });
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['status', 'created_at']);
                $table->index('prestataire_id');
            });
        }

        // withdrawals: filtré par status dans le dashboard admin
        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->string('status', 20)->change();
            });
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->index('status');
            });
        }

        // refunds: filtré par status dans le dashboard admin
        if (Schema::hasTable('refunds')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->string('status', 20)->change();
            });
            Schema::table('refunds', function (Blueprint $table) {
                $table->index('status');
            });
        }

        // reviews: requêtes fréquentes par service_id, prestataire_id, created_at
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex(['status', 'created_at']);
                $table->dropIndex(['prestataire_id']);
            });
        }

        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('refunds')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        }
    }
};
