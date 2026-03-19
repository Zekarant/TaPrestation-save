<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Messages: index critiques pour la messagerie
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!$this->hasIndex('messages', 'messages_sender_id_index')) {
                    $table->index('sender_id');
                }
                if (!$this->hasIndex('messages', 'messages_receiver_id_index')) {
                    $table->index('receiver_id');
                }
                if (!$this->hasIndex('messages', 'messages_created_at_index')) {
                    $table->index('created_at');
                }
                if (!$this->hasIndex('messages', 'messages_receiver_id_read_at_index')) {
                    $table->index(['receiver_id', 'read_at']);
                }
            });
        }

        // Bookings: index composés pour les requêtes fréquentes
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!$this->hasIndex('bookings', 'bookings_status_created_at_index')) {
                    $table->index(['status', 'created_at']);
                }
                if (!$this->hasIndex('bookings', 'bookings_client_id_status_index')) {
                    $table->index(['client_id', 'status']);
                }
                if (!$this->hasIndex('bookings', 'bookings_prestataire_id_status_index')) {
                    $table->index(['prestataire_id', 'status']);
                }
            });
        }

        // Equipment rental requests: index composé pour le monitoring
        if (Schema::hasTable('equipment_rental_requests')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                if (!$this->hasIndex('equipment_rental_requests', 'equip_rental_status_expires_created_index')) {
                    $table->index(['status', 'expires_at', 'created_at'], 'equip_rental_status_expires_created_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex(['sender_id']);
                $table->dropIndex(['receiver_id']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['receiver_id', 'read_at']);
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex(['status', 'created_at']);
                $table->dropIndex(['client_id', 'status']);
                $table->dropIndex(['prestataire_id', 'status']);
            });
        }

        if (Schema::hasTable('equipment_rental_requests')) {
            Schema::table('equipment_rental_requests', function (Blueprint $table) {
                $table->dropIndex('equip_rental_status_expires_created_index');
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
