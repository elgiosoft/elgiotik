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
            ->with(['bandwidthPlan', 'customer'])
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
            $totalTime = $this->calculateSessionTime($voucher->activated_at, $plan->time_limit);

            // Determine if currently online (10% chance if not expired)
            $isOnline = (!$voucher->expires_at || $voucher->expires_at->isFuture()) && rand(1, 100) <= 10;

            $hotspotUser = [
                'voucher_id' => $voucher->id,
                'router_id' => $router->id,
                'username' => $voucher->code,
                'password' => $voucher->password,
                'mac_address' => $macAddress,
                'ip_address' => $this->generateIpAddress(),
                'profile' => $plan->name,
                'uptime' => $totalTime,
                'bytes_in' => $uploadBytes,
                'bytes_out' => $downloadBytes,
                'packets_in' => intval($uploadBytes / rand(500, 1500)),
                'packets_out' => intval($downloadBytes / rand(500, 1500)),
                'is_online' => $isOnline,
                'last_seen_at' => $isOnline ? now() : now()->subMinutes(rand(30, 1440)),
                'first_login_at' => $voucher->activated_at,
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
     * Calculate session time based on activation and time limit
     */
    private function calculateSessionTime($activatedAt, $timeLimit): int
    {
        if (!$activatedAt) {
            return 0;
        }

        $secondsSinceActivation = now()->diffInSeconds($activatedAt);

        // If there's a time limit, parse it
        if ($timeLimit) {
            $limitSeconds = $this->parseTimeLimit($timeLimit);
            return min($secondsSinceActivation, $limitSeconds);
        }

        // No time limit, return actual time
        return $secondsSinceActivation;
    }

    /**
     * Parse time limit string (e.g., "1h", "24h", "3h") to seconds
     */
    private function parseTimeLimit($timeLimit): int
    {
        if (preg_match('/(\d+)h/', $timeLimit, $matches)) {
            return intval($matches[1]) * 3600;
        }

        if (preg_match('/(\d+)m/', $timeLimit, $matches)) {
            return intval($matches[1]) * 60;
        }

        return 86400; // Default to 24 hours
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
