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
        Schema::create('prestataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('siret')->nullable();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('France');
            $table->integer('service_radius_km')->default(50);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->integer('years_experience')->nullable();
            $table->decimal('hourly_rate_min', 8, 2)->nullable();
            $table->decimal('hourly_rate_max', 8, 2)->nullable();
            $table->integer('availability_radius')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('portfolio_images')->nullable();
            $table->json('certifications')->nullable();
            $table->string('insurance_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->json('bank_details')->nullable();
            $table->json('preferred_payment_methods')->nullable();
            $table->integer('response_time')->nullable();
            $table->decimal('completion_rate', 5, 2)->nullable();
            $table->decimal('rating_average', 3, 2)->nullable();
            $table->integer('total_reviews')->default(0);
            $table->integer('total_projects')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->string('subscription_type')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->string('verification_status')->default('pending');
            $table->string('background_check_status')->default('pending');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('business_license')->nullable();
            $table->string('professional_insurance')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestataires');
    }
};
