<?php

namespace App\Services;

use App\Models\Router;
use RouterOS\Client;
use RouterOS\Query;
use Exception;
use Illuminate\Support\Facades\Log;

class MikroTikService
{
    protected $client;
    protected $router;
    protected $connectionTimeout;
    protected $attempts;
    protected $delay;

    public function __construct(?Router $router = null)
    {
        // Load connection settings from config
        $this->connectionTimeout = config('mikrotik.connection_timeout', 5);
        $this->attempts = config('mikrotik.attempts', 3);
        $this->delay = config('mikrotik.delay', 1);

        if ($router) {
            $this->router = $router;
            $this->connect($router);
        }
    }

    /**
     * Connect to MikroTik router with retry logic
     */
    public function connect(Router $router): bool
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            try {
                // Use VPN IP if enabled, otherwise use direct IP
                $connectIp = $router->getConnectionIp();

                Log::info("Attempting to connect to MikroTik router {$router->name} (Attempt {$attempt}/{$this->attempts})", [
                    'ip' => $connectIp,
                    'port' => $router->api_port,
                    'via_vpn' => $router->usesVpn(),
                ]);

                $this->client = new Client([
                    'host' => $connectIp,
                    'user' => $router->username,
                    'pass' => $router->decryptedPassword(),
                    'port' => $router->api_port,
                    'timeout' => $this->connectionTimeout,
                ]);

                // Test connection by getting system identity
                $query = new Query('/system/identity/print');
                $this->client->query($query)->read();

                $this->router = $router;
                $router->updateStatus('online');

                Log::info("Successfully connected to MikroTik router {$router->name}");
                return true;

            } catch (Exception $e) {
                $lastException = $e;
                Log::warning("MikroTik connection attempt {$attempt} failed for {$router->name}: " . $e->getMessage());

                // Wait before retrying (except on last attempt)
                if ($attempt < $this->attempts) {
                    sleep($this->delay);
                }
            }
        }

        // All attempts failed
        Log::error("All MikroTik connection attempts failed for {$router->name}: " . $lastException->getMessage());
        $router->updateStatus('offline');
        return false;
    }

    /**
     * Test connection to router
     */
    public function testConnection(Router $router): array
    {
        try {
            // Use VPN IP if enabled
            $connectIp = $router->getConnectionIp();

            $client = new Client([
                'host' => $connectIp,
                'user' => $router->username,
                'pass' => $router->decryptedPassword(),
                'port' => $router->api_port,
            ]);

            // Try to get system resource
            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();

            return [
                'success' => true,
                'message' => 'Connection successful',
                'data' => $response[0] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get system resources
     */
    public function getSystemResource(): ?array
    {
        try {
            $query = new Query('/system/resource/print');
            $response = $this->client->query($query)->read();
            return $response[0] ?? null;
        } catch (Exception $e) {
            Log::error('Failed to get system resource: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create hotspot user
     */
    public function createHotspotUser(string $username, string $password, string $profile): bool
    {
        try {
            $query = (new Query('/ip/hotspot/user/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('profile', $profile);

            $this->client->query($query)->read();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to create hotspot user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove hotspot user
     */
    public function removeHotspotUser(string $username): bool
    {
        try {
            // Find user ID
            $query = (new Query('/ip/hotspot/user/print'))
                ->where('name', $username);

            $users = $this->client->query($query)->read();

            if (empty($users)) {
                return false;
            }

            $userId = $users[0]['.id'];

            // Remove user
            $query = (new Query('/ip/hotspot/user/remove'))
                ->equal('.id', $userId);

            $this->client->query($query)->read();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to remove hotspot user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update hotspot user
     */
    public function updateHotspotUser(string $username, array $data): bool
    {
        try {
            // Find user ID
            $query = (new Query('/ip/hotspot/user/print'))
                ->where('name', $username);

            $users = $this->client->query($query)->read();

            if (empty($users)) {
                return false;
            }

            $userId = $users[0]['.id'];

            // Build update query
            $query = (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $userId);

            foreach ($data as $key => $value) {
                $query->equal($key, $value);
            }

            $this->client->query($query)->read();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to update hotspot user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable hotspot user
     */
    public function enableHotspotUser(string $username): bool
    {
        return $this->updateHotspotUser($username, ['disabled' => 'no']);
    }

    /**
     * Disable hotspot user
     */
    public function disableHotspotUser(string $username): bool
    {
        return $this->updateHotspotUser($username, ['disabled' => 'yes']);
    }

    /**
     * Get all hotspot users
     */
    public function getHotspotUsers(): array
    {
        try {
            $query = new Query('/ip/hotspot/user/print');
            return $this->client->query($query)->read();
        } catch (Exception $e) {
            Log::error('Failed to get hotspot users: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get hotspot user by username
     */
    public function getHotspotUser(string $username): ?array
    {
        try {
            $query = (new Query('/ip/hotspot/user/print'))
                ->where('name', $username);

            $users = $this->client->query($query)->read();
            return $users[0] ?? null;
        } catch (Exception $e) {
            Log::error('Failed to get hotspot user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get active hotspot connections
     */
    public function getActiveConnections(): array
    {
        try {
            $query = new Query('/ip/hotspot/active/print');
            return $this->client->query($query)->read();
        } catch (Exception $e) {
            Log::error('Failed to get active connections: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Disconnect user session
     */
    public function disconnectUser(string $username): bool
    {
        try {
            // Find active session
            $query = (new Query('/ip/hotspot/active/print'))
                ->where('user', $username);

            $sessions = $this->client->query($query)->read();

            if (empty($sessions)) {
                return false;
            }

            $sessionId = $sessions[0]['.id'];

            // Remove session
            $query = (new Query('/ip/hotspot/active/remove'))
                ->equal('.id', $sessionId);

            $this->client->query($query)->read();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to disconnect user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create user profile
     */
    public function createProfile(string $name, array $settings): bool
    {
        try {
            $query = (new Query('/ip/hotspot/user/profile/add'))
                ->equal('name', $name);

            foreach ($settings as $key => $value) {
                $query->equal($key, $value);
            }

            $this->client->query($query)->read();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to create profile: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all profiles
     */
    public function getProfiles(): array
    {
        try {
            $query = new Query('/ip/hotspot/user/profile/print');
            return $this->client->query($query)->read();
        } catch (Exception $e) {
            Log::error('Failed to get profiles: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get traffic statistics for a user
     */
    public function getUserTraffic(string $username): ?array
    {
        try {
            $query = (new Query('/ip/hotspot/active/print'))
                ->where('user', $username);

            $sessions = $this->client->query($query)->read();
            return $sessions[0] ?? null;
        } catch (Exception $e) {
            Log::error('Failed to get user traffic: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get router identity
     */
    public function getIdentity(): ?string
    {
        try {
            $query = new Query('/system/identity/print');
            $response = $this->client->query($query)->read();
            return $response[0]['name'] ?? null;
        } catch (Exception $e) {
            Log::error('Failed to get identity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get router uptime
     */
    public function getUptime(): ?string
    {
        try {
            $query = new Query('/system/resource/print');
            $response = $this->client->query($query)->read();
            return $response[0]['uptime'] ?? null;
        } catch (Exception $e) {
            Log::error('Failed to get uptime: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get hotspot server status
     */
    public function getHotspotServers(): array
    {
        try {
            $query = new Query('/ip/hotspot/print');
            return $this->client->query($query)->read();
        } catch (Exception $e) {
            Log::error('Failed to get hotspot servers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync hotspot users from router to database
     */
    public function syncHotspotUsers(): array
    {
        $routerUsers = $this->getHotspotUsers();
        $synced = [];
        $errors = [];

        foreach ($routerUsers as $routerUser) {
            try {
                // Logic to sync with database would go here
                $synced[] = $routerUser['name'];
            } catch (Exception $e) {
                $errors[] = [
                    'user' => $routerUser['name'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'synced' => $synced,
            'errors' => $errors,
            'total' => count($routerUsers),
        ];
    }
}
