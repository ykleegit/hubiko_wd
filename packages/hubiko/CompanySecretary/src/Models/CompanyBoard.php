<?php

namespace Hubiko\CompanySecretary\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'company_registration_number',
        'incorporation_date',
        'registered_address',
        'business_address',
        'company_type',
        'share_capital',
        'authorized_shares',
        'issued_shares',
        'par_value',
        'financial_year_end',
        'is_active',
        'workspace_id',
        'created_by',
        'settings'
    ];

    protected $casts = [
        'incorporation_date' => 'date',
        'financial_year_end' => 'date',
        'share_capital' => 'decimal:2',
        'par_value' => 'decimal:2',
        'authorized_shares' => 'integer',
        'issued_shares' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    public function directors(): HasMany
    {
        return $this->hasMany(Director::class, 'company_board_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(BoardMeeting::class, 'company_board_id');
    }

    public function resolutions(): HasMany
    {
        return $this->hasMany(Resolution::class, 'company_board_id');
    }

    public function filings(): HasMany
    {
        return $this->hasMany(RegulatoryFiling::class, 'company_board_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(\App\Models\WorkSpace::class, 'workspace_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getActiveDirectorsAttribute()
    {
        return $this->directors()->where('is_active', true)->count();
    }

    public function getUpcomingMeetingsAttribute()
    {
        return $this->meetings()->where('meeting_date', '>', now())->count();
    }
}
