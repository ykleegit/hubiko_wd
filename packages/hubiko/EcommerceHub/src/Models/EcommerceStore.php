<?php

namespace Hubiko\EcommerceHub\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website_url',
        'logo',
        'banner_image',
        'theme_id',
        'currency',
        'timezone',
        'is_active',
        'workspace_id',
        'created_by',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(EcommerceProduct::class, 'store_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(EcommerceOrder::class, 'store_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(EcommerceCustomer::class, 'store_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
