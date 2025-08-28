<?php

namespace Modules\WhatStore\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Customer extends Model
{
    protected $table = 'whatstore_customers';
    
    protected $fillable = [
        'whatsapp_number',
        'name',
        'email',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'preferences',
        'extra_data',
        'subscribed',
        'enabled_ai_bot',
        'has_chat',
        'is_last_message_by_customer',
        'last_message',
        'last_interaction',
        'last_reply_at',
        'last_client_reply_at',
        'last_support_reply_at',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'preferences' => 'array',
        'extra_data' => 'array',
        'subscribed' => 'boolean',
        'enabled_ai_bot' => 'boolean',
        'has_chat' => 'boolean',
        'is_last_message_by_customer' => 'boolean',
        'last_interaction' => 'datetime',
        'last_reply_at' => 'datetime',
        'last_client_reply_at' => 'datetime',
        'last_support_reply_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->workspace = getActiveWorkSpace();
            $model->created_by = auth()->id();
        });
    }

    /**
     * Get orders for this customer
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Get messages for this customer
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'customer_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get notes for this customer
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Message::class, 'customer_id')
                    ->where('is_note', true)
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get customer groups
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'whatstore_customer_group_members', 'customer_id', 'group_id')
                    ->withTimestamps()
                    ->withPivot('joined_at');
    }

    /**
     * Send a message to this customer
     */
    public function sendMessage(
        string $content,
        bool $isFromCustomer = false,
        bool $isCampaignMessage = false,
        string $messageType = 'text',
        string $whatsappMessageId = null,
        array $extra = null
    ): Message {
        // Check for duplicate message
        if ($isFromCustomer && $whatsappMessageId) {
            $existingMessage = Message::where('whatsapp_message_id', $whatsappMessageId)->first();
            if ($existingMessage) {
                return $existingMessage;
            }
        }

        $messageData = [
            'customer_id' => $this->id,
            'type' => $messageType,
            'is_from_customer' => $isFromCustomer,
            'is_campaign_message' => $isCampaignMessage,
            'status' => 'sent',
            'whatsapp_message_id' => $whatsappMessageId,
        ];

        // Set content based on message type
        match($messageType) {
            'text' => $messageData['content'] = $content,
            'image' => $messageData['header_image'] = $content,
            'video' => $messageData['header_video'] = $content,
            'document' => $messageData['header_document'] = $content,
            'audio' => $messageData['header_audio'] = $content,
            'location' => $messageData['header_location'] = $content,
            default => $messageData['content'] = $content,
        };

        $message = Message::create($messageData);

        // Update customer interaction data
        $this->updateInteractionData($content, $isFromCustomer, $isCampaignMessage);

        // Handle bot replies for incoming messages
        if ($isFromCustomer && $this->enabled_ai_bot && !$isCampaignMessage) {
            $this->processBotReplies($content, $message);
        }

        return $message;
    }

    /**
     * Update customer interaction timestamps and data
     */
    protected function updateInteractionData(string $content, bool $isFromCustomer, bool $isCampaignMessage): void
    {
        $updates = [
            'last_interaction' => now(),
            'last_message' => $this->trimString($content, 40),
        ];

        if (!$isCampaignMessage) {
            $updates['has_chat'] = true;
            $updates['last_reply_at'] = now();

            if ($isFromCustomer) {
                $updates['last_client_reply_at'] = now();
                $updates['is_last_message_by_customer'] = true;
            } else {
                $updates['last_support_reply_at'] = now();
                $updates['is_last_message_by_customer'] = false;
            }
        }

        $this->update($updates);
    }

    /**
     * Process bot replies for incoming messages
     */
    protected function processBotReplies(string $content, Message $incomingMessage): void
    {
        $replySent = false;

        // Check text-based replies first
        $replies = Reply::forWorkspace()->active()->get();
        foreach ($replies as $reply) {
            if (!$replySent && $reply->shouldTrigger($content, $this)) {
                $replyData = $reply->generateReply($this);
                Message::create($replyData);
                $replySent = true;
                break;
            }
        }

        // Check campaign-based bot replies
        if (!$replySent) {
            $campaigns = Campaign::forWorkspace()->activeBots()->get();
            foreach ($campaigns as $campaign) {
                if (!$replySent && $campaign->shouldTrigger($content, $this)) {
                    $campaign->generateMessages(null, $this);
                    $replySent = true;
                    break;
                }
            }
        }

        // Mark if bot replied
        if ($replySent) {
            $incomingMessage->update(['bot_has_replied' => true]);
        }

        // Handle special commands
        $this->handleSpecialCommands($content);
    }

    /**
     * Handle special customer commands
     */
    protected function handleSpecialCommands(string $content): void
    {
        $content = strtolower(trim($content));

        // Handle unsubscribe
        if (in_array($content, ['stop', 'stop promotions', 'unsubscribe'])) {
            $this->update(['subscribed' => false]);
            $this->sendMessage(__('You have been unsubscribed from promotional messages.'), false);
        }

        // Handle agent handover
        if (in_array($content, ['talk to human', 'speak to agent', 'human agent'])) {
            $this->update(['enabled_ai_bot' => false]);
            $this->sendMessage(__('You will be connected to a human agent shortly. Thank you for your patience.'), false);
        }
    }

    /**
     * Add a note for this customer
     */
    public function addNote(string $content): Message
    {
        $messageData = [
            'customer_id' => $this->id,
            'type' => 'text',
            'content' => $content,
            'header_text' => __('Note'),
            'is_from_customer' => false,
            'is_note' => true,
            'status' => 'sent',
            'sender_name' => auth()->user()->name ?? 'System',
        ];

        return Message::create($messageData);
    }

    /**
     * Get customer's total order value
     */
    public function getTotalOrderValueAttribute(): float
    {
        return $this->orders()->sum('total_amount');
    }

    /**
     * Get customer's order count
     */
    public function getOrderCountAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Get customer's last order
     */
    public function getLastOrderAttribute()
    {
        return $this->orders()->latest()->first();
    }

    /**
     * Check if customer is a repeat buyer
     */
    public function getIsRepeatBuyerAttribute(): bool
    {
        return $this->order_count > 1;
    }

    /**
     * Get customer status based on activity
     */
    public function getStatusAttribute(): string
    {
        if (!$this->subscribed) return 'unsubscribed';
        if ($this->last_interaction && $this->last_interaction->diffInDays() > 30) return 'inactive';
        if ($this->order_count > 0) return 'customer';
        return 'prospect';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'customer' => 'badge-success',
            'prospect' => 'badge-info',
            'inactive' => 'badge-warning',
            'unsubscribed' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Trim string to specified length
     */
    public function trimString(string $str, int $maxLength): string
    {
        if (mb_strlen($str) <= $maxLength) {
            return $str;
        }

        $trimmed = mb_substr($str, 0, $maxLength);
        $lastSpaceIndex = mb_strrpos($trimmed, ' ');

        if ($lastSpaceIndex !== false) {
            return mb_substr($trimmed, 0, $lastSpaceIndex) . '...';
        }

        return $trimmed . '...';
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
     * Scope for subscribed customers
     */
    public function scopeSubscribed($query)
    {
        return $query->where('subscribed', true);
    }

    /**
     * Scope for customers with orders
     */
    public function scopeWithOrders($query)
    {
        return $query->whereHas('orders');
    }

    /**
     * Scope for active customers (interacted recently)
     */
    public function scopeActive($query, $days = 30)
    {
        return $query->where('last_interaction', '>=', now()->subDays($days));
    }
}
