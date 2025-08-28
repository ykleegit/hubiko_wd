<?php

namespace Hubiko\SEOHub\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOWebsite extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'domain',
        'description',
        'industry',
        'target_keywords',
        'competitors',
        'google_analytics_id',
        'google_search_console_id',
        'is_active',
        'last_crawled_at',
        'crawl_frequency',
        'workspace_id',
        'created_by',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_crawled_at' => 'datetime',
        'target_keywords' => 'array',
        'competitors' => 'array',
        'settings' => 'array'
    ];

    public function audits(): HasMany
    {
        return $this->hasMany(SEOAudit::class, 'website_id');
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(SEOKeyword::class, 'website_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(SEOIssue::class, 'website_id');
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

    public function getLatestAuditAttribute()
    {
        return $this->audits()->latest()->first();
    }

    public function getTotalIssuesAttribute()
    {
        return $this->issues()->where('status', 'open')->count();
    }
}
