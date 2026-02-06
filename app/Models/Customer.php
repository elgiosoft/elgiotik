<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'location',
        'notes',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
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
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithEmail($query)
    {
        return $query->whereNotNull('email');
    }

    public function scopeWithPhone($query)
    {
        return $query->whereNotNull('phone');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }

    /**
     * Helper Methods
     */

    public function getTotalVouchers(): int
    {
        return $this->vouchers()->count();
    }

    public function getActiveVouchers(): int
    {
        return $this->vouchers()->where('status', 'active')->count();
    }

    public function getUsedVouchers(): int
    {
        return $this->vouchers()->where('status', 'used')->count();
    }

    public function getTotalSpent(): float
    {
        return $this->vouchers()
                    ->whereNotNull('sold_at')
                    ->sum('price');
    }

    public function hasActiveHotspotUsers(): bool
    {
        return $this->hotspotUsers()->where('status', 'active')->exists();
    }

    public function getActiveHotspotUsersCount(): int
    {
        return $this->hotspotUsers()->where('status', 'active')->count();
    }

    public function getOnlineHotspotUsersCount(): int
    {
        return $this->hotspotUsers()->where('is_online', true)->count();
    }
}
