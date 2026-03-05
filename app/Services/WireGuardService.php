<?php

namespace App\Services;

use App\Models\Router;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class WireGuardService
{
    protected $configPath;
    protected $serverPublicKey;
    protected $vpnSubnet;
    protected $vpnPort;
    protected $serverEndpoint;

    public function __construct()
    {
        $this->configPath = config('mikrotik.vpn.config_path', '/etc/wireguard/wg0.conf');
        $this->vpnSubnet = config('mikrotik.vpn.subnet', '10.10.10.0/24');
        $this->vpnPort = config('mikrotik.vpn.port', 51820);

        // Get server endpoint and strip http/https protocol
        $endpoint = config('mikrotik.vpn.server_endpoint', config('app.url'));
        $this->serverEndpoint = preg_replace('#^https?://#', '', $endpoint);
    }

    /**
     * Generate WireGuard key pair
     */
    public function generateKeyPair(): array
    {
        try {
            // Generate private key
            $privateKey = Process::run('wg genkey')->throw()->output();
            $privateKey = trim($privateKey);

            // Generate public key from private key
            $publicKey = Process::run("echo '{$privateKey}' | wg pubkey")->throw()->output();
            $publicKey = trim($publicKey);

            return [
                'private_key' => $privateKey,
                'public_key' => $publicKey,
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate WireGuard keys: ' . $e->getMessage());
            throw new Exception('Failed to generate WireGuard keys. Ensure WireGuard is installed on the server.');
        }
    }

    /**
     * Get next available VPN IP address
     */
    public function getNextAvailableIp(): string
    {
        // Parse subnet to get base IP
        [$baseIp, $cidr] = explode('/', $this->vpnSubnet);
        $parts = explode('.', $baseIp);

        // Start from .11 (reserve .1-.10 for server/special use)
        $startOctet = 11;

        // Get all assigned IPs
        $assignedIps = Router::whereNotNull('vpn_ip')
            ->pluck('vpn_ip')
            ->toArray();

        // Find next available IP
        for ($i = $startOctet; $i < 254; $i++) {
            $testIp = "{$parts[0]}.{$parts[1]}.{$parts[2]}.{$i}";

            if (!in_array($testIp, $assignedIps)) {
                return $testIp;
            }
        }

        throw new Exception('No available VPN IP addresses in subnet ' . $this->vpnSubnet);
    }

    /**
     * Generate MikroTik configuration script for router
     */
    public function generateMikroTikConfig(Router $router): string
    {
        if (!$router->vpn_enabled || !$router->vpn_private_key) {
            throw new Exception('Router VPN not configured');
        }

        // Get server public key
        $serverPublicKey = $this->getServerPublicKey();

        $config = <<<RSC
# MikroTik WireGuard VPN Configuration for {$router->name}
# Generated automatically by ElgioTik
# Generated on: {now()->toDateTimeString()}
#
# INSTRUCTIONS:
# 1. Copy this entire script
# 2. Connect to your MikroTik router (WinBox, SSH, or WebFig)
# 3. Open New Terminal
# 4. Paste and execute this script
# 5. Router will automatically connect to ElgioTik VPN

# Configuration Variables
:local routerName "{$router->name}"
:local serverPublicKey "{$serverPublicKey}"
:local routerPrivateKey "{$router->vpn_private_key}"
:local serverEndpoint "{$this->serverEndpoint}"
:local serverPort {$this->vpnPort}
:local routerVpnIp "{$router->vpn_ip}"
:local vpnSubnet "{$this->vpnSubnet}"
:local serverVpnIp "{$this->getServerVpnIp()}"

:put "========================================"
:put "ElgioTik VPN Configuration"
:put "Router: \$routerName"
:put "========================================"
:put ""

# Remove existing WireGuard interface if exists
:put "Step 1: Cleaning up existing configuration..."
:if ([/interface wireguard find name=wireguard-elgiotik] != "") do={{
    :put "Removing existing WireGuard interface..."
    /interface wireguard remove [find name=wireguard-elgiotik]
}}

# Remove existing IP address
:if ([/ip address find comment="ElgioTik VPN IP"] != "") do={{
    /ip address remove [find comment="ElgioTik VPN IP"]
}}

:delay 1s
:put "Cleanup complete"
:put ""

# Create WireGuard interface
:put "Step 2: Creating WireGuard interface..."
/interface wireguard add \\
    name=wireguard-elgiotik \\
    listen-port=\$serverPort \\
    private-key=\$routerPrivateKey \\
    comment="ElgioTik VPN Tunnel"

:delay 1s
:put "WireGuard interface created"
:put ""

# Add peer (ElgioTik server)
:put "Step 3: Adding server peer..."
/interface wireguard peers add \\
    interface=wireguard-elgiotik \\
    public-key=\$serverPublicKey \\
    endpoint-address=\$serverEndpoint \\
    endpoint-port=\$serverPort \\
    allowed-address=\$vpnSubnet \\
    persistent-keepalive=25s \\
    comment="ElgioTik Server"

:put "Server peer added"
:put ""

# Assign IP address
:put "Step 4: Configuring IP address..."
/ip address add \\
    address=\$routerVpnIp/24 \\
    interface=wireguard-elgiotik \\
    comment="ElgioTik VPN IP"

:put "IP address \$routerVpnIp assigned"
:put ""

# Configure firewall
:put "Step 5: Configuring firewall rules..."

# Allow WireGuard from internet
:if ([/ip firewall filter find comment="WireGuard VPN to ElgioTik"] = "") do={{
    /ip firewall filter add \\
        chain=input \\
        protocol=udp \\
        dst-port=\$serverPort \\
        action=accept \\
        comment="WireGuard VPN to ElgioTik" \\
        place-before=0
    :put "Added WireGuard firewall rule"
}}

# Allow API access only from VPN subnet
:if ([/ip firewall filter find comment="API from ElgioTik VPN"] = "") do={{
    /ip firewall filter add \\
        chain=input \\
        protocol=tcp \\
        dst-port=8728 \\
        src-address=\$vpnSubnet \\
        action=accept \\
        comment="API from ElgioTik VPN" \\
        place-before=0
    :put "Added API access rule"
}}

# Allow API-SSL access from VPN subnet
:if ([/ip firewall filter find comment="API-SSL from ElgioTik VPN"] = "") do={{
    /ip firewall filter add \\
        chain=input \\
        protocol=tcp \\
        dst-port=8729 \\
        src-address=\$vpnSubnet \\
        action=accept \\
        comment="API-SSL from ElgioTik VPN" \\
        place-before=0
    :put "Added API-SSL access rule"
}}

# Block API from internet (if not already blocked)
:if ([/ip firewall filter find comment="Block API from internet"] = "") do={{
    /ip firewall filter add \\
        chain=input \\
        protocol=tcp \\
        dst-port=8728,8729 \\
        action=drop \\
        comment="Block API from internet"
    :put "Added API block rule"
}}

:put "Firewall configured"
:put ""

# Configure API service
:put "Step 6: Configuring API service..."
/ip service set api address="\$vpnSubnet,127.0.0.1" disabled=no
:put "API service configured (accessible from VPN only)"
:put ""

# Add route to server
:put "Step 7: Adding route to server..."
:if ([/ip route find comment="Route to ElgioTik server"] = "") do={{
    /ip route add \\
        dst-address=\$serverVpnIp/32 \\
        gateway=wireguard-elgiotik \\
        comment="Route to ElgioTik server"
    :put "Route added"
}}
:put ""

:put "========================================"
:put "Configuration Complete!"
:put "========================================"
:put ""
:put "Router Configuration:"
:put "  Name: \$routerName"
:put "  VPN IP: \$routerVpnIp"
:put "  Server: \$serverEndpoint:\$serverPort"
:put "  Status: Connecting..."
:put ""
:put "Verification:"
:put "  1. Check WireGuard status: /interface wireguard print"
:put "  2. Check peers: /interface wireguard peers print"
:put "  3. Ping server: /ping \$serverVpnIp count=5"
:put ""
:put "Next Steps:"
:put "  1. Wait 30 seconds for VPN connection"
:put "  2. Ping ElgioTik server: /ping \$serverVpnIp"
:put "  3. Router should show 'Online' in ElgioTik dashboard"
:put "========================================"

RSC;

        return $config;
    }

    /**
     * Add router peer to server WireGuard config
     */
    public function addPeerToServer(Router $router): bool
    {
        try {
            if (!$router->vpn_enabled || !$router->vpn_public_key || !$router->vpn_ip) {
                throw new Exception('Router VPN configuration incomplete');
            }

            // Check if running on a system with WireGuard
            if (!file_exists('/usr/bin/wg')) {
                Log::warning('WireGuard not installed. Peer configuration must be added manually.');
                return false;
            }

            // Add peer using wg set command
            $command = sprintf(
                'wg set wg0 peer %s allowed-ips %s persistent-keepalive 25',
                escapeshellarg($router->vpn_public_key),
                escapeshellarg($router->vpn_ip . '/32')
            );

            $result = Process::run("sudo {$command}");

            if ($result->successful()) {
                // Save config
                Process::run('sudo wg-quick save wg0');

                Log::info("Added VPN peer for router {$router->name}", [
                    'router_id' => $router->id,
                    'vpn_ip' => $router->vpn_ip,
                ]);

                return true;
            }

            throw new Exception('Failed to add peer: ' . $result->errorOutput());

        } catch (Exception $e) {
            Log::error('Failed to add WireGuard peer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove router peer from server WireGuard config
     */
    public function removePeerFromServer(Router $router): bool
    {
        try {
            if (!$router->vpn_public_key) {
                return true; // Nothing to remove
            }

            // Check if WireGuard is available
            if (!file_exists('/usr/bin/wg')) {
                Log::warning('WireGuard not installed. Peer must be removed manually.');
                return false;
            }

            $command = sprintf(
                'wg set wg0 peer %s remove',
                escapeshellarg($router->vpn_public_key)
            );

            $result = Process::run("sudo {$command}");

            if ($result->successful()) {
                // Save config
                Process::run('sudo wg-quick save wg0');

                Log::info("Removed VPN peer for router {$router->name}", [
                    'router_id' => $router->id,
                ]);

                return true;
            }

            throw new Exception('Failed to remove peer: ' . $result->errorOutput());

        } catch (Exception $e) {
            Log::error('Failed to remove WireGuard peer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get server's public key
     */
    protected function getServerPublicKey(): string
    {
        try {
            $result = Process::run('sudo wg show wg0 public-key');

            if ($result->successful()) {
                return trim($result->output());
            }

            // Fallback: read from file
            if (file_exists('/etc/wireguard/server_public.key')) {
                return trim(file_get_contents('/etc/wireguard/server_public.key'));
            }

            throw new Exception('Cannot read server public key');

        } catch (Exception $e) {
            Log::error('Failed to get server public key: ' . $e->getMessage());
            return 'SERVER_PUBLIC_KEY_NOT_AVAILABLE';
        }
    }

    /**
     * Get server VPN IP
     */
    protected function getServerVpnIp(): string
    {
        [$baseIp, $cidr] = explode('/', $this->vpnSubnet);
        $parts = explode('.', $baseIp);
        return "{$parts[0]}.{$parts[1]}.{$parts[2]}.1";
    }

    /**
     * Check if WireGuard is available on server
     */
    public function isWireGuardAvailable(): bool
    {
        return file_exists('/usr/bin/wg') && file_exists('/etc/wireguard/wg0.conf');
    }

    /**
     * Get VPN peer status
     */
    public function getPeerStatus(Router $router): ?array
    {
        if (!$router->vpn_public_key || !$this->isWireGuardAvailable()) {
            return null;
        }

        try {
            $result = Process::run("sudo wg show wg0 dump");

            if (!$result->successful()) {
                return null;
            }

            $lines = explode("\n", trim($result->output()));

            // Parse WireGuard dump output
            foreach ($lines as $line) {
                $parts = explode("\t", $line);

                if (count($parts) >= 6 && $parts[0] === $router->vpn_public_key) {
                    return [
                        'endpoint' => $parts[2] ?? null,
                        'allowed_ips' => $parts[3] ?? null,
                        'latest_handshake' => $parts[4] ?? null,
                        'transfer_rx' => $parts[5] ?? null,
                        'transfer_tx' => $parts[6] ?? null,
                    ];
                }
            }

            return null;

        } catch (Exception $e) {
            Log::error('Failed to get peer status: ' . $e->getMessage());
            return null;
        }
    }
}
