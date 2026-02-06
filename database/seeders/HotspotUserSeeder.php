<?php

namespace Database\Seeders;

use App\Models\HotspotUser;
use App\Models\Voucher;
use App\Models\Router;
use Illuminate\Database\Seeder;

class HotspotUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usedVouchers = Voucher::where('status', 'used')
            ->with(['bandwidthPlan', 'customer', 'router'])
            ->get();

        $routers = Router::where('is_active', true)->get();

        if ($usedVouchers->isEmpty() || $routers->isEmpty()) {
            $this->command->warn('Skipping HotspotUserSeeder: No used vouchers or active routers found');
            return;
        }

        $hotspotUsers = [];

        foreach ($usedVouchers as $voucher) {
            if (!$voucher->customer) {
                continue;
            }

            $router = $routers->random();
            $plan = $voucher->bandwidthPlan;

            // Generate realistic MAC address
            $macAddress = sprintf(
                '%02X:%02X:%02X:%02X:%02X:%02X',
                rand(0, 255),
                rand(0, 255),
                rand(0, 255),
                rand(0, 255),
                rand(0, 255),
                rand(0, 255)
            );

            // Calculate usage data
            $totalBytes = $this->calculateTotalBytes($voucher->activated_at);
            $uploadBytes = intval($totalBytes * rand(20, 40) / 100); // 20-40% upload
            $downloadBytes = $totalBytes - $uploadBytes;

            // Calculate session time
            $sessionTime = $this->calculateSessionTime($voucher->activated_at, $plan->validity_hours ?? null);

            // Determine if currently online (10% chance if not expired)
            $isOnline = (!$voucher->expires_at || $voucher->expires_at->isFuture()) && rand(1, 100) <= 10;

            // Determine status
            $status = 'active';
            if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                $status = 'expired';
                $isOnline = false;
            }

            $hotspotUser = [
                'username' => $voucher->code,
                'password' => substr(strtolower(str_replace('-', '', $voucher->code)), 0, 8),
                'router_id' => $router->id,
                'bandwidth_plan_id' => $plan->id,
                'customer_id' => $voucher->customer_id,
                'voucher_id' => $voucher->id,
                'status' => $status,
                'mac_address' => $macAddress,
                'ip_address' => $this->generateIpAddress(),
                'bytes_in' => $uploadBytes,
                'bytes_out' => $downloadBytes,
                'session_time' => $sessionTime,
                'last_login_at' => $voucher->activated_at,
                'last_logout_at' => $isOnline ? null : now()->subMinutes(rand(30, 1440)),
                'expires_at' => $voucher->expires_at,
                'is_online' => $isOnline,
                'created_by' => $voucher->sold_by,
                'created_at' => $voucher->activated_at,
                'updated_at' => now(),
            ];

            $hotspotUsers[] = $hotspotUser;
        }

        foreach ($hotspotUsers as $user) {
            HotspotUser::create($user);
        }

        $this->command->info('Created ' . count($hotspotUsers) . ' hotspot users');
    }

    /**
     * Calculate total bytes used based on activation time
     */
    private function calculateTotalBytes($activatedAt): int
    {
        if (!$activatedAt) {
            return 0;
        }

        $hoursActive = now()->diffInHours($activatedAt);

        // Average 100MB - 500MB per hour of usage
        $mbPerHour = rand(100, 500);
        $totalMB = min($hoursActive * $mbPerHour, rand(5000, 50000)); // Cap at realistic values

        return $totalMB * 1024 * 1024; // Convert to bytes
    }

    /**
     * Calculate session time based on activation and validity hours
     */
    private function calculateSessionTime($activatedAt, $validityHours): int
    {
        if (!$activatedAt) {
            return 0;
        }

        $secondsSinceActivation = now()->diffInSeconds($activatedAt);

        // If there's a time limit in hours, convert to seconds
        if ($validityHours) {
            $limitSeconds = $validityHours * 3600;
            return min($secondsSinceActivation, $limitSeconds);
        }

        // No time limit, return actual time
        return $secondsSinceActivation;
    }

    /**
     * Generate a realistic private IP address
     */
    private function generateIpAddress(): string
    {
        // Generate IP in 10.5.5.x range
        return '10.5.5.' . rand(10, 254);
    }
}
