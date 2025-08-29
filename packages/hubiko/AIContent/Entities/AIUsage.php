<?php

namespace Hubiko\AIContent\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIUsage extends Model
{
    use HasFactory;

    protected $table = 'ai_usages';

    protected $fillable = [
        'content_id',
        'user_id',
        'action_type',
        'tokens_consumed',
        'cost',
        'response_time',
        'success',
        'error_message',
        'workspace_id'
    ];

    protected $casts = [
        'tokens_consumed' => 'integer',
        'cost' => 'decimal:4',
        'response_time' => 'decimal:2',
        'success' => 'boolean'
    ];

    // Workspace scoping
    public function scopeWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?: getActiveWorkSpace();
        return $query->where('workspace_id', $workspaceId);
    }

    // Status scopes
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    // Relationships
    public function content(): BelongsTo
    {
        return $this->belongsTo(AIContent::class, 'content_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // Helper methods
    public function getFormattedCostAttribute()
    {
        return '$' . number_format($this->cost, 4);
    }

    public function getFormattedResponseTimeAttribute()
    {
        return $this->response_time . 's';
    }
}