<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'bandwidth_plan_id',
        'router_id',
        'customer_id',
        'status',
        'price',
        'activated_at',
        'expires_at',
        'sold_by',
        'sold_at',
        'mac_address',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function bandwidthPlan(): BelongsTo
    {
        return $this->belongsTo(BandwidthPlan::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeDisabled($query)
    {
        return $query->where('status', 'disabled');
    }

    public function scopeSold($query)
    {
        return $query->whereNotNull('sold_at');
    }

    public function scopeUnsold($query)
    {
        return $query->whereNull('sold_at');
    }

    public function scopeActivated($query)
    {
        return $query->whereNotNull('activated_at');
    }

    public function scopeNotActivated($query)
    {
        return $query->whereNull('activated_at');
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

    /**
     * Helper Methods
     */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isDisabled(): bool
    {
        return $this->status === 'disabled';
    }

    public function isSold(): bool
    {
        return !is_null($this->sold_at);
    }

    public function isActivated(): bool
    {
        return !is_null($this->activated_at);
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'used',
            'activated_at' => now(),
            'expires_at' => $this->calculateExpirationDate(),
        ]);
    }

    public function markAsSold(int $userId, ?int $customerId = null): void
    {
        $this->update([
            'sold_by' => $userId,
            'sold_at' => now(),
            'customer_id' => $customerId,
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

        $status = $this->isActivated() ? 'used' : 'active';
        $this->update(['status' => $status]);
    }

    public function checkAndUpdateExpiration(): void
    {
        if ($this->expires_at && $this->expires_at->isPast() && !$this->isExpired()) {
            $this->update(['status' => 'expired']);
        }
    }

    private function calculateExpirationDate(): ?\DateTime
    {
        if (!$this->bandwidthPlan) {
            return null;
        }

        $duration = $this->bandwidthPlan->getValidityDuration();

        if (!$duration) {
            return null;
        }

        return now()->addSeconds($duration);
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
}
