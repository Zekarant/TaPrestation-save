<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryProvider;

class DeliveryProviderSeeder extends Seeder
{
    public function run()
    {
        $providers = [
            [
                'name' => 'Colissimo',
                'code' => 'colissimo',
                'is_active' => true,
                'base_rate' => 8.50,
                'per_km_rate' => 0.05,
                'estimated_days' => 3,
                'delivery_type' => 'standard',
            ],
            [
                'name' => 'Chronopost',
                'code' => 'chronopost',
                'is_active' => true,
                'base_rate' => 15.00,
                'per_km_rate' => 0.10,
                'estimated_days' => 1,
                'delivery_type' => 'express',
            ],
            [
                'name' => 'Mondial Relay',
                'code' => 'mondial-relay',
                'is_active' => true,
                'base_rate' => 4.50,
                'per_km_rate' => 0.03,
                'estimated_days' => 5,
                'delivery_type' => 'standard',
            ],
            [
                'name' => 'UPS',
                'code' => 'ups',
                'is_active' => true,
                'base_rate' => 12.00,
                'per_km_rate' => 0.08,
                'estimated_days' => 2,
                'delivery_type' => 'express',
            ],
        ];

        foreach ($providers as $provider) {
            DeliveryProvider::updateOrCreate(
                ['code' => $provider['code']],
                $provider
            );
        }
    }
}
