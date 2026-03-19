<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'prestataire_id' => Prestataire::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph(2),
            'status' => 'published',
        ];
    }

    /**
     * Indicate that the review has a high rating.
     */
    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'comment' => 'Excellent travail, très professionnel et à l\'écoute. Je recommande vivement!',
        ]);
    }

    /**
     * Indicate that the review has a low rating.
     */
    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(1, 2),
            'comment' => 'Le travail n\'était pas à la hauteur de mes attentes.',
        ]);
    }

    /**
     * Indicate that the review is pending moderation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}