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
        Schema::create('language_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('preferred_language')->default('fr');
            $table->string('timezone')->default('Europe/Paris');
            $table->string('currency')->default('EUR');
            $table->boolean('use_metric')->default(true);
            $table->enum('date_format', ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'])->default('DD/MM/YYYY');
            $table->enum('time_format', ['24h', '12h'])->default('24h');
            $table->json('localization_preferences')->nullable();
            $table->timestamps();
            $table->index('preferred_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_settings');
    }
};
