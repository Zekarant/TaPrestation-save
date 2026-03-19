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
        Schema::create('equipment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->unsignedBigInteger('reporter_id');
            $table->string('reporter_type');
            $table->string('reason');
            $table->string('category');
            $table->text('description')->nullable();
            $table->json('evidence_photos')->nullable();
            $table->json('contact_info')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->text('admin_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->foreignId('related_rental_id')->nullable()->constrained('equipment_rentals')->onDelete('set null');
            $table->string('reporter_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['reporter_id', 'reporter_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_reports');
    }
};
