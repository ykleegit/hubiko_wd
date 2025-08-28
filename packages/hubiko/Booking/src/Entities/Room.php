<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_number',
        'room_type',
        'floor_number',
        'capacity',
        'price_per_night',
        'description',
        'amenities',
        'status',
        'images',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'price_per_night' => 'decimal:2'
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_OUT_OF_ORDER = 'out_of_order';

    /**
     * Get all bookings for this room
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get current booking for this room
     */
    public function currentBooking()
    {
        return $this->hasOne(Booking::class)
                    ->where('status', 'checked_in')
                    ->whereDate('check_in_date', '<=', now())
                    ->whereDate('check_out_date', '>=', now());
    }

    /**
     * Get the user who created this room
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter rooms by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Scope to get available rooms
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Check if room is available for given dates
     */
    public function isAvailableForDates($checkIn, $checkOut)
    {
        if ($this->status !== self::STATUS_AVAILABLE) {
            return false;
        }

        $conflictingBookings = $this->bookings()
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->where(function($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                      ->orWhere(function($subQ) use ($checkIn, $checkOut) {
                          $subQ->where('check_in_date', '<=', $checkIn)
                               ->where('check_out_date', '>=', $checkOut);
                      });
                });
            })
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        return $conflictingBookings === 0;
    }

    /**
     * Get room status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_OCCUPIED => 'warning',
            self::STATUS_MAINTENANCE => 'info',
            self::STATUS_OUT_OF_ORDER => 'danger',
            default => 'secondary'
        };
    }
}
