<?php

namespace Database\Seeders;

use App\Models\Voucher;
use App\Models\BandwidthPlan;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = BandwidthPlan::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->get();
        $users = User::all();

        if ($plans->isEmpty() || $customers->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Skipping VoucherSeeder: Required data (plans, customers, or users) not found');
            return;
        }

        $vouchers = [];

        // Generate some sold and used vouchers for each customer
        foreach ($customers->take(10) as $index => $customer) {
            // Each customer gets 2-5 vouchers
            $voucherCount = rand(2, 5);

            for ($i = 0; $i < $voucherCount; $i++) {
                $plan = $plans->random();
                $soldAt = now()->subDays(rand(1, 180));
                $status = $this->determineStatus($soldAt, $plan);

                $voucher = [
                    'bandwidth_plan_id' => $plan->id,
                    'router_id' => null,
                    'customer_id' => $customer->id,
                    'code' => $this->generateUniqueCode(),
                    'password' => $this->generatePassword(),
                    'status' => $status,
                    'price' => $plan->price,
                    'sold_at' => $soldAt,
                    'sold_by' => $users->random()->id,
                    'activated_at' => null,
                    'expires_at' => null,
                    'mac_address' => null,
                    'notes' => null,
                    'created_at' => $soldAt->copy()->subHours(rand(1, 24)),
                ];

                // Set activation and expiry dates for used vouchers
                if ($status === 'used') {
                    $activatedAt = $soldAt->copy()->addHours(rand(1, 48));
                    $validityDays = $plan->validity_days ?? 1;
                    $voucher['activated_at'] = $activatedAt;
                    $voucher['expires_at'] = $activatedAt->copy()->addDays($validityDays);
                }

                // Set expiry for expired vouchers
                if ($status === 'expired') {
                    $validityDays = $plan->validity_days ?? 1;
                    $voucher['expires_at'] = $soldAt->copy()->addDays($validityDays);
                }

                $vouchers[] = $voucher;
            }
        }

        // Generate some unsold vouchers for each plan
        foreach ($plans as $plan) {
            $unsoldCount = rand(5, 15);

            for ($i = 0; $i < $unsoldCount; $i++) {
                $createdAt = now()->subDays(rand(1, 30));

                $vouchers[] = [
                    'bandwidth_plan_id' => $plan->id,
                    'router_id' => null,
                    'customer_id' => null,
                    'code' => $this->generateUniqueCode(),
                    'password' => $this->generatePassword(),
                    'status' => 'active',
                    'price' => $plan->price,
                    'sold_at' => null,
                    'sold_by' => null,
                    'activated_at' => null,
                    'expires_at' => null,
                    'mac_address' => null,
                    'notes' => null,
                    'created_at' => $createdAt,
                ];
            }
        }

        // Generate some disabled vouchers
        foreach ($plans->take(3) as $plan) {
            for ($i = 0; $i < 3; $i++) {
                $vouchers[] = [
                    'bandwidth_plan_id' => $plan->id,
                    'customer_id' => null,
                    'code' => $this->generateUniqueCode(),
                    'password' => $this->generatePassword(),
                    'status' => 'disabled',
                    'price' => $plan->price,
                    'sold_at' => null,
                    'sold_by' => null,
                    'activated_at' => null,
                    'expires_at' => null,
                    'created_at' => now()->subDays(rand(5, 60)),
                ];
            }
        }

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }

        $this->command->info('Created ' . count($vouchers) . ' vouchers');
    }

    /**
     * Generate a unique voucher code
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(4) . '-' . Str::random(4));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generate a voucher password
     */
    private function generatePassword(): string
    {
        return strtolower(Str::random(8));
    }

    /**
     * Determine voucher status based on sold date and plan
     */
    private function determineStatus($soldAt, $plan): string
    {
        $random = rand(1, 100);

        // 60% chance the voucher has been used
        if ($random <= 60) {
            $activatedAt = $soldAt->copy()->addHours(rand(1, 48));
            $expiresAt = $activatedAt->copy()->addDays($plan->validity_period);

            // Check if it should be expired
            if ($expiresAt->isPast()) {
                return 'expired';
            }

            return 'used';
        }

        // 20% chance it's expired (sold but never used)
        if ($random <= 80) {
            $expiresAt = $soldAt->copy()->addDays($plan->validity_period);
            if ($expiresAt->isPast()) {
                return 'expired';
            }
        }

        // 20% chance it's still active (sold but not yet used)
        return 'active';
    }
}
