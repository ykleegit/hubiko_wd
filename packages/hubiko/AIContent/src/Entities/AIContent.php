<?php

namespace Hubiko\AIContent\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AIContent extends Model
{
    use HasFactory;

    protected $table = 'ai_contents';

    protected $fillable = [
        'title',
        'content_type',
        'prompt',
        'generated_content',
        'template_id',
        'language',
        'tone',
        'length',
        'keywords',
        'status',
        'ai_provider',
        'ai_model',
        'tokens_used',
        'generation_time',
        'quality_score',
        'workspace_id',
        'created_by'
    ];

    protected $casts = [
        'keywords' => 'array',
        'tokens_used' => 'integer',
        'generation_time' => 'decimal:2',
        'quality_score' => 'decimal:2'
    ];

    // Workspace scoping
    public function scopeWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?: getActiveWorkSpace();
        return $query->where('workspace_id', $workspaceId);
    }

    // User scoping
    public function scopeCreatedBy($query, $userId = null)
    {
        $userId = $userId ?: \Auth::id();
        return $query->where('created_by', $userId);
    }

    // Status scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(AITemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(AIUsage::class, 'content_id');
    }

    public function exports(): HasMany
    {
        return $this->hasMany(AIExport::class, 'content_id');
    }

    // Helper methods
    public function getWordCountAttribute()
    {
        return str_word_count(strip_tags($this->generated_content));
    }

    public function getCharacterCountAttribute()
    {
        return strlen(strip_tags($this->generated_content));
    }

    public function getReadingTimeAttribute()
    {
        $wordsPerMinute = 200;
        return ceil($this->word_count / $wordsPerMinute);
    }

    public function getFormattedContentAttribute()
    {
        return nl2br(e($this->generated_content));
    }
}
