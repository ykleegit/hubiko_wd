<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'date_of_birth',
        'gender',
        'preferences',
        'allergies',
        'medical_notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'is_vip',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'date_of_birth',
        'deleted_at'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'preferences' => 'array',
        'allergies' => 'array',
        'is_vip' => 'boolean'
    ];

    /**
     * Get all appointments for this client
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the user who created this client
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter clients by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Search clients by name, email or phone
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Get client's appointment history
     */
    public function getAppointmentHistoryAttribute()
    {
        return $this->appointments()
            ->with(['service', 'staff'])
            ->orderBy('start_time', 'desc')
            ->get();
    }

    /**
     * Get client's total spent
     */
    public function getTotalSpentAttribute()
    {
        return $this->appointments()
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    /**
     * Get client's favorite services
     */
    public function getFavoriteServicesAttribute()
    {
        return $this->appointments()
            ->selectRaw('service_id, COUNT(*) as count')
            ->where('status', 'completed')
            ->groupBy('service_id')
            ->orderBy('count', 'desc')
            ->with('service')
            ->limit(5)
            ->get();
    }
}
