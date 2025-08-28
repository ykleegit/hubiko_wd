<?php

namespace Hubiko\SEOHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOWebsite extends Model
{
    protected $table = 'seo_websites';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'name',
        'url',
        'host',
        'description',
        'settings',
        'status',
        'last_audit_at',
        'next_audit_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_audit_at' => 'datetime',
        'next_audit_at' => 'datetime',
    ];

    /**
     * Get the audits for this website
     */
    public function audits(): HasMany
    {
        return $this->hasMany(SEOAudit::class, 'website_id');
    }

    /**
     * Get the keywords for this website
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(SEOKeyword::class, 'website_id');
    }

    /**
     * Get the competitors for this website
     */
    public function competitors(): HasMany
    {
        return $this->hasMany(SEOCompetitor::class, 'website_id');
    }

    /**
     * Get the monitoring data for this website
     */
    public function monitoring(): HasMany
    {
        return $this->hasMany(SEOMonitoring::class, 'website_id');
    }

    /**
     * Get the latest audit
     */
    public function latestAudit()
    {
        return $this->audits()->latest('completed_at')->first();
    }

    /**
     * Get the latest SEO score
     */
    public function getLatestScoreAttribute()
    {
        $latestAudit = $this->latestAudit();
        return $latestAudit ? $latestAudit->score : 0;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            'monitoring' => '<span class="badge bg-info">Monitoring</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Check if website needs audit
     */
    public function needsAudit(): bool
    {
        if (!$this->last_audit_at) {
            return true;
        }

        if ($this->next_audit_at && now()->gte($this->next_audit_at)) {
            return true;
        }

        return false;
    }

    /**
     * Get domain from URL
     */
    public function getDomainAttribute()
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    /**
     * Scope for active websites
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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
