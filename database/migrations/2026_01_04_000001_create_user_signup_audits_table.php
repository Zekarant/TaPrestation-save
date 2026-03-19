<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_signup_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('accept_language', 255)->nullable();

            $table->unsignedInteger('clicks')->nullable();
            $table->unsignedInteger('keypresses')->nullable();
            $table->unsignedInteger('time_to_submit_ms')->nullable();

            $table->string('recaptcha_version', 32)->nullable();
            $table->string('recaptcha_action', 64)->nullable();
            $table->decimal('recaptcha_score', 4, 3)->nullable();
            $table->boolean('recaptcha_success')->default(false);
            $table->json('recaptcha_error_codes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_signup_audits');
    }
};
