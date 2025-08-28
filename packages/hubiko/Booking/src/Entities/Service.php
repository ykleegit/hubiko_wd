<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'duration_minutes',
        'price',
        'buffer_time_minutes',
        'max_advance_booking_days',
        'min_advance_booking_hours',
        'is_active',
        'requires_staff',
        'requires_resource',
        'image',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'requires_staff' => 'boolean',
        'requires_resource' => 'boolean'
    ];

    /**
     * Get the category for this service
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    /**
     * Get all appointments for this service
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get staff members who can provide this service
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'service_staff');
    }

    /**
     * Get resources required for this service
     */
    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'service_resources');
    }

    /**
     * Get the user who created this service
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter services by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Scope to get active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Get total duration including buffer time
     */
    public function getTotalDurationAttribute()
    {
        return $this->duration_minutes + $this->buffer_time_minutes;
    }
}
