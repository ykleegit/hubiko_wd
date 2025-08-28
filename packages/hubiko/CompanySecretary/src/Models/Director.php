<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Director extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_board_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'nationality',
        'identification_number',
        'identification_type',
        'date_of_birth',
        'appointment_date',
        'resignation_date',
        'director_type',
        'is_chairman',
        'is_independent',
        'is_active',
        'qualifications',
        'experience',
        'other_directorships',
        'created_by'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'appointment_date' => 'date',
        'resignation_date' => 'date',
        'is_chairman' => 'boolean',
        'is_independent' => 'boolean',
        'is_active' => 'boolean',
        'qualifications' => 'array',
        'other_directorships' => 'array'
    ];

    public function companyBoard(): BelongsTo
    {
        return $this->belongsTo(CompanyBoard::class, 'company_board_id');
    }

    public function meetingAttendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class, 'director_id');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getTenureAttribute()
    {
        if ($this->appointment_date) {
            $endDate = $this->resignation_date ?: now();
            return $this->appointment_date->diffInYears($endDate);
        }
        return 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeChairman($query)
    {
        return $query->where('is_chairman', true);
    }

    public function scopeIndependent($query)
    {
        return $query->where('is_independent', true);
    }
}
