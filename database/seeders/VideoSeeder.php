<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing prestataire IDs
        $prestataireIds = DB::table('prestataires')->pluck('id')->toArray();
        
        if (empty($prestataireIds)) {
            echo "No prestataires found. Please run Prestataire seeders first.\n";
            return;
        }

        // Sample video data
        $videos = [
            [
                'prestataire_id' => $prestataireIds[array_rand($prestataireIds)],
                'title' => 'Présentation de nos services',
                'description' => 'Découvrez nos services exceptionnels et notre expertise dans le domaine.',
                'video_path' => 'videos/F2uqUZBx8wbvkty6ieECIDbW3tBnk48laJslhI4S.mp4',
                'is_public' => true,
                'duration' => 120,
                'status' => 'approved',
                'views_count' => 0,
                'likes_count' => 0,
                'comments_count' => 0,
                'shares_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prestataire_id' => $prestataireIds[array_rand($prestataireIds)],
                'title' => 'Témoignage client',
                'description' => 'Hear what our satisfied customers have to say about our services.',
                'video_path' => 'videos/OjiFvRlILIYvPh2amA1sSFFMQJyFDE5qVPqijsNF.mov',
                'is_public' => true,
                'duration' => 90,
                'status' => 'approved',
                'views_count' => 0,
                'likes_count' => 0,
                'comments_count' => 0,
                'shares_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prestataire_id' => $prestataireIds[array_rand($prestataireIds)],
                'title' => 'Behind the scenes',
                'description' => 'A look behind the scenes at our daily operations.',
                'video_path' => 'videos/687a33108e797.webm',
                'is_public' => true,
                'duration' => 45,
                'status' => 'approved',
                'views_count' => 0,
                'likes_count' => 0,
                'comments_count' => 0,
                'shares_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert videos
        DB::table('videos')->insert($videos);
        
        echo "Seeded " . count($videos) . " videos.\n";
    }
}