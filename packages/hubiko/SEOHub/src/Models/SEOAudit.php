<?php

namespace Hubiko\SEOHub\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SEOAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'audit_type',
        'status',
        'score',
        'total_pages_crawled',
        'total_issues_found',
        'critical_issues',
        'warning_issues',
        'notice_issues',
        'audit_data',
        'recommendations',
        'started_at',
        'completed_at',
        'created_by'
    ];

    protected $casts = [
        'score' => 'integer',
        'total_pages_crawled' => 'integer',
        'total_issues_found' => 'integer',
        'critical_issues' => 'integer',
        'warning_issues' => 'integer',
        'notice_issues' => 'integer',
        'audit_data' => 'array',
        'recommendations' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(SEOWebsite::class, 'website_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(SEOIssue::class, 'audit_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function getScoreColorAttribute()
    {
        if ($this->score >= 80) return 'success';
        if ($this->score >= 60) return 'warning';
        return 'danger';
    }

    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }
}
