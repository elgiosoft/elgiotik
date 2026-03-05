<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class TestRouterConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:test {router_id? : The ID of the router to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to MikroTik router(s)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routerId = $this->argument('router_id');

        if ($routerId) {
            $this->testSingleRouter($routerId);
        } else {
            $this->testAllRouters();
        }

        return Command::SUCCESS;
    }

    /**
     * Test a single router connection
     */
    protected function testSingleRouter(int $routerId): void
    {
        $router = Router::find($routerId);

        if (!$router) {
            $this->error("Router with ID {$routerId} not found!");
            return;
        }

        $this->info("Testing connection to: {$router->name}");
        $this->info("IP Address: {$router->ip_address}");
        $this->info("API Port: {$router->api_port}");
        $this->newLine();

        $service = new MikroTikService();
        $result = $service->testConnection($router);

        if ($result['success']) {
            $this->info("✓ Connection successful!");

            if (isset($result['data'])) {
                $this->newLine();
                $this->info("Router Information:");
                $this->table(
                    ['Property', 'Value'],
                    [
                        ['Board Name', $result['data']['board-name'] ?? 'N/A'],
                        ['Version', $result['data']['version'] ?? 'N/A'],
                        ['Uptime', $result['data']['uptime'] ?? 'N/A'],
                        ['CPU Load', ($result['data']['cpu-load'] ?? '0') . '%'],
                        ['Free Memory', $this->formatBytes($result['data']['free-memory'] ?? 0)],
                        ['Total Memory', $this->formatBytes($result['data']['total-memory'] ?? 0)],
                    ]
                );
            }
        } else {
            $this->error("✗ Connection failed!");
            $this->error("Error: {$result['message']}");

            $this->newLine();
            $this->warn("Troubleshooting tips:");
            $this->warn("1. Verify VPN connection: ping {$router->ip_address}");
            $this->warn("2. Check if API service is enabled on router");
            $this->warn("3. Verify API port is correct (default: 8728)");
            $this->warn("4. Check firewall rules allow API access from this server");
            $this->warn("5. Verify username and password are correct");
        }
    }

    /**
     * Test all routers
     */
    protected function testAllRouters(): void
    {
        $routers = Router::all();

        if ($routers->isEmpty()) {
            $this->warn("No routers found in database.");
            return;
        }

        $this->info("Testing {$routers->count()} router(s)...");
        $this->newLine();

        $results = [];

        foreach ($routers as $router) {
            $this->info("Testing: {$router->name} ({$router->ip_address})...");

            $service = new MikroTikService();
            $result = $service->testConnection($router);

            $status = $result['success'] ? '✓ Online' : '✗ Offline';
            $results[] = [
                $router->id,
                $router->name,
                $router->ip_address,
                $router->api_port,
                $status,
            ];

            if (!$result['success']) {
                $this->line("  Error: {$result['message']}");
            }

            $this->newLine();
        }

        $this->info("Connection Test Summary:");
        $this->table(
            ['ID', 'Name', 'IP Address', 'Port', 'Status'],
            $results
        );

        $online = collect($results)->filter(fn($r) => str_contains($r[4], '✓'))->count();
        $offline = collect($results)->filter(fn($r) => str_contains($r[4], '✗'))->count();

        $this->newLine();
        $this->info("Online: {$online} | Offline: {$offline}");
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
