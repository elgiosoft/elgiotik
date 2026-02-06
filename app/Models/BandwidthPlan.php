<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BandwidthPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'rate_limit',
        'download_speed',
        'upload_speed',
        'price',
        'validity_days',
        'validity_hours',
        'data_limit',
        'session_timeout',
        'idle_timeout',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'validity_days' => 'integer',
        'validity_hours' => 'integer',
        'data_limit' => 'integer',
        'session_timeout' => 'integer',
        'idle_timeout' => 'integer',
        'is_active' => 'boolean',
    ];

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

    public function scopeUnlimited($query)
    {
        return $query->whereNull('validity_days')
                    ->whereNull('validity_hours')
                    ->whereNull('data_limit');
    }

    public function scopeTimeLimited($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('validity_days')
              ->orWhereNotNull('validity_hours');
        });
    }

    public function scopeDataLimited($query)
    {
        return $query->whereNotNull('data_limit');
    }

    /**
     * Helper Methods
     */

    public function isUnlimited(): bool
    {
        return is_null($this->validity_days)
            && is_null($this->validity_hours)
            && is_null($this->data_limit);
    }

    public function hasTimeLimit(): bool
    {
        return !is_null($this->validity_days) || !is_null($this->validity_hours);
    }

    public function hasDataLimit(): bool
    {
        return !is_null($this->data_limit);
    }

    public function getValidityDuration(): ?int
    {
        if ($this->validity_days) {
            return $this->validity_days * 24 * 3600; // in seconds
        }

        if ($this->validity_hours) {
            return $this->validity_hours * 3600; // in seconds
        }

        return null;
    }

    public function getFormattedDataLimit(): string
    {
        if (!$this->data_limit) {
            return 'Unlimited';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->data_limit;

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFormattedValidity(): string
    {
        if ($this->validity_days) {
            return $this->validity_days . ' day' . ($this->validity_days > 1 ? 's' : '');
        }

        if ($this->validity_hours) {
            return $this->validity_hours . ' hour' . ($this->validity_hours > 1 ? 's' : '');
        }

        return 'Unlimited';
    }

    public function getActiveVouchersCount(): int
    {
        return $this->vouchers()->where('status', 'active')->count();
    }

    public function getActiveUsersCount(): int
    {
        return $this->hotspotUsers()->where('status', 'active')->count();
    }
}
