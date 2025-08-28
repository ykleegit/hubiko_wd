<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_board_id',
        'title',
        'meeting_type',
        'meeting_date',
        'meeting_time',
        'location',
        'virtual_meeting_link',
        'agenda',
        'minutes',
        'status',
        'quorum_required',
        'quorum_present',
        'chairman_id',
        'secretary_id',
        'attachments',
        'created_by'
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'meeting_time' => 'datetime',
        'quorum_required' => 'integer',
        'quorum_present' => 'integer',
        'agenda' => 'array',
        'attachments' => 'array'
    ];

    public function companyBoard(): BelongsTo
    {
        return $this->belongsTo(CompanyBoard::class, 'company_board_id');
    }

    public function chairman(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'chairman_id');
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'secretary_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class, 'meeting_id');
    }

    public function resolutions(): HasMany
    {
        return $this->hasMany(Resolution::class, 'meeting_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('meeting_date', '>', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getIsQuorumMetAttribute()
    {
        return $this->quorum_present >= $this->quorum_required;
    }

    public function getAttendanceRateAttribute()
    {
        $totalDirectors = $this->companyBoard->directors()->active()->count();
        return $totalDirectors > 0 ? ($this->quorum_present / $totalDirectors) * 100 : 0;
    }
}
