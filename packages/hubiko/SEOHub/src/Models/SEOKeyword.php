<?php

namespace Hubiko\SEOHub\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SEOKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'keyword',
        'search_volume',
        'difficulty',
        'current_position',
        'target_position',
        'url',
        'competition',
        'cpc',
        'trend_data',
        'last_updated',
        'status',
        'created_by'
    ];

    protected $casts = [
        'search_volume' => 'integer',
        'difficulty' => 'integer',
        'current_position' => 'integer',
        'target_position' => 'integer',
        'cpc' => 'decimal:2',
        'trend_data' => 'array',
        'last_updated' => 'datetime'
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(SEOWebsite::class, 'website_id');
    }

    public function scopeTracked($query)
    {
        return $query->where('status', 'tracking');
    }

    public function scopeTopRanking($query)
    {
        return $query->where('current_position', '<=', 10);
    }

    public function getDifficultyLevelAttribute()
    {
        if ($this->difficulty <= 30) return 'Easy';
        if ($this->difficulty <= 60) return 'Medium';
        return 'Hard';
    }

    public function getPositionChangeAttribute()
    {
        if ($this->target_position && $this->current_position) {
            return $this->target_position - $this->current_position;
        }
        return 0;
    }
}
