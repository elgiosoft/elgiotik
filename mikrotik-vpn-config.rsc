# MikroTik WireGuard VPN Configuration for ElgioTik
#
# This script configures WireGuard VPN on your MikroTik router to connect to ElgioTik server
#
# BEFORE RUNNING:
# 1. Replace SERVER_PUBLIC_KEY with your ElgioTik server's public key
# 2. Replace ROUTER_VPN_IP with assigned VPN IP (e.g., 10.10.10.11)
# 3. Replace elgiotik.com with your actual server address
# 4. Adjust port numbers if needed
#
# HOW TO USE:
# 1. Copy this file to your MikroTik router
# 2. Edit the variables below
# 3. Run: /import mikrotik-vpn-config.rsc
#

# ====================
# CONFIGURATION VARIABLES - EDIT THESE!
# ====================

:local serverPublicKey "PASTE_SERVER_PUBLIC_KEY_HERE"
:local serverEndpoint "elgiotik.com"
:local serverPort 51820
:local routerVpnIp "10.10.10.11"
:local vpnSubnet "10.10.10.0/24"
:local serverVpnIp "10.10.10.1"

# ====================
# DO NOT EDIT BELOW THIS LINE
# ====================

:put "========================================"
:put "ElgioTik WireGuard VPN Configuration"
:put "========================================"
:put ""

# Generate WireGuard keys
:put "Step 1: Generating WireGuard keys..."
:local wgPrivKey
:local wgPubKey

# Check if interface exists
:if ([/interface wireguard find name=wireguard-elgiotik] != "") do={
    :put "Removing existing WireGuard interface..."
    /interface wireguard remove [find name=wireguard-elgiotik]
}

# Create WireGuard interface and generate keys
/interface wireguard add name=wireguard-elgiotik listen-port=$serverPort
:delay 1s

# Get generated private key
:set wgPrivKey [/interface wireguard get wireguard-elgiotik private-key]
:set wgPubKey [/interface wireguard get wireguard-elgiotik public-key]

:put "Router Public Key: $wgPubKey"
:put "(IMPORTANT: Save this key and add it to ElgioTik server config!)"
:put ""

# Add peer (ElgioTik server)
:put "Step 2: Adding peer configuration..."
/interface wireguard peers add \
    interface=wireguard-elgiotik \
    public-key=$serverPublicKey \
    endpoint-address=$serverEndpoint \
    endpoint-port=$serverPort \
    allowed-address=$vpnSubnet \
    persistent-keepalive=25s

:put "Peer added successfully"
:put ""

# Assign IP address
:put "Step 3: Configuring IP address..."
/ip address add address=$routerVpnIp/24 interface=wireguard-elgiotik network=10.10.10.0
:put "IP address $routerVpnIp assigned"
:put ""

# Configure firewall
:put "Step 4: Configuring firewall..."

# Allow WireGuard from internet
/ip firewall filter add \
    chain=input \
    protocol=udp \
    dst-port=$serverPort \
    action=accept \
    comment="WireGuard VPN to ElgioTik" \
    place-before=0

# Allow API access only from VPN subnet
:if ([/ip firewall filter find comment="API from ElgioTik VPN"] = "") do={
    /ip firewall filter add \
        chain=input \
        protocol=tcp \
        dst-port=8728 \
        src-address=$vpnSubnet \
        action=accept \
        comment="API from ElgioTik VPN" \
        place-before=0
}

# Block API from internet (if not already blocked)
:if ([/ip firewall filter find comment="Block API from internet"] = "") do={
    /ip firewall filter add \
        chain=input \
        protocol=tcp \
        dst-port=8728 \
        action=drop \
        comment="Block API from internet"
}

:put "Firewall rules configured"
:put ""

# Ensure API service is enabled
:put "Step 5: Configuring API service..."
/ip service set api address="$vpnSubnet,127.0.0.1" disabled=no
:put "API service configured (accessible from VPN only)"
:put ""

# Add route (optional, for full VPN routing)
:put "Step 6: Adding route..."
/ip route add dst-address=$serverVpnIp/32 gateway=wireguard-elgiotik comment="Route to ElgioTik server"
:put "Route added"
:put ""

:put "========================================"
:put "Configuration Complete!"
:put "========================================"
:put ""
:put "Your Configuration:"
:put "  Router VPN IP: $routerVpnIp"
:put "  Server Endpoint: $serverEndpoint:$serverPort"
:put "  VPN Subnet: $vpnSubnet"
:put ""
:put "IMPORTANT - Router Public Key:"
:put "  $wgPubKey"
:put ""
:put "Next Steps:"
:put "1. Add the above public key to your ElgioTik server config"
:put "2. Restart WireGuard on server: systemctl restart wg-quick@wg0"
:put "3. Test connectivity: ping $serverVpnIp"
:put "4. Check VPN status: /interface wireguard print"
:put "5. Add this router in ElgioTik admin panel"
:put "========================================"
