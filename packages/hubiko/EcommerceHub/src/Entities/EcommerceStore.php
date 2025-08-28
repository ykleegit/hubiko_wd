<?php

namespace Hubiko\EcommerceHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'description',
        'logo',
        'currency',
        'timezone',
        'settings',
        'is_active',
        'workspace_id',
        'created_by'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    // Workspace scoping
    public function scopeWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?: getActiveWorkSpace();
        return $query->where('workspace_id', $workspaceId);
    }

    // User scoping
    public function scopeCreatedBy($query, $userId = null)
    {
        $userId = $userId ?: \Auth::id();
        return $query->where('created_by', $userId);
    }

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(EcommerceProduct::class, 'store_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EcommerceCategory::class, 'store_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(EcommerceCustomer::class, 'store_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(EcommerceOrder::class, 'store_id');
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(EcommerceCoupon::class, 'store_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Helper methods
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset('assets/images/default-store-logo.png');
    }

    public function getSetting($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    public function getStoreUrl()
    {
        if ($this->domain) {
            return 'https://' . $this->domain;
        }
        return route('ecommerce.store', ['slug' => $this->slug]);
    }

    // Statistics
    public function getTotalSales()
    {
        return $this->orders()->where('payment_status', 'paid')->sum('total_amount');
    }

    public function getTotalOrders()
    {
        return $this->orders()->count();
    }

    public function getActiveProductsCount()
    {
        return $this->products()->where('status', 'active')->count();
    }

    public function getCustomersCount()
    {
        return $this->customers()->where('status', 'active')->count();
    }
}
