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
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('reporter_id')->nullable();
            $table->enum('reporter_type', ['client', 'prestataire', 'anonymous'])->default('anonymous');
            $table->string('reason');
            $table->enum('category', [
                'inappropriate_content',
                'fraud',
                'misleading_info',
                'poor_service',
                'pricing_issue',
                'unavailable',
                'spam',
                'copyright',
                'other'
            ]);
            $table->text('description');
            $table->json('evidence_photos')->nullable();
            $table->json('contact_info')->nullable();
            $table->enum('status', ['pending', 'under_review', 'investigating', 'resolved', 'dismissed', 'escalated'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('admin_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('follow_up_required')->default(false);
            $table->timestamp('follow_up_date')->nullable();
            $table->foreignId('related_booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->string('reporter_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['category', 'status']);
            $table->index(['priority', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
