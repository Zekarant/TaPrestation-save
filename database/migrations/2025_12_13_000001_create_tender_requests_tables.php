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
        // Table principale des appels d'offres
        Schema::create('tender_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('reference')->unique(); // Référence unique générée
            
            // Localisation
            $table->string('address')->nullable();
            $table->string('city');
            $table->string('postal_code')->nullable();
            $table->string('country')->default('France');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('radius_km')->default(50); // Rayon de recherche
            
            // Période et timing
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('preferred_time_start')->nullable();
            $table->time('preferred_time_end')->nullable();
            $table->boolean('flexible_dates')->default(false);
            $table->enum('urgency', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Budget
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            $table->enum('budget_type', ['fixed', 'hourly', 'daily', 'negotiable'])->default('fixed');
            $table->boolean('budget_visible')->default(true);
            
            // Médias
            $table->json('photos')->nullable(); // Array de chemins photos
            $table->json('videos')->nullable(); // Array de chemins vidéos
            $table->json('documents')->nullable(); // Array de documents PDF, etc.
            
            // Accès et contact
            $table->text('access_instructions')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->enum('contact_preference', ['phone', 'email', 'messaging', 'any'])->default('any');
            
            // Statut
            $table->enum('status', [
                'draft',        // Brouillon
                'pending',      // En attente de validation
                'published',    // Publié et visible
                'in_progress',  // Offres en cours d'évaluation
                'awarded',      // Attribué à un prestataire
                'completed',    // Terminé
                'cancelled',    // Annulé
                'expired'       // Expiré
            ])->default('draft');
            
            // Paramètres
            $table->integer('max_responses')->default(10); // Nombre max de réponses
            $table->boolean('auto_match')->default(true); // Matching automatique
            $table->boolean('public_visibility')->default(true); // Visible publiquement
            $table->datetime('expires_at')->nullable();
            $table->datetime('published_at')->nullable();
            $table->datetime('awarded_at')->nullable();
            
            // Prestataire sélectionné
            $table->foreignId('awarded_prestataire_id')->nullable()->constrained('prestataires')->nullOnDelete();
            
            // Étape du formulaire (pour sauvegarde progressive)
            $table->integer('form_step')->default(1);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour les recherches
            $table->index(['status', 'published_at']);
            $table->index(['city', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Table de liaison appels d'offres - catégories
        Schema::create('tender_request_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['tender_request_id', 'category_id']);
        });

        // Table des réponses/propositions des prestataires
        Schema::create('tender_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained()->onDelete('cascade');
            
            // Proposition
            $table->text('cover_letter'); // Lettre de motivation
            $table->decimal('proposed_price', 10, 2);
            $table->enum('price_type', ['fixed', 'hourly', 'daily'])->default('fixed');
            $table->text('price_details')->nullable();
            
            // Disponibilité proposée
            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_end_date')->nullable();
            $table->integer('estimated_duration_hours')->nullable();
            
            // Documents joints
            $table->json('attachments')->nullable();
            
            // Statut
            $table->enum('status', [
                'pending',      // En attente de lecture
                'viewed',       // Vu par le client
                'shortlisted',  // Présélectionné
                'accepted',     // Accepté
                'rejected',     // Rejeté
                'withdrawn'     // Retiré par le prestataire
            ])->default('pending');
            
            // Score de matching (calculé par l'algorithme)
            $table->integer('match_score')->default(0);
            $table->json('match_details')->nullable(); // Détails du calcul
            
            // Communication
            $table->datetime('viewed_at')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->text('client_message')->nullable(); // Message du client
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['tender_request_id', 'prestataire_id']);
            $table->index(['status', 'match_score']);
        });

        // Table des notifications/invitations prestataires
        Schema::create('tender_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained()->onDelete('cascade');
            
            $table->enum('type', ['auto_match', 'manual_invite', 'category_match']);
            $table->integer('match_score')->default(0);
            $table->json('match_reasons')->nullable(); // Raisons du match
            
            $table->boolean('is_read')->default(false);
            $table->boolean('is_interested')->nullable(); // null = pas répondu
            $table->datetime('read_at')->nullable();
            $table->datetime('responded_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['tender_request_id', 'prestataire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tender_invitations');
        Schema::dropIfExists('tender_responses');
        Schema::dropIfExists('tender_request_categories');
        Schema::dropIfExists('tender_requests');
    }
};
