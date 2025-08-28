<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_number',
        'client_id',
        'service_id',
        'staff_id',
        'resource_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'price',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status',
        'notes',
        'client_notes',
        'reminder_sent',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'start_time',
        'end_time',
        'deleted_at'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'reminder_sent' => 'boolean'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
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
        
        static::creating(function ($appointment) {
            if (empty($appointment->appointment_number)) {
                $appointment->appointment_number = self::generateAppointmentNumber();
            }
        });
    }

    /**
     * Generate unique appointment number
     */
    public static function generateAppointmentNumber()
    {
        $prefix = 'APT';
        $date = now()->format('Ymd');
        $lastAppointment = self::whereDate('created_at', now())->latest()->first();
        $sequence = $lastAppointment ? (int)substr($lastAppointment->appointment_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the client for this appointment
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the service for this appointment
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the staff member for this appointment
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the resource for this appointment
     */
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Get the user who created this appointment
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter appointments by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Scope to get active appointments
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Scope to get today's appointments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    /**
     * Scope to get upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if appointment can be started
     */
    public function canStart()
    {
        return $this->status === self::STATUS_CONFIRMED && 
               $this->start_time->diffInMinutes(now(), false) >= -15; // Can start 15 minutes early
    }

    /**
     * Check if appointment can be completed
     */
    public function canComplete()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if appointment can be cancelled
     */
    public function canCancel()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) &&
               $this->start_time->isFuture();
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_IN_PROGRESS => 'primary',
            self::STATUS_COMPLETED => 'success',
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

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
