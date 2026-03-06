<?php

namespace App\Services;

use App\Models\HotspotUser;
use App\Models\Voucher;
use App\Models\BandwidthPlan;
use App\Models\Router;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HotspotUserService
{
    protected $mikrotikService;

    public function __construct(MikroTikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Sync bandwidth plan profile to MikroTik router
     */
    public function syncProfileToRouter(Voucher $voucher): array
    {
        try {
            $router = $voucher->router;
            $bandwidthPlan = $voucher->bandwidthPlan;

            if (!$router || !$bandwidthPlan) {
                throw new Exception('Voucher must have both router and bandwidth plan');
            }

            // Connect to router
            if (!$this->mikrotikService->connect($router)) {
                throw new Exception('Failed to connect to router');
            }

            // Build profile name
            $profileName = $this->generateProfileName($voucher);

            // Prepare profile settings for MikroTik
            $settings = $this->buildProfileSettings($bandwidthPlan);

            // Create profile on MikroTik
            $success = $this->mikrotikService->createProfile($profileName, $settings);

            if ($success) {
                // Update voucher with MikroTik profile ID
                $voucher->update(['mikrotik_profile_id' => $profileName]);

                return [
                    'success' => true,
                    'message' => 'Profile synced successfully',
                    'profile_name' => $profileName,
                ];
            }

            throw new Exception('Failed to create profile on MikroTik');

        } catch (Exception $e) {
            Log::error('Failed to sync profile to router: ' . $e->getMessage(), [
                'voucher_id' => $voucher->id,
                'router_id' => $voucher->router_id,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate hotspot users for a voucher
     */
    public function generateHotspotUsers(Voucher $voucher, int $count, ?int $soldBy = null, ?int $transactionId = null): array
    {
        $generated = [];
        $errors = [];

        // Check if voucher has capacity
        $availableCapacity = $voucher->getRemainingCapacity();
        if ($count > $availableCapacity) {
            return [
                'success' => false,
                'message' => "Requested {$count} users but only {$availableCapacity} slots available",
                'generated' => [],
                'errors' => [],
            ];
        }

        // Ensure profile is synced to router
        if (!$voucher->mikrotik_profile_id) {
            $syncResult = $this->syncProfileToRouter($voucher);
            if (!$syncResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to sync profile to router: ' . $syncResult['message'],
                    'generated' => [],
                    'errors' => [],
                ];
            }
        }

        DB::beginTransaction();

        try {
            // Connect to router
            if (!$this->mikrotikService->connect($voucher->router)) {
                throw new Exception('Failed to connect to router');
            }

            for ($i = 0; $i < $count; $i++) {
                try {
                    // Generate unique credentials
                    $username = $this->generateUsername();
                    $password = $this->generatePassword();

                    // Create hotspot user in database
                    $hotspotUser = HotspotUser::create([
                        'voucher_id' => $voucher->id,
                        'router_id' => $voucher->router_id,
                        'bandwidth_plan_id' => $voucher->bandwidth_plan_id,
                        'username' => $username,
                        'password' => $password,
                        'status' => 'pending',
                        'synced_to_router' => false,
                        'sold_by' => $soldBy,
                        'transaction_id' => $transactionId
                    ]);

                    // Try to create user on MikroTik
                    $mikrotikSuccess = $this->mikrotikService->createHotspotUser(
                        $username,
                        $password,
                        $voucher->mikrotik_profile_id
                    );

                    if ($mikrotikSuccess) {
                        $hotspotUser->markAsSynced($username);
                        $voucher->incrementUsersGenerated();

                        $generated[] = [
                            'id' => $hotspotUser->id,
                            'username' => $username,
                            'password' => $password,
                        ];
                    } else {
                        // User created in DB but failed on router
                        $hotspotUser->markAsSyncFailed('Failed to create user on MikroTik router');

                        $errors[] = [
                            'username' => $username,
                            'error' => 'Failed to create on MikroTik (saved in database only)',
                        ];
                    }

                } catch (Exception $e) {
                    $errors[] = [
                        'username' => $username ?? 'N/A',
                        'error' => $e->getMessage(),
                    ];

                    Log::error('Failed to generate hotspot user', [
                        'voucher_id' => $voucher->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => sprintf('Generated %d users (%d synced, %d errors)',
                    count($generated) + count($errors),
                    count($generated),
                    count($errors)
                ),
                'generated' => $generated,
                'errors' => $errors,
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to generate hotspot users: ' . $e->getMessage(), [
                'voucher_id' => $voucher->id,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'generated' => $generated,
                'errors' => $errors,
            ];
        }
    }

    /**
     * Retry syncing failed users to router
     */
    public function retrySyncFailedUsers(Voucher $voucher): array
    {
        $failedUsers = $voucher->hotspotUsers()->notSynced()->get();

        if ($failedUsers->isEmpty()) {
            return [
                'success' => true,
                'message' => 'No failed users to retry',
                'synced' => 0,
                'errors' => 0,
            ];
        }

        // Connect to router
        if (!$this->mikrotikService->connect($voucher->router)) {
            return [
                'success' => false,
                'message' => 'Failed to connect to router',
                'synced' => 0,
                'errors' => 0,
            ];
        }

        $synced = 0;
        $errors = 0;

        foreach ($failedUsers as $user) {
            try {
                $success = $this->mikrotikService->createHotspotUser(
                    $user->username,
                    $user->password,
                    $voucher->mikrotik_profile_id
                );

                if ($success) {
                    $user->markAsSynced($user->username);
                    $synced++;
                } else {
                    $errors++;
                }
            } catch (Exception $e) {
                $user->markAsSyncFailed($e->getMessage());
                $errors++;
            }
        }

        return [
            'success' => true,
            'message' => "Retry complete: {$synced} synced, {$errors} failed",
            'synced' => $synced,
            'errors' => $errors,
        ];
    }

    /**
     * Generate unique username
     */
    protected function generateUsername(): string
    {
        do {
            $username = 'user_' . strtolower(Str::random(8));
        } while (HotspotUser::where('username', $username)->exists());

        return $username;
    }

    /**
     * Generate secure password
     */
    protected function generatePassword(): string
    {
        return Str::random(12);
    }

    /**
     * Generate profile name for voucher
     */
    protected function generateProfileName(Voucher $voucher): string
    {
        $bandwidthPlan = $voucher->bandwidthPlan;
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', $bandwidthPlan->name);
        return "elgiotik_{$cleanName}_{$voucher->id}";
    }

    /**
     * Build MikroTik profile settings from bandwidth plan
     */
    protected function buildProfileSettings(BandwidthPlan $bandwidthPlan): array
    {
        $settings = [];

        // Rate limit (speed)
        if ($bandwidthPlan->rate_limit) {
            $settings['rate-limit'] = $bandwidthPlan->rate_limit;
        } elseif ($bandwidthPlan->download_speed || $bandwidthPlan->upload_speed) {
            $upload = $bandwidthPlan->upload_speed ?: '0';
            $download = $bandwidthPlan->download_speed ?: '0';
            $settings['rate-limit'] = "{$upload}/{$download}";
        }

        // Session timeout
        if ($bandwidthPlan->session_timeout) {
            $settings['session-timeout'] = $this->formatTime($bandwidthPlan->session_timeout);
        }

        // Idle timeout
        if ($bandwidthPlan->idle_timeout) {
            $settings['idle-timeout'] = $this->formatTime($bandwidthPlan->idle_timeout);
        }

        // Shared users (limit connections)
        $settings['shared-users'] = '1';

        return $settings;
    }

    /**
     * Format seconds to MikroTik time format
     */
    protected function formatTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
