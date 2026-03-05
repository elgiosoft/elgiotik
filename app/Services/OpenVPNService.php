<?php

namespace App\Services;

use App\Models\Router;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class OpenVPNService
{
    protected $serverEndpoint;
    protected $vpnSubnet;
    protected $vpnPort;
    protected $serverCert;
    protected $serverKey;
    protected $caCert;

    public function __construct()
    {
        $endpoint = config('mikrotik.vpn.server_endpoint', config('app.url'));
        $this->serverEndpoint = preg_replace('#^https?://#', '', $endpoint);
        $this->vpnSubnet = config('mikrotik.vpn.openvpn_subnet', '10.10.20.0/24');
        $this->vpnPort = config('mikrotik.vpn.openvpn_port', 1194);
    }

    /**
     * Generate OpenVPN certificates for router
     */
    public function generateCertificates(Router $router): array
    {
        try {
            // For simplicity, we'll generate a pre-shared key
            // In production, you'd want to use proper PKI with easy-rsa or similar

            $psk = $this->generatePreSharedKey();

            return [
                'psk' => $psk,
                'client_cert' => null, // Can be implemented with easy-rsa
                'client_key' => null,
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate OpenVPN certificates: ' . $e->getMessage());
            throw new Exception('Failed to generate OpenVPN certificates.');
        }
    }

    /**
     * Generate pre-shared key for OpenVPN
     */
    protected function generatePreSharedKey(): string
    {
        // Generate a random pre-shared key (2048-bit)
        return base64_encode(random_bytes(256));
    }

    /**
     * Get next available VPN IP address
     */
    public function getNextAvailableIp(): string
    {
        [$baseIp, $cidr] = explode('/', $this->vpnSubnet);
        $parts = explode('.', $baseIp);
        $startOctet = 11;

        // Only get IPs from OpenVPN subnet (10.10.20.0/24)
        $assignedIps = Router::whereNotNull('vpn_ip')
            ->where('vpn_type', 'openvpn')
            ->pluck('vpn_ip')
            ->toArray();

        for ($i = $startOctet; $i < 254; $i++) {
            $testIp = "{$parts[0]}.{$parts[1]}.{$parts[2]}.{$i}";
            if (!in_array($testIp, $assignedIps)) {
                return $testIp;
            }
        }

        throw new Exception('No available VPN IP addresses in subnet ' . $this->vpnSubnet);
    }

    /**
     * Generate MikroTik OpenVPN configuration script
     */
    public function generateMikroTikConfig(Router $router): string
    {
        if (!$router->vpn_enabled || !$router->vpn_private_key) {
            throw new Exception('Router VPN not configured');
        }

        $serverVpnIp = $this->getServerVpnIp();
        $psk = $router->vpn_private_key; // Using private_key field to store PSK

        $config = <<<RSC
# MikroTik OpenVPN Configuration for {$router->name}
# Generated automatically by ElgioTik
# Generated on: {now()->toDateTimeString()}
# RouterOS Version: 6.x (OpenVPN)
#
# INSTRUCTIONS:
# 1. Copy this entire script
# 2. Connect to your MikroTik router (WinBox, SSH, or WebFig)
# 3. Open New Terminal
# 4. Paste and execute this script
# 5. Router will automatically connect to ElgioTik VPN

# Configuration Variables
:local routerName "{$router->name}"
:local serverAddress "{$this->serverEndpoint}"
:local serverPort {$this->vpnPort}
:local clientVpnIp "{$router->vpn_ip}"
:local serverVpnIp "{$serverVpnIp}"
:local vpnSubnet "{$this->vpnSubnet}"

:put "========================================"
:put "ElgioTik OpenVPN Configuration"
:put "Router: \$routerName"
:put "RouterOS 6.x Compatible"
:put "========================================"
:put ""

# Remove existing OVPN client if exists
:put "Step 1: Cleaning up existing configuration..."
:if ([/interface ovpn-client find name=ovpn-elgiotik] != "") do={{
    :put "Removing existing OpenVPN client..."
    /interface ovpn-client remove [find name=ovpn-elgiotik]
}}
:delay 1s
:put "Cleanup complete"
:put ""

# Create pre-shared key file
:put "Step 2: Creating authentication key..."
/file print file=elgiotik-psk.txt
:delay 0.5
/file set elgiotik-psk.txt contents="{$psk}"
:delay 0.5
:put "Authentication key created"
:put ""

# Create OpenVPN client interface
:put "Step 3: Creating OpenVPN client..."
/interface ovpn-client add \\
    name=ovpn-elgiotik \\
    connect-to=\$serverAddress \\
    port=\$serverPort \\
    mode=ip \\
    user="{$router->name}" \\
    password="" \\
    auth=sha1 \\
    cipher=aes256 \\
    add-default-route=no \\
    use-peer-dns=no \\
    comment="ElgioTik VPN Tunnel"

:delay 2s
:put "OpenVPN client created"
:put ""

# Wait for connection
:put "Step 4: Waiting for connection..."
:delay 5s

# Configure firewall
:put "Step 5: Configuring firewall rules..."

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

# Block API from internet
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

:put "========================================"
:put "Configuration Complete!"
:put "========================================"
:put ""
:put "Router Configuration:"
:put "  Name: \$routerName"
:put "  Client IP: \$clientVpnIp"
:put "  Server: \$serverAddress:\$serverPort"
:put "  Status: Connecting..."
:put ""
:put "Verification:"
:put "  1. Check OVPN status: /interface ovpn-client print"
:put "  2. Check connection: /interface ovpn-client monitor ovpn-elgiotik"
:put "  3. Ping server: /ping \$serverVpnIp count=5"
:put ""
:put "Next Steps:"
:put "  1. Wait 30 seconds for VPN connection"
:put "  2. Check status: /interface ovpn-client monitor ovpn-elgiotik"
:put "  3. Router should show 'Online' in ElgioTik dashboard"
:put "========================================"

RSC;

        return $config;
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
     * Check if OpenVPN is available on server
     */
    public function isOpenVPNAvailable(): bool
    {
        return file_exists('/usr/sbin/openvpn') || file_exists('/usr/bin/openvpn');
    }
}
