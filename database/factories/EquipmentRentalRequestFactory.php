<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EquipmentRentalRequest;
use App\Models\Equipment;
use App\Models\Client;
use App\Models\Prestataire;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EquipmentRentalRequest>
 */
class EquipmentRentalRequestFactory extends Factory
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
        $durationDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $unitPrice = $this->faker->randomFloat(2, 50, 500);
        $totalAmount = $unitPrice * $durationDays;
        $deliveryFee = $this->faker->randomFloat(2, 0, 50);
        $securityDeposit = $this->faker->randomFloat(2, 100, 1000);
        
        return [
            'equipment_id' => Equipment::factory(),
            'client_id' => Client::factory(),
            'prestataire_id' => Prestataire::factory(),
            'request_number' => 'REQ-' . strtoupper($this->faker->unique()->bothify('??######')),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected', 'cancelled']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'security_deposit' => $securityDeposit,
            'delivery_fee' => $deliveryFee,
            'final_amount' => $totalAmount + $deliveryFee,
            'delivery_required' => $this->faker->boolean(),
            'delivery_address' => $this->faker->optional()->address(),
            'pickup_address' => $this->faker->optional()->address(),
        ];
    }

    /**
     * Indicate that the request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the request is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'prestataire_response_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => $this->faker->sentence(),
            'prestataire_response_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the request is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancellation_reason' => $this->faker->sentence(),
            'cancelled_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}