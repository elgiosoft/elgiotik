<?php

namespace Database\Seeders;

use App\Models\Router;
use Illuminate\Database\Seeder;

class RouterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routers = [
            [
                'name' => 'Main Gateway Router',
                'ip_address' => '192.168.88.1',
                'username' => 'admin',
                'password' => 'admin123',
                'api_port' => 8728,
                'is_active' => true,
                'status' => 'online',
                'location' => 'Central Office',
                'description' => 'Primary router handling main gateway and hotspot services',
                'last_seen_at' => now(),
            ],
            [
                'name' => 'Village North Router',
                'ip_address' => '192.168.88.2',
                'username' => 'admin',
                'password' => 'admin123',
                'api_port' => 8728,
                'is_active' => true,
                'status' => 'online',
                'location' => 'North Village',
                'description' => 'Router serving northern residential area',
                'last_seen_at' => now(),
            ],
            [
                'name' => 'Village South Router',
                'ip_address' => '192.168.88.3',
                'username' => 'admin',
                'password' => 'admin123',
                'api_port' => 8728,
                'is_active' => true,
                'status' => 'online',
                'location' => 'South Village',
                'description' => 'Router serving southern residential area',
                'last_seen_at' => now(),
            ],
            [
                'name' => 'Market Area Router',
                'ip_address' => '192.168.88.4',
                'username' => 'admin',
                'password' => 'admin123',
                'api_port' => 8728,
                'is_active' => true,
                'status' => 'online',
                'location' => 'Market District',
                'description' => 'Router for market and commercial area',
                'last_seen_at' => now(),
            ],
            [
                'name' => 'Backup Router',
                'ip_address' => '192.168.88.10',
                'username' => 'admin',
                'password' => 'admin123',
                'api_port' => 8728,
                'is_active' => false,
                'status' => 'offline',
                'location' => 'Central Office',
                'description' => 'Backup router for failover scenarios',
                'last_seen_at' => now()->subHours(2),
            ],
        ];

        foreach ($routers as $router) {
            Router::create($router);
        }

        $this->command->info('Created ' . count($routers) . ' routers');
    }
}
