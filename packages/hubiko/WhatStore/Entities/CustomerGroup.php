<?php

namespace Modules\WhatStore\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerGroup extends Model
{
    protected $table = 'whatstore_customer_groups';
    
    protected $fillable = [
        'name',
        'description',
        'criteria',
        'is_dynamic',
        'customer_count',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_dynamic' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->workspace = getActiveWorkSpace();
            $model->created_by = auth()->id();
        });
    }

    /**
     * Get customers in this group
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'whatstore_customer_group_members', 'group_id', 'customer_id')
                    ->withTimestamps()
                    ->withPivot('joined_at');
    }

    /**
     * Add customer to group
     */
    public function addCustomer(Customer $customer): void
    {
        if (!$this->customers()->where('customer_id', $customer->id)->exists()) {
            $this->customers()->attach($customer->id, ['joined_at' => now()]);
            $this->increment('customer_count');
        }
    }

    /**
     * Remove customer from group
     */
    public function removeCustomer(Customer $customer): void
    {
        if ($this->customers()->where('customer_id', $customer->id)->exists()) {
            $this->customers()->detach($customer->id);
            $this->decrement('customer_count');
        }
    }

    /**
     * Update dynamic group membership based on criteria
     */
    public function updateDynamicMembership(): void
    {
        if (!$this->is_dynamic || empty($this->criteria)) {
            return;
        }

        $query = Customer::forWorkspace();
        
        foreach ($this->criteria as $criterion => $value) {
            match($criterion) {
                'has_orders' => $query->whereHas('orders'),
                'no_orders' => $query->whereDoesntHave('orders'),
                'min_orders' => $query->whereHas('orders', function($q) use ($value) {
                    $q->selectRaw('COUNT(*) as order_count')
                      ->groupBy('customer_id')
                      ->havingRaw('order_count >= ?', [$value]);
                }),
                'last_order_days' => $query->whereHas('orders', function($q) use ($value) {
                    $q->where('created_at', '>=', now()->subDays($value));
                }),
                'total_spent_min' => $query->whereHas('orders', function($q) use ($value) {
                    $q->selectRaw('SUM(total_amount) as total_spent')
                      ->groupBy('customer_id')
                      ->havingRaw('total_spent >= ?', [$value]);
                }),
                'city' => $query->where('city', $value),
                'country' => $query->where('country', $value),
                'subscribed' => $query->where('subscribed', $value),
                'active_days' => $query->where('last_interaction', '>=', now()->subDays($value)),
                default => null
            };
        }

        $eligibleCustomers = $query->get();
        
        // Clear existing memberships and add new ones
        $this->customers()->detach();
        
        foreach ($eligibleCustomers as $customer) {
            $this->customers()->attach($customer->id, ['joined_at' => now()]);
        }
        
        $this->update(['customer_count' => $eligibleCustomers->count()]);
    }

    /**
     * Get group type label
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->is_dynamic ? __('Dynamic') : __('Static');
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeAttribute(): string
    {
        return $this->is_dynamic ? 'badge-info' : 'badge-secondary';
    }

    /**
     * Scope for workspace filtering
     */
    public function scopeForWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?? getActiveWorkSpace();
        return $query->where('workspace', $workspaceId);
    }

    /**
     * Scope for dynamic groups
     */
    public function scopeDynamic($query)
    {
        return $query->where('is_dynamic', true);
    }
}
