<?php

namespace Hubiko\SEOHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOIssue extends Model
{
    protected $table = 'seo_issues';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'audit_id',
        'type',
        'severity',
        'title',
        'description',
        'recommendation',
        'element',
        'details',
        'status',
        'fixed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'fixed_at' => 'datetime',
    ];

    /**
     * Get the audit this issue belongs to
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(SEOAudit::class, 'audit_id');
    }

    /**
     * Get severity badge HTML
     */
    public function getSeverityBadgeAttribute()
    {
        $badges = [
            'major' => '<span class="badge bg-danger">Major</span>',
            'moderate' => '<span class="badge bg-warning">Moderate</span>',
            'minor' => '<span class="badge bg-info">Minor</span>',
        ];

        return $badges[$this->severity] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'open' => '<span class="badge bg-danger">Open</span>',
            'fixed' => '<span class="badge bg-success">Fixed</span>',
            'ignored' => '<span class="badge bg-secondary">Ignored</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Check if issue is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if issue is fixed
     */
    public function isFixed(): bool
    {
        return $this->status === 'fixed';
    }

    /**
     * Mark issue as fixed
     */
    public function markAsFixed()
    {
        $this->update([
            'status' => 'fixed',
            'fixed_at' => now(),
        ]);
    }

    /**
     * Scope for open issues
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
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
