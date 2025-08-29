<?php

namespace Hubiko\AIContent\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIExport extends Model
{
    use HasFactory;

    protected $table = 'ai_exports';

    protected $fillable = [
        'content_id',
        'user_id',
        'export_type',
        'file_name',
        'file_path',
        'file_size',
        'format',
        'status',
        'download_count',
        'expires_at',
        'workspace_id'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
        'expires_at' => 'datetime'
    ];

    // Workspace scoping
    public function scopeWorkspace($query, $workspaceId = null)
    {
        $workspaceId = $workspaceId ?: getActiveWorkSpace();
        return $query->where('workspace_id', $workspaceId);
    }

    // Status scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
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
    public function getDownloadUrlAttribute()
    {
        if ($this->status === 'completed' && $this->file_path) {
            return route('ai-content.exports.download', $this->id);
        }
        return null;
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
}