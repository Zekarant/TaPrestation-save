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
        Schema::create('equipment_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('rental_id')->constrained('equipment_rentals')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('prestataires')->onDelete('cascade');
            
            // Notes détaillées
            $table->tinyInteger('overall_rating')->unsigned(); // 1-5
            $table->tinyInteger('condition_rating')->unsigned()->nullable(); // 1-5
            $table->tinyInteger('performance_rating')->unsigned()->nullable(); // 1-5
            $table->tinyInteger('value_rating')->unsigned()->nullable(); // 1-5
            $table->tinyInteger('service_rating')->unsigned()->nullable(); // 1-5
            
            // Commentaires
            $table->text('title')->nullable();
            $table->text('comment');
            $table->text('pros')->nullable(); // Points positifs
            $table->text('cons')->nullable(); // Points négatifs
            $table->text('usage_context')->nullable(); // Contexte d'utilisation
            $table->json('photos')->nullable();
            
            // Informations sur l'utilisation
            $table->integer('rental_duration_days');
            $table->string('usage_type')->nullable(); // professionnel, personnel, etc.
            $table->string('usage_frequency')->nullable(); // intensive, moderate, light
            $table->boolean('would_recommend')->default(true);
            $table->boolean('would_rent_again')->default(true);
            
            // Modération et statut
            $table->enum('moderation_status', [
                'pending', 'approved', 'rejected', 'flagged'
            ])->default('pending');
            $table->text('moderation_reason')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Réponse du prestataire
            $table->text('prestataire_response')->nullable();
            $table->timestamp('prestataire_responded_at')->nullable();
            
            // Utilité et interactions
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);
            $table->boolean('verified_rental')->default(true);
            $table->boolean('featured')->default(false);
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->ipAddress('client_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('edited')->default(false);
            $table->timestamp('last_edited_at')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['equipment_id', 'moderation_status']);
            $table->index(['client_id', 'moderation_status']);
            $table->index(['prestataire_id', 'moderation_status']);
            $table->index(['overall_rating', 'moderation_status']);
            $table->index(['verified_rental', 'moderation_status']);
            $table->index(['featured', 'overall_rating']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_reviews');
    }
};