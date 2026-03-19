<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionPlan::create([
            'name' => 'Basic',
            'description' => 'Perfect for getting started',
            'price' => 9.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'booking_limit' => 30,
            'includes_analytics' => false,
            'includes_priority_support' => false,
            'commission_rate' => 5.0,
            'features' => json_encode([
                'Up to 30 bookings per month',
                'Basic messaging',
                'Standard support',
                'Basic reviews',
                'Profile customization',
            ]),
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Professional',
            'description' => 'For growing professionals',
            'price' => 29.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'booking_limit' => 100,
            'includes_analytics' => true,
            'includes_priority_support' => false,
            'commission_rate' => 5.0,
            'features' => json_encode([
                'Up to 100 bookings per month',
                'Advanced messaging',
                'Email support',
                'Detailed analytics',
                'Advanced reviews & ratings',
                'Portfolio showcase',
                'Custom branding options',
                'Social media integration',
            ]),
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Enterprise',
            'description' => 'For established businesses',
            'price' => 99.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'booking_limit' => null, // Unlimited
            'includes_analytics' => true,
            'includes_priority_support' => true,
            'commission_rate' => 3.0,
            'features' => json_encode([
                'Unlimited bookings',
                'Priority messaging & support',
                '24/7 dedicated support',
                'Advanced analytics & reporting',
                'Team management',
                'API access',
                'Custom integrations',
                'White-label options',
                'Advanced security features',
                'Priority advertising placement',
            ]),
            'is_active' => true,
        ]);
    }
}

