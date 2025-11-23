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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('technical_specifications')->nullable();
            $table->json('photos')->nullable();
            $table->string('main_photo')->nullable();
            
            // Tarification
            $table->decimal('price_per_hour', 8, 2)->nullable();
            $table->decimal('price_per_day', 8, 2);
            $table->decimal('price_per_week', 8, 2)->nullable();
            $table->decimal('price_per_month', 8, 2)->nullable();
            $table->decimal('security_deposit', 8, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->boolean('delivery_included')->default(false);
            
            // État et disponibilité
            $table->enum('condition', ['excellent', 'very_good', 'good', 'fair'])->default('good');
            $table->enum('status', ['active', 'inactive', 'maintenance', 'rented', 'unavailable'])->default('active');
            $table->boolean('is_available')->default(true);
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            $table->integer('minimum_rental_duration')->default(1); // en jours
            $table->integer('maximum_rental_duration')->nullable(); // en jours
            
            // Localisation
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('delivery_radius')->default(0); // en km
            
            // Conditions et règles
            $table->text('rental_conditions')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->text('safety_instructions')->nullable();
            $table->json('included_accessories')->nullable();
            $table->json('optional_accessories')->nullable();
            $table->boolean('requires_license')->default(false);
            $table->string('required_license_type')->nullable();
            $table->integer('minimum_age')->nullable();
            
            // Statistiques
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('total_rentals')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamp('last_rented_at')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['prestataire_id', 'status']);
            $table->index(['city', 'is_available']);
            $table->index(['status', 'is_available']);
            $table->index(['price_per_day', 'is_available']);
            $table->index(['average_rating', 'total_reviews']);
            $table->index(['featured', 'sort_order']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};