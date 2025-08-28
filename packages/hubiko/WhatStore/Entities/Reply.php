<?php

namespace Modules\WhatStore\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reply extends Model
{
    use SoftDeletes;

    protected $table = 'whatstore_replies';
    
    protected $fillable = [
        'name',
        'trigger_keywords',
        'match_type',
        'reply_text',
        'header_text',
        'footer_text',
        'button1',
        'button1_id',
        'button2',
        'button2_id',
        'button3',
        'button3_id',
        'button_name',
        'button_url',
        'is_active',
        'usage_count',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->workspace = getActiveWorkSpace();
            $model->created_by = auth()->id();
        });
    }

    /**
     * Check if this reply should trigger for the given message
     */
    public function shouldTrigger(string $receivedMessage, Customer $customer): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $receivedMessage = strtolower(trim($receivedMessage));
        $keywords = array_map('trim', explode(',', strtolower($this->trigger_keywords)));

        foreach ($keywords as $keyword) {
            $matches = match($this->match_type) {
                'exact' => $receivedMessage === $keyword,
                'contains' => stripos($receivedMessage, $keyword) !== false,
                'starts_with' => str_starts_with($receivedMessage, $keyword),
                'ends_with' => str_ends_with($receivedMessage, $keyword),
                default => false
            };

            if ($matches) {
                $this->increment('usage_count');
                return true;
            }
        }

        return false;
    }

    /**
     * Generate reply message for customer
     */
    public function generateReply(Customer $customer): array
    {
        $buttons = [];

        // Add quick reply buttons
        for ($i = 1; $i <= 3; $i++) {
            $buttonText = $this->{"button{$i}"};
            $buttonId = $this->{"button{$i}_id"};
            
            if (!empty($buttonText)) {
                $buttons[] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => $buttonId ?? "btn_{$i}",
                        'title' => $buttonText
                    ]
                ];
            }
        }

        // Add CTA button if available
        if (!empty($this->button_name) && !empty($this->button_url)) {
            $buttons[] = [
                'type' => 'cta_url',
                'parameters' => [
                    'display_text' => $this->button_name,
                    'url' => $this->button_url
                ]
            ];
        }

        return [
            'customer_id' => $customer->id,
            'type' => 'text',
            'content' => $this->processVariables($this->reply_text, $customer),
            'header_text' => $this->processVariables($this->header_text ?? '', $customer),
            'footer_text' => $this->processVariables($this->footer_text ?? '', $customer),
            'buttons' => json_encode($buttons),
            'is_from_customer' => false,
            'is_automated' => true,
            'status' => 'pending',
            'workspace' => $this->workspace,
            'created_by' => $this->created_by,
        ];
    }

    /**
     * Process variables in text with customer data
     */
    protected function processVariables(string $text, Customer $customer): string
    {
        if (empty($text)) return '';

        $variables = [
            '{{customer_name}}' => $customer->name ?? 'Customer',
            '{{customer_phone}}' => $customer->whatsapp_number,
            '{{customer_email}}' => $customer->email ?? '',
            '{{customer_city}}' => $customer->city ?? '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $text);
    }

    /**
     * Get match type label
     */
    public function getMatchTypeLabelAttribute(): string
    {
        return match($this->match_type) {
            'exact' => __('Exact Match'),
            'contains' => __('Contains'),
            'starts_with' => __('Starts With'),
            'ends_with' => __('Ends With'),
            default => $this->match_type
        };
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active ? 'badge-success' : 'badge-secondary';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? __('Active') : __('Inactive');
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
     * Scope for active replies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
