<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'router_id',
        'bandwidth_plan_id',
        'customer_id',
        'voucher_id',
        'status',
        'mac_address',
        'ip_address',
        'last_login_at',
        'last_logout_at',
        'bytes_in',
        'bytes_out',
        'session_time',
        'expires_at',
        'is_online',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'expires_at' => 'datetime',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'session_time' => 'integer',
        'is_online' => 'boolean',
    ];

    /**
     * Relationships
     */

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function bandwidthPlan(): BelongsTo
    {
        return $this->belongsTo(BandwidthPlan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDisabled($query)
    {
        return $query->where('status', 'disabled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeOffline($query)
    {
        return $query->where('is_online', false);
    }

    public function scopeForRouter($query, int $routerId)
    {
        return $query->where('router_id', $routerId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForBandwidthPlan($query, int $planId)
    {
        return $query->where('bandwidth_plan_id', $planId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('username', 'like', "%{$search}%")
              ->orWhere('mac_address', 'like', "%{$search}%")
              ->orWhere('ip_address', 'like', "%{$search}%");
        });
    }

    /**
     * Helper Methods
     */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDisabled(): bool
    {
        return $this->status === 'disabled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isOnline(): bool
    {
        return $this->is_online === true;
    }

    public function isOffline(): bool
    {
        return $this->is_online === false;
    }

    public function markAsOnline(string $ipAddress, ?string $macAddress = null): void
    {
        $this->update([
            'is_online' => true,
            'last_login_at' => now(),
            'ip_address' => $ipAddress,
            'mac_address' => $macAddress ?? $this->mac_address,
        ]);
    }

    public function markAsOffline(): void
    {
        $this->update([
            'is_online' => false,
            'last_logout_at' => now(),
        ]);
    }

    public function disable(): void
    {
        $this->update(['status' => 'disabled']);
    }

    public function enable(): void
    {
        if ($this->isExpired()) {
            return;
        }

        $this->update(['status' => 'active']);
    }

    public function checkAndUpdateExpiration(): void
    {
        if ($this->expires_at && $this->expires_at->isPast() && !$this->isExpired()) {
            $this->update([
                'status' => 'expired',
                'is_online' => false,
            ]);
        }
    }

    public function updateUsageStats(int $bytesIn, int $bytesOut, int $sessionTime): void
    {
        $this->update([
            'bytes_in' => $this->bytes_in + $bytesIn,
            'bytes_out' => $this->bytes_out + $bytesOut,
            'session_time' => $this->session_time + $sessionTime,
        ]);
    }

    public function getTotalBytes(): int
    {
        return $this->bytes_in + $this->bytes_out;
    }

    public function getFormattedBytesIn(): string
    {
        return $this->formatBytes($this->bytes_in);
    }

    public function getFormattedBytesOut(): string
    {
        return $this->formatBytes($this->bytes_out);
    }

    public function getFormattedTotalBytes(): string
    {
        return $this->formatBytes($this->getTotalBytes());
    }

    public function getFormattedSessionTime(): string
    {
        $hours = floor($this->session_time / 3600);
        $minutes = floor(($this->session_time % 3600) / 60);
        $seconds = $this->session_time % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    public function getHoursUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInHours($this->expires_at, false);
    }

    public function getTotalSessionsCount(): int
    {
        return $this->sessions()->count();
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
