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
                'rate_limit' => '1M/1M',
                'download_speed' => '1M',
                'upload_speed' => '1M',
                'price' => 5.00,
                'validity_days' => null,
                'validity_hours' => 1,
                'data_limit' => null,
                'session_timeout' => 3600, // 1 hour in seconds
                'idle_timeout' => 600, // 10 minutes
                'is_active' => true,
                'description' => 'Basic 1-hour internet access with 1Mbps speed',
            ],
            [
                'name' => 'Standard 3 Hours',
                'rate_limit' => '2M/2M',
                'download_speed' => '2M',
                'upload_speed' => '2M',
                'price' => 10.00,
                'validity_days' => null,
                'validity_hours' => 3,
                'data_limit' => null,
                'session_timeout' => 10800, // 3 hours
                'idle_timeout' => 600,
                'is_active' => true,
                'description' => 'Standard 3-hour access with 2Mbps speed',
            ],
            [
                'name' => 'Premium Daily',
                'rate_limit' => '5M/5M',
                'download_speed' => '5M',
                'upload_speed' => '5M',
                'price' => 25.00,
                'validity_days' => 1,
                'validity_hours' => null,
                'data_limit' => null,
                'session_timeout' => null, // unlimited
                'idle_timeout' => 1200,
                'is_active' => true,
                'description' => 'Full day access with 5Mbps speed',
            ],
            [
                'name' => 'Weekly Pack',
                'rate_limit' => '3M/3M',
                'download_speed' => '3M',
                'upload_speed' => '3M',
                'price' => 50.00,
                'validity_days' => 7,
                'validity_hours' => null,
                'data_limit' => 10737418240, // 10GB in bytes
                'session_timeout' => null,
                'idle_timeout' => 1800,
                'is_active' => true,
                'description' => '7-day access with 10GB data limit and 3Mbps speed',
            ],
            [
                'name' => 'Monthly Unlimited',
                'rate_limit' => '10M/10M',
                'download_speed' => '10M',
                'upload_speed' => '10M',
                'price' => 150.00,
                'validity_days' => 30,
                'validity_hours' => null,
                'data_limit' => null,
                'session_timeout' => null,
                'idle_timeout' => 3600,
                'is_active' => true,
                'description' => '30-day unlimited access with 10Mbps speed',
            ],
            [
                'name' => 'Family Monthly',
                'rate_limit' => '20M/20M',
                'download_speed' => '20M',
                'upload_speed' => '20M',
                'price' => 250.00,
                'validity_days' => 30,
                'validity_hours' => null,
                'data_limit' => null,
                'session_timeout' => null,
                'idle_timeout' => 3600,
                'is_active' => true,
                'description' => '30-day family plan with 20Mbps speed',
            ],
            [
                'name' => 'Business Premium',
                'rate_limit' => '50M/50M',
                'download_speed' => '50M',
                'upload_speed' => '50M',
                'price' => 500.00,
                'validity_days' => 30,
                'validity_hours' => null,
                'data_limit' => null,
                'session_timeout' => null,
                'idle_timeout' => 7200,
                'is_active' => true,
                'description' => 'Business-grade 30-day plan with 50Mbps speed',
            ],
            [
                'name' => 'Student Special',
                'rate_limit' => '2M/2M',
                'download_speed' => '2M',
                'upload_speed' => '2M',
                'price' => 30.00,
                'validity_days' => 7,
                'validity_hours' => null,
                'data_limit' => 5368709120, // 5GB in bytes
                'session_timeout' => null,
                'idle_timeout' => 900,
                'is_active' => true,
                'description' => 'Student special: 7 days with 5GB data and 2Mbps speed',
            ],
            [
                'name' => 'Night Owl',
                'rate_limit' => '5M/5M',
                'download_speed' => '5M',
                'upload_speed' => '5M',
                'price' => 15.00,
                'validity_days' => null,
                'validity_hours' => 12,
                'data_limit' => null,
                'session_timeout' => 43200, // 12 hours
                'idle_timeout' => 600,
                'is_active' => true,
                'description' => '12-hour access with 5Mbps speed (perfect for overnight use)',
            ],
            [
                'name' => 'Legacy Plan',
                'rate_limit' => '512k/512k',
                'download_speed' => '512k',
                'upload_speed' => '512k',
                'price' => 3.00,
                'validity_days' => null,
                'validity_hours' => 2,
                'data_limit' => null,
                'session_timeout' => 7200,
                'idle_timeout' => 300,
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
