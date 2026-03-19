<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_setup_required')) {
                $table->boolean('password_setup_required')
                    ->default(false)
                    ->after('avatar');
            }
        });

        DB::table('users')
            ->where(function ($query) {
                $query->whereNotNull('google_id')
                    ->orWhereNotNull('apple_id');
            })
            ->where(function ($query) {
                $query->whereNull('password')
                    ->orWhere('password', '');
            })
            ->update(['password_setup_required' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_setup_required')) {
                $table->dropColumn('password_setup_required');
            }
        });
    }
};
