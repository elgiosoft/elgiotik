#!/bin/bash

# ElgioTik VPN Setup Script for Ubuntu/Debian Server
# This script helps set up WireGuard VPN for MikroTik router connections

set -e

echo "=========================================="
echo "  ElgioTik VPN Setup (WireGuard)"
echo "=========================================="
echo ""

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root (use sudo)"
   exit 1
fi

# Check OS
if [[ ! -f /etc/os-release ]]; then
    echo "Cannot detect OS version"
    exit 1
fi

source /etc/os-release

echo "Detected OS: $PRETTY_NAME"
echo ""

# Install WireGuard
echo "[1/6] Installing WireGuard..."
apt update -qq
apt install -y wireguard wireguard-tools qrencode

# Generate server keys
echo "[2/6] Generating WireGuard keys..."
cd /etc/wireguard

if [[ ! -f server_private.key ]]; then
    wg genkey | tee server_private.key | wg pubkey > server_public.key
    chmod 600 server_private.key
    echo "✓ Keys generated"
else
    echo "✓ Keys already exist"
fi

SERVER_PRIVATE_KEY=$(cat server_private.key)
SERVER_PUBLIC_KEY=$(cat server_public.key)

echo ""
echo "Server Public Key: $SERVER_PUBLIC_KEY"
echo "(Save this - you'll need it for MikroTik configuration)"
echo ""

# Ask for VPN subnet
read -p "Enter VPN subnet (default: 10.10.10.0/24): " VPN_SUBNET
VPN_SUBNET=${VPN_SUBNET:-10.10.10.0/24}

read -p "Enter server VPN IP (default: 10.10.10.1): " SERVER_IP
SERVER_IP=${SERVER_IP:-10.10.10.1}

read -p "Enter WireGuard listen port (default: 51820): " LISTEN_PORT
LISTEN_PORT=${LISTEN_PORT:-51820}

# Create WireGuard config
echo "[3/6] Creating WireGuard configuration..."

cat > /etc/wireguard/wg0.conf <<EOF
[Interface]
PrivateKey = $SERVER_PRIVATE_KEY
Address = $SERVER_IP/24
ListenPort = $LISTEN_PORT
SaveConfig = false

# Enable IP forwarding
PostUp = sysctl -w net.ipv4.ip_forward=1
PostDown = sysctl -w net.ipv4.ip_forward=0

# Add your MikroTik peers below:
# Example:
# [Peer]
# PublicKey = ROUTER_PUBLIC_KEY_HERE
# AllowedIPs = 10.10.10.11/32
# PersistentKeepalive = 25

EOF

chmod 600 /etc/wireguard/wg0.conf

echo "✓ Configuration created at /etc/wireguard/wg0.conf"
echo ""

# Configure firewall
echo "[4/6] Configuring firewall..."

# Check if UFW is active
if command -v ufw &> /dev/null && ufw status | grep -q "Status: active"; then
    echo "Configuring UFW firewall..."
    ufw allow $LISTEN_PORT/udp comment "WireGuard VPN"
    echo "✓ UFW configured"
elif command -v iptables &> /dev/null; then
    echo "Configuring iptables..."
    iptables -A INPUT -p udp --dport $LISTEN_PORT -j ACCEPT
    # Save rules (method varies by distro)
    if command -v netfilter-persistent &> /dev/null; then
        netfilter-persistent save
    elif command -v iptables-save &> /dev/null; then
        iptables-save > /etc/iptables/rules.v4
    fi
    echo "✓ iptables configured"
else
    echo "⚠ No firewall detected. Please manually allow port $LISTEN_PORT/udp"
fi

echo ""

# Enable IP forwarding permanently
echo "[5/6] Enabling IP forwarding..."
if ! grep -q "net.ipv4.ip_forward=1" /etc/sysctl.conf; then
    echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
fi
sysctl -w net.ipv4.ip_forward=1 > /dev/null
echo "✓ IP forwarding enabled"
echo ""

# Enable and start WireGuard
echo "[6/6] Starting WireGuard..."
systemctl enable wg-quick@wg0
systemctl start wg-quick@wg0

if systemctl is-active --quiet wg-quick@wg0; then
    echo "✓ WireGuard started successfully"
else
    echo "✗ Failed to start WireGuard"
    echo "Check logs: journalctl -u wg-quick@wg0"
    exit 1
fi

echo ""
echo "=========================================="
echo "  WireGuard VPN Setup Complete!"
echo "=========================================="
echo ""
echo "Server Configuration:"
echo "  - VPN Subnet: $VPN_SUBNET"
echo "  - Server IP: $SERVER_IP"
echo "  - Listen Port: $LISTEN_PORT"
echo "  - Public Key: $SERVER_PUBLIC_KEY"
echo ""
echo "Next Steps:"
echo ""
echo "1. Add MikroTik peers to /etc/wireguard/wg0.conf"
echo ""
echo "   Example peer configuration:"
echo "   [Peer]"
echo "   PublicKey = ROUTER_PUBLIC_KEY"
echo "   AllowedIPs = 10.10.10.11/32"
echo "   PersistentKeepalive = 25"
echo ""
echo "2. Reload WireGuard after adding peers:"
echo "   sudo systemctl restart wg-quick@wg0"
echo ""
echo "3. Configure MikroTik routers (see VPN_SETUP_GUIDE.md)"
echo ""
echo "4. Add routers in ElgioTik using VPN IP addresses"
echo ""
echo "5. Test connection:"
echo "   php artisan router:test"
echo ""
echo "Useful commands:"
echo "  - Check status: sudo wg show"
echo "  - View logs: sudo journalctl -u wg-quick@wg0 -f"
echo "  - Restart VPN: sudo systemctl restart wg-quick@wg0"
echo ""
echo "For detailed setup guide, read: VPN_SETUP_GUIDE.md"
echo "=========================================="
