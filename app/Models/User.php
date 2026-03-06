<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    public function routers(): HasMany
    {
        return $this->hasMany(Router::class);
    }

    public function bandwidthPlans(): HasMany
    {
        return $this->hasMany(BandwidthPlan::class);
    }

    public function createdCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    public function soldVouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'sold_by');
    }

    public function createdHotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'created_by');
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

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    public function scopeCashiers($query)
    {
        return $query->where('role', 'cashier');
    }

    /**
     * Helper Methods
     */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function canManageRouters(): bool
    {
        return in_array($this->role, ['admin', 'owner', 'staff']);
    }

    public function canSellVouchers(): bool
    {
        return in_array($this->role, ['admin', 'owner', 'staff', 'cashier']);
    }

    public function ownsRouter(Router $router): bool
    {
        return $this->id === $router->user_id || $this->isAdmin();
    }

    public function ownsBandwidthPlan(BandwidthPlan $bandwidthPlan): bool
    {
        return $this->id === $bandwidthPlan->user_id || $this->isAdmin();
    }

    public function ownsCustomer(Customer $customer): bool
    {
        // Admins can access all customers
        if ($this->isAdmin()) {
            return true;
        }

        // Check if user created the customer
        if ($customer->created_by === $this->id) {
            return true;
        }

        // Check if customer has vouchers or hotspot users on user's routers
        $routerIds = $this->routers()->pluck('id');

        $hasVouchers = $customer->vouchers()->whereIn('router_id', $routerIds)->exists();
        $hasHotspotUsers = $customer->hotspotUsers()->whereIn('router_id', $routerIds)->exists();

        return $hasVouchers || $hasHotspotUsers;
    }
}
