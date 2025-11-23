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
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('photo', 'avatar');
            $table->string('phone')->nullable()->after('user_id');
            $table->string('address')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('avatar', 'photo');
            $table->dropColumn(['phone', 'address', 'bio']);
        });
    }
};
