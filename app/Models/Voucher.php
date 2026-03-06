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
        'bandwidth_plan_id',
        'mikrotik_profile_id',
        'router_id',
        'customer_id',
        'status',
        'price',
        'user_capacity',
        'users_generated',
        'activated_at',
        'expires_at',
        'sold_by',
        'sold_at',
        'mac_address',
        'notes',
        'voucher_hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'user_capacity' => 'integer',
        'users_generated' => 'integer',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    /**
     * Boot method to generate voucher hash
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voucher) {
            if (!$voucher->voucher_hash) {
                $voucher->voucher_hash = \Illuminate\Support\Str::random(32);
            }
        });
    }

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

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
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

    public function scopeHasCapacity($query)
    {
        return $query->whereRaw('users_generated < user_capacity');
    }

    public function scopeUsed($query)
    {
        return $query->where('users_generated', '>', 0);
    }

    /**
     * Helper Methods
     */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function hasCapacity(): bool
    {
        return $this->users_generated < $this->user_capacity;
    }

    public function getRemainingCapacity(): int
    {
        return max(0, $this->user_capacity - $this->users_generated);
    }

    public function isSold(): bool
    {
        return !is_null($this->sold_at);
    }

    public function isActivated(): bool
    {
        return !is_null($this->activated_at);
    }

    public function incrementUsersGenerated(): void
    {
        $this->increment('users_generated');
    }

    public function markAsInactive(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function markAsActive(): void
    {
        $this->update(['status' => 'active']);
    }
}
