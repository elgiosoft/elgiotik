<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MikroTik Connection Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how the application connects to MikroTik routers.
    | Adjust timeout and retry settings based on your network conditions.
    |
    */

    'connection_timeout' => env('MIKROTIK_CONNECTION_TIMEOUT', 5),

    'attempts' => env('MIKROTIK_ATTEMPTS', 3),

    'delay' => env('MIKROTIK_DELAY', 1),

    /*
    |--------------------------------------------------------------------------
    | Default API Port
    |--------------------------------------------------------------------------
    |
    | Default MikroTik API port. Standard is 8728, SSL API uses 8729.
    |
    */

    'default_port' => env('MIKROTIK_DEFAULT_PORT', 8728),

    /*
    |--------------------------------------------------------------------------
    | VPN Connection Settings
    |--------------------------------------------------------------------------
    |
    | If your routers are accessed via VPN, configure the settings here.
    | This is mainly for documentation and network planning purposes.
    |
    */

    'vpn' => [
        'enabled' => env('VPN_ENABLED', false),
        'type' => env('VPN_TYPE', 'wireguard'), // wireguard, ipsec, l2tp, pptp, tailscale
        'interface' => env('VPN_INTERFACE', 'wg0'),
        'subnet' => env('VPN_SUBNET', '10.10.10.0/24'),
        'port' => env('VPN_PORT', 51820),
        'server_endpoint' => env('VPN_SERVER_ENDPOINT', env('APP_URL')),
        'config_path' => env('VPN_CONFIG_PATH', '/etc/wireguard/wg0.conf'),
        'auto_provision' => env('VPN_AUTO_PROVISION', true),
    ],

];
