<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use App\Models\Equipment;
use App\Models\Client;
use App\Models\Prestataire;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EquipmentRental>
 */
class EquipmentRentalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 months');
        $plannedDurationDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $unitPrice = $this->faker->randomFloat(2, 50, 500);
        $baseAmount = $unitPrice * $plannedDurationDays;
        $deliveryFee = $this->faker->randomFloat(2, 0, 50);
        $securityDeposit = $this->faker->randomFloat(2, 100, 1000);
        
        return [
            'rental_request_id' => EquipmentRentalRequest::factory(),
            'equipment_id' => Equipment::factory(),
            'client_id' => Client::factory(),
            'prestataire_id' => Prestataire::factory(),
            'rental_number' => 'RNT-' . strtoupper($this->faker->unique()->bothify('??######')),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'planned_duration_days' => $plannedDurationDays,
            'unit_price' => $unitPrice,
            'base_amount' => $baseAmount,
            'security_deposit' => $securityDeposit,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $baseAmount + $deliveryFee,
            'final_amount' => $baseAmount + $deliveryFee,
            'status' => $this->faker->randomElement(['confirmed', 'in_preparation', 'in_progress', 'returned', 'completed', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'partially_paid', 'refunded', 'failed']),
            'delivery_address' => $this->faker->optional()->address(),
            'pickup_address' => $this->faker->optional()->address(),
        ];
    }

    /**
     * Indicate that the rental is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the rental is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'delivered_at' => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the rental is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'delivered_at' => now()->subDays(3),
            'picked_up_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the rental is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}