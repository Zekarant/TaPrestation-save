<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('equipment_deletions')) {
            Schema::create('equipment_deletions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('equipment_id');
                $table->string('equipment_name');
                $table->unsignedBigInteger('prestataire_id');
                $table->string('reason', 500);
                $table->unsignedBigInteger('deleted_by');
                $table->timestamp('deleted_at');
                $table->json('equipment_data')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_deletions');
    }
};
