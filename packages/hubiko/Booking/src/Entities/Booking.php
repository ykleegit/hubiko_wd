<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'guest_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'actual_check_in',
        'actual_check_out',
        'adults',
        'children',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status',
        'special_requests',
        'notes',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'check_in_date',
        'check_out_date',
        'actual_check_in',
        'actual_check_out',
        'deleted_at'
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'actual_check_in' => 'datetime',
        'actual_check_out' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_CHECKED_OUT = 'checked_out';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = self::generateBookingNumber();
            }
        });
    }

    /**
     * Generate unique booking number
     */
    public static function generateBookingNumber()
    {
        $prefix = 'BK';
        $date = now()->format('Ymd');
        $lastBooking = self::whereDate('created_at', now())->latest()->first();
        $sequence = $lastBooking ? (int)substr($lastBooking->booking_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the guest for this booking
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the room for this booking
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the user who created this booking
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter bookings by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Scope to get active bookings
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]);
    }

    /**
     * Get the duration of stay in nights
     */
    public function getNightsAttribute()
    {
        return $this->check_in_date->diffInDays($this->check_out_date);
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if booking can be checked in
     */
    public function canCheckIn()
    {
        return $this->status === self::STATUS_CONFIRMED && 
               $this->check_in_date->isToday() || $this->check_in_date->isPast();
    }

    /**
     * Check if booking can be checked out
     */
    public function canCheckOut()
    {
        return $this->status === self::STATUS_CHECKED_IN;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_CHECKED_IN => 'success',
            self::STATUS_CHECKED_OUT => 'secondary',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_NO_SHOW => 'dark',
            default => 'secondary'
        };
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentStatusColorAttribute()
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'warning',
            self::PAYMENT_STATUS_PARTIAL => 'info',
            self::PAYMENT_STATUS_PAID => 'success',
            self::PAYMENT_STATUS_REFUNDED => 'danger',
            default => 'secondary'
        };
    }
}
