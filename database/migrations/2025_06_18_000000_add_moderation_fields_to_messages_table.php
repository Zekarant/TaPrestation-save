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
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_reported')->default(false)->after('read_at');
            $table->enum('status', ['pending', 'approved', 'hidden', 'deleted'])->default('approved')->after('is_reported');
            $table->text('moderation_reason')->nullable()->after('status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_reason');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null')->after('moderated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropColumn([
                'is_reported',
                'status',
                'moderation_reason',
                'moderated_at',
                'moderated_by'
            ]);
        });
    }
};