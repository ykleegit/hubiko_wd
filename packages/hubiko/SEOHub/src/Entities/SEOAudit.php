<?php

namespace Hubiko\SEOHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOAudit extends Model
{
    protected $table = 'seo_audits';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'website_id',
        'url',
        'title',
        'meta_description',
        'score',
        'total_issues',
        'major_issues',
        'moderate_issues',
        'minor_issues',
        'passed_tests',
        'audit_data',
        'performance_metrics',
        'seo_metrics',
        'accessibility_metrics',
        'best_practices',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'audit_data' => 'array',
        'performance_metrics' => 'array',
        'seo_metrics' => 'array',
        'accessibility_metrics' => 'array',
        'best_practices' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the website this audit belongs to
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(SEOWebsite::class, 'website_id');
    }

    /**
     * Get the issues for this audit
     */
    public function issues(): HasMany
    {
        return $this->hasMany(SEOIssue::class, 'audit_id');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'running' => '<span class="badge bg-info">Running</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get score color class
     */
    public function getScoreColorAttribute()
    {
        if ($this->score >= 90) return 'text-success';
        if ($this->score >= 70) return 'text-warning';
        return 'text-danger';
    }

    /**
     * Get grade based on score
     */
    public function getGradeAttribute()
    {
        if ($this->score >= 90) return 'A';
        if ($this->score >= 80) return 'B';
        if ($this->score >= 70) return 'C';
        if ($this->score >= 60) return 'D';
        return 'F';
    }

    /**
     * Get duration of audit
     */
    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Check if audit is in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['pending', 'running']);
    }

    /**
     * Check if audit is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if audit failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope for completed audits
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for workspace
     */
    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope for user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
