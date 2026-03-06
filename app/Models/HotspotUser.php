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
        'voucher_id',
        'router_id',
        'bandwidth_plan_id',
        'customer_id',
        'username',
        'password',
        'status',
        'transaction_id',
        'mikrotik_user_id',
        'synced_to_router',
        'sync_error',
        'activated_at',
        'expires_at',
        'mac_address',
        'ip_address',
        'last_login_at',
        'last_logout_at',
        'bytes_in',
        'bytes_out',
        'session_time',
        'is_online',
        'sold_by',
        'sold_at',
        'created_by',
        'notes',
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
        'synced_to_router' => 'boolean',
        'is_online' => 'boolean',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'session_time' => 'integer',
    ];

    /**
     * Relationships
     */

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function bandwidthPlan(): BelongsTo
    {
        return $this->belongsTo(BandwidthPlan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

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

    public function scopeSynced($query)
    {
        return $query->where('synced_to_router', true);
    }

    public function scopeNotSynced($query)
    {
        return $query->where('synced_to_router', false);
    }

    public function scopeForRouter($query, int $routerId)
    {
        return $query->where('router_id', $routerId);
    }

    public function scopeForVoucher($query, int $voucherId)
    {
        return $query->where('voucher_id', $voucherId);
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeOffline($query)
    {
        return $query->where('is_online', false);
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
              ->orWhere('ip_address', 'like', "%{$search}%")
              ->orWhere('transaction_id', 'like', "%{$search}%");
        });
    }

    /**
     * Helper Methods
     */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isSynced(): bool
    {
        return $this->synced_to_router === true;
    }

    public function markAsPaid(string $transactionId): void
    {
        $this->update([
            'status' => 'paid',
            'transaction_id' => $transactionId,
        ]);
    }

    public function markAsSynced(string $mikrotikUserId): void
    {
        $this->update([
            'synced_to_router' => true,
            'mikrotik_user_id' => $mikrotikUserId,
            'sync_error' => null,
        ]);
    }

    public function markAsSyncFailed(string $error): void
    {
        $this->update([
            'synced_to_router' => false,
            'sync_error' => $error,
        ]);
    }

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

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
