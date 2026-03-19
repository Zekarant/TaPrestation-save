<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helper to safely add an index
        $addIndex = function (string $table, string|array $columns, ?string $name = null) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                    if ($name) {
                        $t->index($columns, $name);
                    } else {
                        $t->index($columns);
                    }
                });
            } catch (\Exception $e) {
                // Index already exists, skip
            }
        };

        // Foreign keys missing indexes
        if (Schema::hasTable('food_orders')) {
            $addIndex('food_orders', 'client_id');
            $addIndex('food_orders', 'payment_status');
            $addIndex('food_orders', 'delivery_status');
        }

        if (Schema::hasTable('food_order_items')) {
            $addIndex('food_order_items', 'food_product_id');
        }

        if (Schema::hasTable('notifications')) {
            $addIndex('notifications', ['notifiable_type', 'notifiable_id'], 'notifications_notifiable_index');
            $addIndex('notifications', 'read_at');
        }

        if (Schema::hasTable('messages')) {
            $addIndex('messages', 'sender_id');
            $addIndex('messages', 'receiver_id');
            $addIndex('messages', 'read_at');
            $addIndex('messages', 'client_request_id');
        }

        if (Schema::hasTable('reviews')) {
            $addIndex('reviews', 'moderated_by');
        }

        if (Schema::hasTable('equipment_reviews')) {
            $addIndex('equipment_reviews', 'moderated_by');
        }

        if (Schema::hasTable('services')) {
            $addIndex('services', 'prestataire_id');
        }

        if (Schema::hasTable('availabilities')) {
            $addIndex('availabilities', 'service_id');
        }

        if (Schema::hasTable('time_slots')) {
            $addIndex('time_slots', 'service_id');
            $addIndex('time_slots', 'status');
        }

        if (Schema::hasTable('prestataire_availabilities')) {
            $addIndex('prestataire_availabilities', ['prestataire_id', 'day_of_week'], 'presta_avail_presta_day_index');
            $addIndex('prestataire_availabilities', 'is_active');
        }

        if (Schema::hasTable('clients')) {
            $addIndex('clients', 'user_id');
        }

        if (Schema::hasTable('urgent_sale_purchases')) {
            $addIndex('urgent_sale_purchases', 'buyer_user_id');
            $addIndex('urgent_sale_purchases', 'payment_transaction_id');
        }

        if (Schema::hasTable('videos')) {
            $addIndex('videos', 'prestataire_id');
        }

        if (Schema::hasTable('video_comments')) {
            $addIndex('video_comments', ['video_id', 'user_id'], 'video_comments_video_user_index');
        }

        if (Schema::hasTable('equipment_rental_requests')) {
            $addIndex('equipment_rental_requests', ['start_date', 'end_date'], 'equip_rental_req_date_range_index');
        }
    }

    public function down(): void
    {
        $dropIndex = function (string $table, string $name) {
            try {
                Schema::table($table, function (Blueprint $t) use ($name) {
                    $t->dropIndex($name);
                });
            } catch (\Exception $e) {
                // Index doesn't exist, skip
            }
        };

        // Drop all indexes added (use Laravel's naming convention: table_column_index)
        $dropIndex('food_orders', 'food_orders_client_id_index');
        $dropIndex('food_orders', 'food_orders_payment_status_index');
        $dropIndex('food_orders', 'food_orders_delivery_status_index');
        $dropIndex('food_order_items', 'food_order_items_food_product_id_index');
        $dropIndex('notifications', 'notifications_notifiable_index');
        $dropIndex('notifications', 'notifications_read_at_index');
        $dropIndex('messages', 'messages_sender_id_index');
        $dropIndex('messages', 'messages_receiver_id_index');
        $dropIndex('messages', 'messages_read_at_index');
        $dropIndex('messages', 'messages_client_request_id_index');
        $dropIndex('reviews', 'reviews_moderated_by_index');
        $dropIndex('equipment_reviews', 'equipment_reviews_moderated_by_index');
        $dropIndex('services', 'services_prestataire_id_index');
        $dropIndex('availabilities', 'availabilities_service_id_index');
        $dropIndex('time_slots', 'time_slots_service_id_index');
        $dropIndex('time_slots', 'time_slots_status_index');
        $dropIndex('prestataire_availabilities', 'presta_avail_presta_day_index');
        $dropIndex('prestataire_availabilities', 'prestataire_availabilities_is_active_index');
        $dropIndex('clients', 'clients_user_id_index');
        $dropIndex('urgent_sale_purchases', 'urgent_sale_purchases_buyer_user_id_index');
        $dropIndex('urgent_sale_purchases', 'urgent_sale_purchases_payment_transaction_id_index');
        $dropIndex('videos', 'videos_prestataire_id_index');
        $dropIndex('video_comments', 'video_comments_video_user_index');
        $dropIndex('equipment_rental_requests', 'equip_rental_req_date_range_index');
    }
};
