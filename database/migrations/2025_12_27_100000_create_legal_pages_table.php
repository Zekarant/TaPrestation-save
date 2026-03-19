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
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // terms, privacy, cookies, mentions, faq, contact, videos
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable(); // Pour upload de fichiers
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_updated_by')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Insérer les pages par défaut
        DB::table('legal_pages')->insert([
            [
                'slug' => 'terms',
                'title' => 'Conditions d\'utilisation',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'privacy',
                'title' => 'Politique de confidentialité',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'cookies',
                'title' => 'Politique de cookies',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'mentions',
                'title' => 'Mentions légales',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'faq',
                'title' => 'FAQ',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'contact',
                'title' => 'Contactez-nous',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'videos',
                'title' => 'Vidéos',
                'content' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
