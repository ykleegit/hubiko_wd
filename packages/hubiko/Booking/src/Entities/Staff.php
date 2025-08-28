<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'specialization',
        'bio',
        'hourly_rate',
        'commission_rate',
        'is_active',
        'avatar',
        'working_hours',
        'break_times',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'working_hours' => 'array',
        'break_times' => 'array'
    ];

    /**
     * Get the user associated with this staff member
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get services this staff member can provide
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_staff');
    }

    /**
     * Get all appointments for this staff member
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the user who created this staff record
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter staff by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Scope to get active staff
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if staff is available at given time
     */
    public function isAvailableAt($datetime, $duration_minutes = 60)
    {
        if (!$this->is_active) {
            return false;
        }

        $dayOfWeek = $datetime->format('l'); // Monday, Tuesday, etc.
        $time = $datetime->format('H:i');

        // Check working hours
        if (!isset($this->working_hours[$dayOfWeek])) {
            return false;
        }

        $workingHours = $this->working_hours[$dayOfWeek];
        if (!$workingHours['is_working']) {
            return false;
        }

        // Check if time falls within working hours
        if ($time < $workingHours['start'] || $time > $workingHours['end']) {
            return false;
        }

        // Check for existing appointments
        $endTime = $datetime->copy()->addMinutes($duration_minutes);
        $conflictingAppointments = $this->appointments()
            ->where(function($query) use ($datetime, $endTime) {
                $query->where(function($q) use ($datetime, $endTime) {
                    $q->whereBetween('start_time', [$datetime, $endTime])
                      ->orWhereBetween('end_time', [$datetime, $endTime])
                      ->orWhere(function($subQ) use ($datetime, $endTime) {
                          $subQ->where('start_time', '<=', $datetime)
                               ->where('end_time', '>=', $endTime);
                      });
                });
            })
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->count();

        return $conflictingAppointments === 0;
    }

    /**
     * Get available time slots for a given date
     */
    public function getAvailableSlots($date, $service_duration = 60)
    {
        $dayOfWeek = $date->format('l');
        
        if (!isset($this->working_hours[$dayOfWeek]) || !$this->working_hours[$dayOfWeek]['is_working']) {
            return [];
        }

        $workingHours = $this->working_hours[$dayOfWeek];
        $slots = [];
        
        $current = $date->copy()->setTimeFromTimeString($workingHours['start']);
        $end = $date->copy()->setTimeFromTimeString($workingHours['end']);

        while ($current->addMinutes($service_duration)->lte($end)) {
            if ($this->isAvailableAt($current, $service_duration)) {
                $slots[] = $current->format('H:i');
            }
            $current->addMinutes(30); // 30-minute intervals
        }

        return $slots;
    }
}
