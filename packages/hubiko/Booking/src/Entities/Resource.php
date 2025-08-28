<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'location',
        'capacity',
        'hourly_rate',
        'is_active',
        'maintenance_schedule',
        'image',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'maintenance_schedule' => 'array'
    ];

    // Resource types for different industries
    const TYPES = [
        'salon' => [
            'Hair Washing Station',
            'Styling Chair',
            'Hair Dryer',
            'Manicure Table',
            'Pedicure Chair',
            'Makeup Station'
        ],
        'beauty' => [
            'Treatment Room',
            'Facial Bed',
            'Massage Table',
            'Waxing Room',
            'Steam Room',
            'Equipment Room'
        ],
        'spa' => [
            'Private Suite',
            'Couples Room',
            'Sauna',
            'Jacuzzi',
            'Relaxation Lounge',
            'Therapy Pool'
        ],
        'medical' => [
            'Consultation Room',
            'Examination Room',
            'Procedure Room',
            'X-Ray Room',
            'Laboratory',
            'Operating Theater'
        ],
        'fitness' => [
            'Personal Training Room',
            'Group Exercise Studio',
            'Yoga Studio',
            'Cardio Area',
            'Weight Room',
            'Recovery Room'
        ],
        'automotive' => [
            'Service Bay',
            'Lift Station',
            'Wash Bay',
            'Detail Bay',
            'Diagnostic Station',
            'Waiting Area'
        ]
    ];

    /**
     * Get services that use this resource
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_resources');
    }

    /**
     * Get all appointments using this resource
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the user who created this resource
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter resources by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Scope to get active resources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if resource is available at given time
     */
    public function isAvailableAt($datetime, $duration_minutes = 60)
    {
        if (!$this->is_active) {
            return false;
        }

        // Check for maintenance schedule
        $dayOfWeek = $datetime->format('l');
        if (isset($this->maintenance_schedule[$dayOfWeek])) {
            $maintenance = $this->maintenance_schedule[$dayOfWeek];
            if ($maintenance['is_maintenance']) {
                $maintenanceStart = $datetime->copy()->setTimeFromTimeString($maintenance['start']);
                $maintenanceEnd = $datetime->copy()->setTimeFromTimeString($maintenance['end']);
                
                if ($datetime->between($maintenanceStart, $maintenanceEnd)) {
                    return false;
                }
            }
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
     * Get utilization rate for a given period
     */
    public function getUtilizationRate($startDate, $endDate)
    {
        $totalHours = $startDate->diffInHours($endDate);
        $bookedHours = $this->appointments()
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'completed'])
            ->sum('duration_minutes') / 60;

        return $totalHours > 0 ? ($bookedHours / $totalHours) * 100 : 0;
    }
}
