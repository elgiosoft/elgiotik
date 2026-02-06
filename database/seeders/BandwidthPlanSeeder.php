<?php

namespace Database\Seeders;

use App\Models\BandwidthPlan;
use Illuminate\Database\Seeder;

class BandwidthPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic 1 Hour',
                'bandwidth_limit' => '1M/1M',
                'time_limit' => '1h',
                'data_limit' => null,
                'price' => 5.00,
                'validity_period' => 1,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'is_active' => true,
                'description' => 'Basic 1-hour internet access with 1Mbps speed',
            ],
            [
                'name' => 'Standard 3 Hours',
                'bandwidth_limit' => '2M/2M',
                'time_limit' => '3h',
                'data_limit' => null,
                'price' => 10.00,
                'validity_period' => 1,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'is_active' => true,
                'description' => 'Standard 3-hour access with 2Mbps speed',
            ],
            [
                'name' => 'Premium Daily',
                'bandwidth_limit' => '5M/5M',
                'time_limit' => '24h',
                'data_limit' => null,
                'price' => 25.00,
                'validity_period' => 1,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'is_active' => true,
                'description' => 'Full day access with 5Mbps speed',
            ],
            [
                'name' => 'Weekly Pack',
                'bandwidth_limit' => '3M/3M',
                'time_limit' => null,
                'data_limit' => '10G',
                'price' => 50.00,
                'validity_period' => 7,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'is_active' => true,
                'description' => '7-day access with 10GB data limit and 3Mbps speed',
            ],
            [
                'name' => 'Monthly Unlimited',
                'bandwidth_limit' => '10M/10M',
                'time_limit' => null,
                'data_limit' => null,
                'price' => 150.00,
                'validity_period' => 30,
                'validity_unit' => 'days',
                'shared_users' => 2,
                'is_active' => true,
                'description' => '30-day unlimited access with 10Mbps speed, supports 2 devices',
            ],
            [
                'name' => 'Family Monthly',
                'bandwidth_limit' => '20M/20M',
                'time_limit' => null,
                'data_limit' => null,
                'price' => 250.00,
                'validity_period' => 30,
                'validity_unit' => 'days',
                'shared_users' => 5,
                'is_active' => true,
                'description' => '30-day family plan with 20Mbps speed, supports 5 devices',
            ],
            [
                'name' => 'Business Premium',
                'bandwidth_limit' => '50M/50M',
                'time_limit' => null,
                'data_limit' => null,
                'price' => 500.00,
                'validity_period' => 30,
                'validity_unit' => 'days',
                'shared_users' => 10,
                'is_active' => true,
                'description' => 'Business-grade 30-day plan with 50Mbps speed, supports 10 devices',
            ],
            [
                'name' => 'Student Special',
                'bandwidth_limit' => '2M/2M',
                'time_limit' => null,
                'data_limit' => '5G',
                'price' => 30.00,
                'validity_period' => 7,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'is_active' => true,
                'description' => 'Student special: 7 days with 5GB data and 2Mbps speed',
            ],
            [
                'name' => 'Night Owl',
                'bandwidth_limit' => '5M/5M',
                'time_limit' => '12h',
                'data_limit' => null,
                'price' => 15.00,
                'validity_period' => 1,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'is_active' => true,
                'description' => '12-hour access with 5Mbps speed (perfect for overnight use)',
            ],
            [
                'name' => 'Legacy Plan',
                'bandwidth_limit' => '512K/512K',
                'time_limit' => '2h',
                'data_limit' => null,
                'price' => 3.00,
                'validity_period' => 1,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'is_active' => false,
                'description' => 'Old legacy plan - no longer offered',
            ],
        ];

        foreach ($plans as $plan) {
            BandwidthPlan::create($plan);
        }

        $this->command->info('Created ' . count($plans) . ' bandwidth plans');
    }
}
