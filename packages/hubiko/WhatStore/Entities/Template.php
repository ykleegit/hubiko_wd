<?php

namespace Modules\WhatStore\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    use SoftDeletes;

    protected $table = 'whatstore_templates';
    
    protected $fillable = [
        'name',
        'language',
        'category',
        'status',
        'components',
        'header_text',
        'header_format',
        'body_text',
        'footer_text',
        'buttons',
        'template_id',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'components' => 'array',
        'buttons' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->workspace = getActiveWorkSpace();
            $model->created_by = auth()->id();
        });
    }

    /**
     * Get campaigns using this template
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'template_id');
    }

    /**
     * Check if template is referenced by campaigns
     */
    public function isReferenced(): bool
    {
        return $this->campaigns()->exists();
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'APPROVED' => 'badge-success',
            'PENDING' => 'badge-warning',
            'REJECTED' => 'badge-danger',
            'DISABLED' => 'badge-secondary',
            default => 'badge-primary'
        };
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'MARKETING' => __('Marketing'),
            'UTILITY' => __('Utility'),
            'AUTHENTICATION' => __('Authentication'),
            default => $this->category
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'APPROVED' => __('Approved'),
            'PENDING' => __('Pending'),
            'REJECTED' => __('Rejected'),
            'DISABLED' => __('Disabled'),
            default => $this->status
        };
    }

    /**
     * Scope for workspace filtering
     */
    public function scopeForWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?? getActiveWorkSpace();
        return $query->where('workspace', $workspaceId);
    }

    /**
     * Scope for approved templates
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['APPROVED', 'PENDING']);
    }
}
