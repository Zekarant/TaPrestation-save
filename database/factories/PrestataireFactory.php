<?php

namespace Database\Factories;

use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prestataire>
 */
class PrestataireFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Prestataire::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'secteur_activite' => fake()->randomElement([
                'Développement Web', 'Design Graphique', 'Marketing Digital',
                'Photographie', 'Rédaction', 'Traduction', 'Consulting',
                'Formation', 'Maintenance IT', 'Architecture'
            ]),
            'description' => fake()->paragraph(3),
            'years_experience' => fake()->numberBetween(1, 20),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'France',
            'is_approved' => fake()->boolean(70), // 70% de chance d'être approuvé
            'photo' => fake()->optional()->imageUrl(300, 300, 'people'),
        ];
    }

    /**
     * Indicate that the prestataire is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the prestataire is not approved.
     */
    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the prestataire is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}