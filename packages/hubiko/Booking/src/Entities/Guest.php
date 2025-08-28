<?php

namespace Hubiko\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'id_type',
        'id_number',
        'date_of_birth',
        'gender',
        'notes',
        'created_by',
        'workspace'
    ];

    protected $dates = [
        'date_of_birth',
        'deleted_at'
    ];

    protected $casts = [
        'date_of_birth' => 'date'
    ];

    /**
     * Get all bookings for this guest
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the user who created this guest
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter guests by workspace
     */
    public function scopeByWorkspace($query, $workspace)
    {
        return $query->where('workspace', $workspace);
    }

    /**
     * Get full name with title
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Search guests by name, email or phone
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}
