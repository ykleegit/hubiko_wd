<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'director_id',
        'attendance_status',
        'attendance_type',
        'check_in_time',
        'check_out_time',
        'notes',
        'proxy_for',
        'created_by'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime'
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(BoardMeeting::class, 'meeting_id');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'director_id');
    }

    public function proxyFor(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'proxy_for');
    }

    public function getDurationAttribute()
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->diffInMinutes($this->check_out_time);
        }
        return null;
    }

    public function scopePresent($query)
    {
        return $query->where('attendance_status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('attendance_status', 'absent');
    }
}
