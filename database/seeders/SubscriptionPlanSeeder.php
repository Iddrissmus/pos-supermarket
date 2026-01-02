<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter (Single Branch)',
                'slug' => 'starter',
                'price' => 1.00,
                'max_branches' => 1,
                'features' => [
                    'Single Location',
                    'Basic Reporting',
                    'Standard Support'
                ],
                'description' => 'Perfect for single shops',
                'is_active' => true,
            ],
            [
                'name' => 'Growth (Up to 5 Branches)',
                'slug' => 'growth',
                'price' => 3.00,
                'max_branches' => 5,
                'features' => [
                    'Up to 5 Locations',
                    'Advanced Analytics',
                    'Stock Transfers',
                    'Priority Support'
                ],
                'description' => 'For expanding businesses',
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise (Unlimited)',
                'slug' => 'enterprise',
                'price' => 10.00,
                'max_branches' => 999,
                'features' => [
                    'Unlimited Locations',
                    'Dedicated Account Manager',
                    'API Access',
                    'Custom Reports'
                ],
                'description' => 'For large chains',
                'is_active' => true,
            ]
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
