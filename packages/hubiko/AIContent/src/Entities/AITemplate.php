<?php

namespace Hubiko\AIContent\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AITemplate extends Model
{
    use HasFactory;

    protected $table = 'ai_templates';

    protected $fillable = [
        'name',
        'description',
        'category',
        'prompt_template',
        'variables',
        'content_type',
        'default_tone',
        'default_length',
        'is_active',
        'is_system',
        'workspace_id',
        'created_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean'
    ];

    // Workspace scoping
    public function scopeWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?: getActiveWorkSpace();
        return $query->where('workspace_id', $workspaceId);
    }

    // Status scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    // Relationships
    public function contents(): HasMany
    {
        return $this->hasMany(AIContent::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Helper methods
    public function processPrompt($variables = [])
    {
        $prompt = $this->prompt_template;
        
        foreach ($variables as $key => $value) {
            $prompt = str_replace('{{' . $key . '}}', $value, $prompt);
        }
        
        return $prompt;
    }

    public function getUsageCountAttribute()
    {
        return $this->contents()->count();
    }
}
