<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Equipment;
use App\Models\Prestataire;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
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
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'technical_specifications' => $this->faker->paragraph(),
            'photos' => json_encode([
                $this->faker->imageUrl(640, 480, 'equipment'),
                $this->faker->imageUrl(640, 480, 'equipment'),
            ]),
            'main_photo' => $this->faker->imageUrl(640, 480, 'equipment'),
            'price_per_hour' => $this->faker->randomFloat(2, 5, 50),
            'price_per_day' => $this->faker->randomFloat(2, 10, 500),
            'price_per_week' => $this->faker->randomFloat(2, 50, 2000),
            'price_per_month' => $this->faker->randomFloat(2, 200, 8000),
            'security_deposit' => $this->faker->randomFloat(2, 50, 1000),
            'delivery_fee' => $this->faker->randomFloat(2, 0, 50),
            'delivery_included' => $this->faker->boolean(),
            'condition' => $this->faker->randomElement(['excellent', 'very_good', 'good', 'fair']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'maintenance', 'rented', 'unavailable']),
            'is_available' => $this->faker->boolean(80),
            'available_from' => $this->faker->dateTimeBetween('now', '+1 month'),
            'available_until' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'minimum_rental_duration' => $this->faker->numberBetween(1, 7),
            'maximum_rental_duration' => $this->faker->numberBetween(30, 365),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'France',
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'delivery_radius' => $this->faker->numberBetween(0, 50),
            'rental_conditions' => $this->faker->paragraph(),
            'usage_instructions' => $this->faker->paragraph(),
            'safety_instructions' => $this->faker->paragraph(),
            'included_accessories' => json_encode([
                $this->faker->word(),
                $this->faker->word(),
            ]),
            'optional_accessories' => json_encode([
                $this->faker->word(),
            ]),
            'requires_license' => $this->faker->boolean(),
            'required_license_type' => $this->faker->randomElement(['B', 'C', 'D', null]),
            'minimum_age' => $this->faker->numberBetween(18, 25),
            'average_rating' => $this->faker->randomFloat(2, 1, 5),
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'total_rentals' => $this->faker->numberBetween(0, 50),
            'view_count' => $this->faker->numberBetween(0, 1000),
            'last_rented_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'metadata' => json_encode([
                'brand' => $this->faker->company(),
                'model' => $this->faker->word(),
                'year' => $this->faker->year(),
            ]),
            'featured' => $this->faker->boolean(10),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the equipment is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the equipment is rented.
     */
    public function rented(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rented',
        ]);
    }

    /**
     * Indicate that the equipment is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }
}