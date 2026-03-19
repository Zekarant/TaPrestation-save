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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('quiet_hours_enabled')->default(true);
            $table->time('quiet_start')->default('22:00');
            $table->time('quiet_end')->default('08:00');
            $table->boolean('booking_notifications')->default(true);
            $table->boolean('payment_notifications')->default(true);
            $table->boolean('review_notifications')->default(true);
            $table->boolean('message_notifications')->default(true);
            $table->boolean('auction_notifications')->default(true);
            $table->boolean('promotion_notifications')->default(false);
            $table->enum('notification_frequency', ['immediate', 'hourly', 'daily', 'weekly'])->default('immediate');
            $table->string('push_device_token')->nullable();
            $table->string('phone_number')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
