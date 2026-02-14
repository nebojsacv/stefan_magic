<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'package_name' => 'Free Trial',
                'price' => 0,
                'assessments_allowed' => 3,
                'billing_cycle' => 'monthly',
                'features' => [
                    'ai_cost_limit' => 0,
                    'support' => 'email',
                    'priority_support' => false,
                    'custom_branding' => false,
                    'api_access' => false,
                ],
                'is_active' => true,
            ],
            [
                'package_name' => 'Basic',
                'price' => 49.00,
                'assessments_allowed' => 10,
                'billing_cycle' => 'monthly',
                'features' => [
                    'ai_cost_limit' => 50,
                    'support' => 'email',
                    'priority_support' => false,
                    'custom_branding' => false,
                    'api_access' => false,
                ],
                'is_active' => true,
            ],
            [
                'package_name' => 'Professional',
                'price' => 149.00,
                'assessments_allowed' => 50,
                'billing_cycle' => 'monthly',
                'features' => [
                    'ai_cost_limit' => 200,
                    'support' => 'email_chat',
                    'priority_support' => true,
                    'custom_branding' => true,
                    'api_access' => true,
                ],
                'is_active' => true,
            ],
            [
                'package_name' => 'Enterprise',
                'price' => 499.00,
                'assessments_allowed' => -1, // Unlimited
                'billing_cycle' => 'monthly',
                'features' => [
                    'ai_cost_limit' => 1000,
                    'support' => 'dedicated',
                    'priority_support' => true,
                    'custom_branding' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'sso' => true,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($packages as $packageData) {
            Package::create($packageData);
        }

        $this->command->info('✅ Created 4 subscription packages');
    }
}
