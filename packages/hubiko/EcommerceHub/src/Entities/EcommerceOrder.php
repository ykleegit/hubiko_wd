<?php

namespace Hubiko\EcommerceHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'store_id',
        'customer_id',
        'is_guest',
        'customer_details',
        'billing_address',
        'shipping_address',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_details',
        'notes',
        'shipped_at',
        'delivered_at',
        'workspace_id',
        'created_by'
    ];

    protected $casts = [
        'customer_details' => 'array',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'payment_details' => 'array',
        'is_guest' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    // Workspace scoping
    public function scopeWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?: getActiveWorkSpace();
        return $query->where('workspace_id', $workspaceId);
    }

    // Status scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(EcommerceStore::class, 'store_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(EcommerceCustomer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EcommerceOrderItem::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Helper methods
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'secondary'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            'partially_refunded' => 'warning'
        ];

        return $badges[$this->payment_status] ?? 'secondary';
    }

    public function getCustomerNameAttribute()
    {
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        
        if ($this->is_guest && $this->customer_details) {
            return $this->customer_details['first_name'] . ' ' . $this->customer_details['last_name'];
        }
        
        return 'Guest Customer';
    }

    public function getCustomerEmailAttribute()
    {
        if ($this->customer) {
            return $this->customer->email;
        }
        
        if ($this->is_guest && $this->customer_details) {
            return $this->customer_details['email'];
        }
        
        return null;
    }

    // Generate unique order number
    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $timestamp = now()->format('ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }

    // Boot method for auto-generating order number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });

        // Trigger events for ecosystem integration
        static::created(function ($order) {
            event(new \Hubiko\EcommerceHub\Events\OrderCreated($order));
        });

        static::updated(function ($order) {
            if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
                event(new \Hubiko\EcommerceHub\Events\OrderPaid($order));
            }
        });
    }
}
