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
    ];

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
        return $this->vpn_enabled && !empty($this->vpn_ip);
    }

    /**
     * Get the IP address to use for connection (VPN if enabled, otherwise direct IP)
     */
    public function getConnectionIp(): string
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
}
