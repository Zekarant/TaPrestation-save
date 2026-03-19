<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UrgentSale;
use App\Models\Prestataire;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UrgentSale>
 */
class UrgentSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prestataire_id' => Prestataire::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 5000),
            'condition' => $this->faker->randomElement(['new', 'good', 'used', 'fair']),
            'category_id' => null, // Will be set when needed
            'photos' => json_encode([
                $this->faker->imageUrl(640, 480, 'products'),
                $this->faker->imageUrl(640, 480, 'products'),
            ]),
            'quantity' => $this->faker->numberBetween(1, 10),
            'location' => $this->faker->city(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'status' => $this->faker->randomElement(['active', 'sold', 'withdrawn']),
            'slug' => $this->faker->unique()->slug(),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'contact_count' => $this->faker->numberBetween(0, 50),
        ];
    }

    /**
     * Indicate that the urgent sale is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the urgent sale is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sold',
        ]);
    }
}