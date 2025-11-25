<?php

namespace Database\Factories;

use App\Models\Video;
use App\Models\Prestataire;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'prestataire_id' => Prestataire::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'video_path' => 'videos/' . $this->faker->uuid . '.mp4',
            'is_public' => true,
            'duration' => $this->faker->numberBetween(30, 3600), // 30 seconds to 1 hour
            'status' => 'published',
            'views_count' => $this->faker->numberBetween(0, 1000),
            'likes_count' => $this->faker->numberBetween(0, 100),
            'comments_count' => $this->faker->numberBetween(0, 50),
            'shares_count' => $this->faker->numberBetween(0, 20),
        ];
    }
}