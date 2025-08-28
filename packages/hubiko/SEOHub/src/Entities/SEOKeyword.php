<?php

namespace Hubiko\SEOHub\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOKeyword extends Model
{
    protected $table = 'seo_keywords';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'website_id',
        'keyword',
        'search_volume',
        'difficulty',
        'current_position',
        'previous_position',
        'target_url',
        'status',
        'ranking_history',
        'last_checked_at',
    ];

    protected $casts = [
        'difficulty' => 'decimal:1',
        'ranking_history' => 'array',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Get the website this keyword belongs to
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(SEOWebsite::class, 'website_id');
    }

    /**
     * Get position change
     */
    public function getPositionChangeAttribute()
    {
        if (!$this->current_position || !$this->previous_position) {
            return 0;
        }

        return $this->previous_position - $this->current_position;
    }

    /**
     * Get position change direction
     */
    public function getPositionTrendAttribute()
    {
        $change = $this->position_change;
        
        if ($change > 0) return 'up';
        if ($change < 0) return 'down';
        return 'stable';
    }

    /**
     * Get difficulty level text
     */
    public function getDifficultyLevelAttribute()
    {
        if ($this->difficulty >= 8) return 'Very Hard';
        if ($this->difficulty >= 6) return 'Hard';
        if ($this->difficulty >= 4) return 'Medium';
        if ($this->difficulty >= 2) return 'Easy';
        return 'Very Easy';
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'tracking' => '<span class="badge bg-success">Tracking</span>',
            'paused' => '<span class="badge bg-warning">Paused</span>',
            'archived' => '<span class="badge bg-secondary">Archived</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Scope for active keywords
     */
    public function scopeTracking($query)
    {
        return $query->where('status', 'tracking');
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
