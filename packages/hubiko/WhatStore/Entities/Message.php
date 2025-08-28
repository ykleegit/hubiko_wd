<?php

namespace Modules\WhatStore\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'whatstore_messages';
    
    protected $fillable = [
        'customer_id',
        'campaign_id',
        'whatsapp_message_id',
        'type',
        'content',
        'header_text',
        'header_image',
        'header_video',
        'header_audio',
        'header_document',
        'header_location',
        'footer_text',
        'buttons',
        'components',
        'is_from_customer',
        'is_campaign_message',
        'is_automated',
        'is_note',
        'bot_has_replied',
        'status',
        'error_message',
        'sender_name',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'extra',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'buttons' => 'array',
        'components' => 'array',
        'extra' => 'array',
        'is_from_customer' => 'boolean',
        'is_campaign_message' => 'boolean',
        'is_automated' => 'boolean',
        'is_note' => 'boolean',
        'bot_has_replied' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->workspace = getActiveWorkSpace();
            $model->created_by = auth()->id();
        });
    }

    /**
     * Get the customer this message belongs to
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the campaign this message belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'sent' => 'badge-primary',
            'delivered' => 'badge-info',
            'read' => 'badge-success',
            'failed' => 'badge-danger',
            'pending' => 'badge-warning',
            default => 'badge-secondary'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => __('Pending'),
            'sent' => __('Sent'),
            'delivered' => __('Delivered'),
            'read' => __('Read'),
            'failed' => __('Failed'),
            default => $this->status
        };
    }

    /**
     * Get message direction
     */
    public function getDirectionAttribute(): string
    {
        return $this->is_from_customer ? 'incoming' : 'outgoing';
    }

    /**
     * Get display content based on message type
     */
    public function getDisplayContentAttribute(): string
    {
        return match($this->type) {
            'text' => $this->content ?? '',
            'image' => '📷 ' . __('Image'),
            'video' => '🎥 ' . __('Video'),
            'document' => '📄 ' . __('Document'),
            'audio' => '🎵 ' . __('Audio'),
            'location' => '📍 ' . __('Location'),
            'template' => $this->content ?? __('Template Message'),
            default => $this->content ?? ''
        };
    }

    /**
     * Check if message has media
     */
    public function hasMedia(): bool
    {
        return !empty($this->header_image) || 
               !empty($this->header_video) || 
               !empty($this->header_audio) || 
               !empty($this->header_document);
    }

    /**
     * Get media URL
     */
    public function getMediaUrlAttribute(): ?string
    {
        return $this->header_image ?? 
               $this->header_video ?? 
               $this->header_audio ?? 
               $this->header_document;
    }

    /**
     * Mark message as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now()
        ]);
    }

    /**
     * Mark message as failed
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
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
     * Scope for incoming messages
     */
    public function scopeIncoming($query)
    {
        return $query->where('is_from_customer', true);
    }

    /**
     * Scope for outgoing messages
     */
    public function scopeOutgoing($query)
    {
        return $query->where('is_from_customer', false);
    }

    /**
     * Scope for campaign messages
     */
    public function scopeCampaignMessages($query)
    {
        return $query->where('is_campaign_message', true);
    }

    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for scheduled messages
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<=', now())
                    ->where('status', 'pending');
    }

    /**
     * Scope for recent messages
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
