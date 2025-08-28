<?php

namespace Hubiko\SEOHub\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'audit_id',
        'title',
        'description',
        'issue_type',
        'severity',
        'category',
        'url',
        'element',
        'recommendation',
        'status',
        'priority',
        'resolved_at',
        'created_by'
    ];

    protected $casts = [
        'resolved_at' => 'datetime'
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(SEOWebsite::class, 'website_id');
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(SEOAudit::class, 'audit_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            'notice' => 'info',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'secondary'
        };
    }
}
