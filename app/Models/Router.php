<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;


class Router extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'ip_address',
        'api_port',
        'username',
        'password',
        'location',
        'description',
        'is_active',
        'status',
        'last_seen_at',
        'vpn_enabled',
        'vpn_ip',
        'vpn_public_key',
        'vpn_private_key',
        'vpn_endpoint',
        'vpn_listen_port',
        'vpn_last_handshake',
        'vpn_config_script',
        'routeros_version',
        'vpn_type',
        'router_hash',
        'wallet_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'vpn_private_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'vpn_enabled' => 'boolean',
        'last_seen_at' => 'datetime',
        'vpn_last_handshake' => 'datetime',
        'wallet_balance' => 'decimal:2',
    ];

    /**
     * Boot method to generate router hash
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($router) {
            if (!$router->router_hash) {
                $router->router_hash = \Illuminate\Support\Str::random(32);
            }
        });
    }

    public function decryptedPassword(): string
    {
        return Crypt::decryptString($this->password);
    }

    /**
     * Relationships
     */

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class);
    }

    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function routerTransactions(): HasMany
    {
        return $this->hasMany(RouterTransaction::class);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Helper Methods
     */

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }

    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function updateStatus(string $status): void
    {
        $this->update([
            'status' => $status,
            'last_seen_at' => $status === 'online' ? now() : $this->last_seen_at,
        ]);
    }

    public function getConnectionString(): string
    {
        return "{$this->ip_address}:{$this->api_port}";
    }

    public function getOnlineUsersCount(): int
    {
        return $this->hotspotUsers()->where('is_online', true)->count();
    }

    /**
     * Check if router uses VPN
     */
    public function usesVpn(): bool
    {
        return $this->vpn_enabled && $this->vpn_ip !== null && $this->vpn_ip !== '';
    }

    /**
     * Get the IP address to use for connection (VPN if enabled, otherwise direct IP)
     */
    public function getConnectionIp(): ?string
    {
        return $this->usesVpn() ? $this->vpn_ip : $this->ip_address;
    }

    /**
     * Check if VPN handshake is recent (within last 3 minutes)
     */
    public function hasRecentHandshake(): bool
    {
        if (!$this->vpn_last_handshake) {
            return false;
        }

        return $this->vpn_last_handshake->diffInMinutes(now()) < 3;
    }

    /**
     * Get RouterOS major version
     */
    public function getRouterOSMajorVersion(): ?int
    {
        if (!$this->routeros_version) {
            return null;
        }

        preg_match('/^(\d+)\./', $this->routeros_version, $matches);
        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    /**
     * Check if router supports WireGuard
     */
    public function supportsWireGuard(): bool
    {
        $majorVersion = $this->getRouterOSMajorVersion();
        return $majorVersion && $majorVersion >= 7;
    }

    /**
     * Check if router supports OpenVPN
     */
    public function supportsOpenVPN(): bool
    {
        return true; // OpenVPN supported in all RouterOS versions
    }

    /**
     * Get recommended VPN type based on RouterOS version
     */
    public function getRecommendedVPNType(): string
    {
        return $this->supportsWireGuard() ? 'wireguard' : 'openvpn';
    }

    /**
     * Get VPN type display name
     */
    public function getVPNTypeNameAttribute(): string
    {
        return match($this->vpn_type) {
            'wireguard' => 'WireGuard',
            'openvpn' => 'OpenVPN',
            default => 'None',
        };
    }

    /**
     * Wallet Methods
     */

    /**
     * Add credit to router wallet
     */
    public function creditWallet(float $amount, ?int $transactionId = null, ?int $hotspotUserId = null, ?string $description = null, ?array $metadata = null): RouterTransaction
    {
        $newBalance = $this->wallet_balance + $amount;

        $routerTransaction = $this->routerTransactions()->create([
            'transaction_id' => $transactionId,
            'hotspot_user_id' => $hotspotUserId,
            'type' => 'credit',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'Wallet credit',
            'metadata' => $metadata,
        ]);

        $this->update(['wallet_balance' => $newBalance]);

        return $routerTransaction;
    }

    /**
     * Deduct amount from router wallet
     */
    public function debitWallet(float $amount, ?string $reference = null, ?string $description = null, ?array $metadata = null): RouterTransaction
    {
        if ($amount > $this->wallet_balance) {
            throw new \Exception('Insufficient wallet balance');
        }

        $newBalance = $this->wallet_balance - $amount;

        $routerTransaction = $this->routerTransactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description ?? 'Wallet withdrawal',
            'reference' => $reference,
            'metadata' => $metadata,
        ]);

        $this->update(['wallet_balance' => $newBalance]);

        return $routerTransaction;
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance(): float
    {
        return (float) $this->wallet_balance;
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->wallet_balance >= $amount;
    }
}
