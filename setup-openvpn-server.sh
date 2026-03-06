#!/bin/bash

# OpenVPN Server Setup Script for ElgioTik
# This script sets up an OpenVPN server for MikroTik RouterOS 6.x clients
# Run with: sudo bash setup-openvpn-server.sh

set -e

echo "=========================================="
echo "ElgioTik OpenVPN Server Setup"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run as root (use sudo)"
    exit 1
fi

# Configuration variables
VPN_SUBNET="10.10.20.0"  # OpenVPN subnet (WireGuard uses 10.10.10.0/24)
VPN_NETMASK="255.255.255.0"
VPN_PORT="1194"
SERVER_IP=$(hostname -I | awk '{print $1}')

echo "Configuration:"
echo "  VPN Subnet: $VPN_SUBNET/24 (OpenVPN)"
echo "  VPN Port: $VPN_PORT"
echo "  Server IP: $SERVER_IP"
echo "  Note: WireGuard uses 10.10.10.0/24, OpenVPN uses 10.10.20.0/24"
echo ""

# Update system
echo "Step 1: Updating system packages..."
apt-get update -qq

# Install OpenVPN and Easy-RSA
echo "Step 2: Installing OpenVPN and Easy-RSA..."
apt-get install -y openvpn easy-rsa iptables-persistent

# Create Easy-RSA directory
echo "Step 3: Setting up PKI infrastructure..."
EASYRSA_DIR="/etc/openvpn/easy-rsa"
mkdir -p $EASYRSA_DIR
ln -sf /usr/share/easy-rsa/* $EASYRSA_DIR/
cd $EASYRSA_DIR

# Initialize PKI
echo "Step 4: Initializing PKI..."
./easyrsa init-pki

# Build CA (non-interactive)
echo "Step 5: Building Certificate Authority..."
EASYRSA_BATCH=1 ./easyrsa build-ca nopass

# Generate server certificate
echo "Step 6: Generating server certificate..."
EASYRSA_BATCH=1 ./easyrsa build-server-full server nopass

# Generate DH parameters
echo "Step 7: Generating Diffie-Hellman parameters (this may take a while)..."
./easyrsa gen-dh

# Generate TLS auth key
echo "Step 8: Generating TLS authentication key..."
openvpn --genkey --secret /etc/openvpn/ta.key

# Copy certificates to OpenVPN directory
echo "Step 9: Installing certificates..."
cp pki/ca.crt /etc/openvpn/
cp pki/issued/server.crt /etc/openvpn/
cp pki/private/server.key /etc/openvpn/
cp pki/dh.pem /etc/openvpn/

# Create OpenVPN server configuration
echo "Step 10: Creating OpenVPN server configuration..."
cat > /etc/openvpn/server.conf <<EOF
# ElgioTik OpenVPN Server Configuration
# Port and protocol
port $VPN_PORT
proto tcp-server
dev tun

# SSL/TLS configuration
ca ca.crt
cert server.crt
key server.key
dh dh.pem
tls-auth ta.key 0

# Network configuration
server $VPN_SUBNET $VPN_NETMASK
topology subnet
ifconfig-pool-persist /var/log/openvpn/ipp.txt

# Client configuration
push "redirect-gateway def1 bypass-dhcp"
push "dhcp-option DNS 8.8.8.8"
push "dhcp-option DNS 8.8.4.4"

# Connection settings
keepalive 10 120
cipher AES-256-CBC
auth SHA256
ncp-disable

# No compression (MikroTik compatibility)
# compress lz4-v2
# push "compress lz4-v2"

# Permissions
user nobody
group nogroup
persist-key
persist-tun

# Logging
status /var/log/openvpn/openvpn-status.log
log-append /var/log/openvpn/openvpn.log
verb 3

# Client compatibility (for MikroTik)
client-to-client
duplicate-cn
verify-client-cert none
username-as-common-name

# Authentication
script-security 2
auth-user-pass-verify /etc/openvpn/auth.sh via-env

# Security
max-clients 100
EOF

# Create simple authentication script
cat > /etc/openvpn/auth.sh <<'AUTHEOF'
#!/bin/bash
# Simple pass-through authentication for MikroTik compatibility
# All authenticated clients are allowed
exit 0
AUTHEOF

chmod +x /etc/openvpn/auth.sh

# Create log directory
mkdir -p /var/log/openvpn
touch /var/log/openvpn/openvpn.log
touch /var/log/openvpn/openvpn-status.log

# Enable IP forwarding
echo "Step 11: Enabling IP forwarding..."
echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
sysctl -p

# Configure firewall
echo "Step 12: Configuring firewall..."
INTERFACE=$(ip route | grep default | awk '{print $5}')

# Allow OpenVPN port
iptables -A INPUT -p udp --dport $VPN_PORT -j ACCEPT

# Allow VPN traffic
iptables -A INPUT -i tun0 -j ACCEPT
iptables -A FORWARD -i tun0 -j ACCEPT
iptables -A FORWARD -o tun0 -j ACCEPT

# NAT for VPN clients
iptables -t nat -A POSTROUTING -s $VPN_SUBNET/24 -o $INTERFACE -j MASQUERADE

# Save iptables rules
mkdir -p /etc/iptables
iptables-save > /etc/iptables/rules.v4

# Enable and start OpenVPN
echo "Step 13: Starting OpenVPN service..."
systemctl enable openvpn@server
systemctl start openvpn@server

# Wait for service to start
sleep 3

# Check OpenVPN status
echo ""
echo "=========================================="
echo "OpenVPN Server Setup Complete!"
echo "=========================================="
echo ""
echo "Server Status:"
systemctl status openvpn@server --no-pager -l | head -n 10
echo ""
echo "Network Interface:"
ip addr show tun0 2>/dev/null || echo "tun0 not yet available (service may still be starting)"
echo ""
echo "Configuration Details:"
echo "  Server IP: $SERVER_IP"
echo "  VPN Port: $VPN_PORT (UDP)"
echo "  OpenVPN Network: $VPN_SUBNET/24"
echo "  OpenVPN Server IP: 10.10.20.1"
echo "  WireGuard Network: 10.10.10.0/24 (separate)"
echo "  WireGuard Server IP: 10.10.10.1"
echo ""
echo "Verification Commands:"
echo "  Check status: systemctl status openvpn@server"
echo "  Check logs: tail -f /var/log/openvpn/openvpn.log"
echo "  List clients: cat /var/log/openvpn/openvpn-status.log"
echo ""
echo "Firewall Status:"
echo "  OpenVPN port $VPN_PORT/udp: OPEN"
echo "  VPN subnet: $VPN_SUBNET/24"
echo ""
echo "Next Steps:"
echo "  1. Ensure port $VPN_PORT/udp is open in your cloud firewall"
echo "  2. Generate client configurations from ElgioTik dashboard"
echo "  3. Import configurations on MikroTik routers"
echo ""
echo "IMPORTANT: If you're using a cloud provider (AWS, DigitalOcean, etc.),"
echo "make sure to open port $VPN_PORT/udp in the security group/firewall."
echo "=========================================="
