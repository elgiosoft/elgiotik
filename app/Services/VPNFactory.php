<?php

namespace App\Services;

use App\Models\Router;
use Exception;

class VPNFactory
{
    /**
     * Get the appropriate VPN service based on RouterOS version
     */
    public static function getService(?string $routerosVersion = null, ?string $vpnType = null)
    {
        // If VPN type is explicitly specified
        if ($vpnType) {
            return $vpnType === 'openvpn' ? new OpenVPNService() : new WireGuardService();
        }

        // Determine based on RouterOS version
        if ($routerosVersion) {
            $majorVersion = static::getMajorVersion($routerosVersion);

            if ($majorVersion >= 7) {
                return new WireGuardService();
            } else {
                return new OpenVPNService();
            }
        }

        // Default to WireGuard
        return new WireGuardService();
    }

    /**
     * Get VPN service for a specific router
     */
    public static function forRouter(Router $router)
    {
        return static::getService($router->routeros_version, $router->vpn_type);
    }

    /**
     * Determine VPN type based on RouterOS version
     */
    public static function determineVPNType(string $routerosVersion): string
    {
        $majorVersion = static::getMajorVersion($routerosVersion);
        return $majorVersion >= 7 ? 'wireguard' : 'openvpn';
    }

    /**
     * Extract major version number from version string
     */
    protected static function getMajorVersion(string $version): int
    {
        // Extract major version from strings like "6.49.10", "7.13.2", etc.
        preg_match('/^(\d+)\./', $version, $matches);
        return isset($matches[1]) ? (int) $matches[1] : 7; // Default to 7 if can't parse
    }

    /**
     * Check if version supports WireGuard
     */
    public static function supportsWireGuard(string $routerosVersion): bool
    {
        return static::getMajorVersion($routerosVersion) >= 7;
    }

    /**
     * Check if version supports OpenVPN
     */
    public static function supportsOpenVPN(string $routerosVersion): bool
    {
        // OpenVPN supported in all versions
        return true;
    }

    /**
     * Get recommended VPN type for version
     */
    public static function getRecommendedVPNType(string $routerosVersion): string
    {
        if (static::supportsWireGuard($routerosVersion)) {
            return 'wireguard'; // Prefer WireGuard for v7+
        }
        return 'openvpn';
    }

    /**
     * Get human-readable VPN type name
     */
    public static function getVPNTypeName(string $vpnType): string
    {
        return match($vpnType) {
            'wireguard' => 'WireGuard',
            'openvpn' => 'OpenVPN',
            default => 'Unknown',
        };
    }

    /**
     * Get VPN configuration requirements
     */
    public static function getRequirements(string $vpnType): array
    {
        return match($vpnType) {
            'wireguard' => [
                'routeros_min_version' => '7.0',
                'server_package' => 'wireguard',
                'client_supported' => true,
                'encryption' => 'ChaCha20-Poly1305',
                'performance' => 'Excellent',
            ],
            'openvpn' => [
                'routeros_min_version' => '6.0',
                'server_package' => 'openvpn',
                'client_supported' => true,
                'encryption' => 'AES-256',
                'performance' => 'Good',
            ],
            default => [],
        };
    }
}
