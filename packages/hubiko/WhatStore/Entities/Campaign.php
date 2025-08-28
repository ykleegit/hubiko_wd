<?php

namespace Modules\WhatStore\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Campaign extends Model
{
    use SoftDeletes;

    protected $table = 'whatstore_campaigns';
    
    protected $fillable = [
        'name',
        'description',
        'template_id',
        'status',
        'target_type',
        'target_criteria',
        'variables',
        'variables_match',
        'media_link',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'read_count',
        'replied_count',
        'is_bot',
        'is_bot_active',
        'bot_type',
        'trigger',
        'used',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'target_criteria' => 'array',
        'variables' => 'array',
        'variables_match' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_bot' => 'boolean',
        'is_bot_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->workspace = getActiveWorkSpace();
            $model->created_by = auth()->id();
        });
    }

    /**
     * Get the template for this campaign
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    /**
     * Get messages sent by this campaign
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'campaign_id');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'completed' => 'badge-success',
            'running' => 'badge-primary',
            'scheduled' => 'badge-info',
            'paused' => 'badge-warning',
            'cancelled' => 'badge-danger',
            'draft' => 'badge-secondary',
            default => 'badge-primary'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => __('Draft'),
            'scheduled' => __('Scheduled'),
            'running' => __('Running'),
            'completed' => __('Completed'),
            'paused' => __('Paused'),
            'cancelled' => __('Cancelled'),
            default => $this->status
        };
    }

    /**
     * Get delivery rate percentage
     */
    public function getDeliveryRateAttribute(): float
    {
        if ($this->sent_count == 0) return 0;
        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    /**
     * Get read rate percentage
     */
    public function getReadRateAttribute(): float
    {
        if ($this->delivered_count == 0) return 0;
        return round(($this->read_count / $this->delivered_count) * 100, 2);
    }

    /**
     * Get reply rate percentage
     */
    public function getReplyRateAttribute(): float
    {
        if ($this->delivered_count == 0) return 0;
        return round(($this->replied_count / $this->delivered_count) * 100, 2);
    }

    /**
     * Check if campaign should trigger for received message
     */
    public function shouldTrigger(string $receivedMessage, Customer $customer): bool
    {
        if (!$this->is_bot || !$this->is_bot_active) {
            return false;
        }

        $receivedMessage = ' ' . strtolower(trim($receivedMessage));
        $triggerValues = $this->trigger;

        // Handle comma-separated triggers
        if (strpos($triggerValues, ',') !== false) {
            $triggerValues = explode(',', $triggerValues);
        }

        $triggers = is_array($triggerValues) ? $triggerValues : [$triggerValues];

        foreach ($triggers as $trigger) {
            $trigger = ' ' . strtolower(trim($trigger));
            
            $matches = match($this->bot_type) {
                'exact_match' => $receivedMessage === $trigger,
                'contains' => stripos($receivedMessage, trim($trigger)) !== false,
                default => false
            };

            if ($matches) {
                $this->increment('used');
                return true;
            }
        }

        return false;
    }

    /**
     * Generate messages for campaign recipients
     */
    public function generateMessages($request = null, Customer $specificCustomer = null): array
    {
        // Determine target customers
        $customers = $this->getTargetCustomers($specificCustomer);
        
        if ($customers->isEmpty()) {
            return [];
        }

        $template = $this->template;
        $messages = [];
        
        $this->total_recipients = $customers->count();
        $this->save();

        // Calculate send time
        $sendTime = $this->calculateSendTime($request);

        foreach ($customers as $customer) {
            $messageData = $this->prepareMessageForCustomer($customer, $template, $sendTime);
            $messages[] = $messageData;
        }

        // Bulk insert messages
        if (!empty($messages)) {
            $chunks = array_chunk($messages, 500);
            foreach ($chunks as $chunk) {
                Message::insert($chunk);
            }
        }

        return $messages;
    }

    /**
     * Get target customers based on campaign settings
     */
    protected function getTargetCustomers($specificCustomer = null): \Illuminate\Support\Collection
    {
        if ($specificCustomer) {
            return collect([$specificCustomer]);
        }

        $query = Customer::forWorkspace()->where('subscribed', true);

        return match($this->target_type) {
            'all_customers' => $query->get(),
            'segment' => $this->getSegmentedCustomers($query),
            'specific_customers' => $this->getSpecificCustomers($query),
            default => collect()
        };
    }

    /**
     * Get segmented customers based on criteria
     */
    protected function getSegmentedCustomers($query): \Illuminate\Support\Collection
    {
        $criteria = $this->target_criteria ?? [];
        
        foreach ($criteria as $criterion => $value) {
            match($criterion) {
                'has_orders' => $query->whereHas('orders'),
                'no_orders' => $query->whereDoesntHave('orders'),
                'last_order_days' => $query->whereHas('orders', function($q) use ($value) {
                    $q->where('created_at', '>=', now()->subDays($value));
                }),
                'total_spent_min' => $query->whereHas('orders', function($q) use ($value) {
                    $q->selectRaw('SUM(total_amount) as total_spent')
                      ->groupBy('customer_id')
                      ->havingRaw('total_spent >= ?', [$value]);
                }),
                default => null
            };
        }

        return $query->get();
    }

    /**
     * Get specific customers by IDs
     */
    protected function getSpecificCustomers($query): \Illuminate\Support\Collection
    {
        $customerIds = $this->target_criteria['customer_ids'] ?? [];
        return $query->whereIn('id', $customerIds)->get();
    }

    /**
     * Calculate send time based on request and timezone
     */
    protected function calculateSendTime($request): string
    {
        if (!$request || !$request->has('send_time') || $request->has('send_now')) {
            return now()->format('Y-m-d H:i:s');
        }

        return Carbon::parse($request->send_time)->format('Y-m-d H:i:s');
    }

    /**
     * Prepare message data for a specific customer
     */
    protected function prepareMessageForCustomer(Customer $customer, Template $template, string $sendTime): array
    {
        $content = $this->processTemplateVariables($template->body_text, $customer);
        $headerText = $this->processTemplateVariables($template->header_text ?? '', $customer);
        
        return [
            'customer_id' => $customer->id,
            'campaign_id' => $this->id,
            'type' => 'template',
            'content' => $content,
            'header_text' => $headerText,
            'footer_text' => $template->footer_text,
            'buttons' => json_encode($template->buttons ?? []),
            'components' => json_encode($template->components ?? []),
            'is_from_customer' => false,
            'is_campaign_message' => true,
            'is_automated' => true,
            'status' => 'pending',
            'scheduled_at' => $sendTime,
            'workspace' => $this->workspace,
            'created_by' => $this->created_by,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Process template variables with customer data
     */
    protected function processTemplateVariables(string $text, Customer $customer): string
    {
        $variables = [
            '{{customer_name}}' => $customer->name ?? 'Customer',
            '{{customer_phone}}' => $customer->whatsapp_number,
            '{{customer_email}}' => $customer->email ?? '',
            '{{customer_city}}' => $customer->city ?? '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $text);
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
     * Scope for active bots
     */
    public function scopeActiveBots($query)
    {
        return $query->where('is_bot', true)->where('is_bot_active', true);
    }

    /**
     * Scope for scheduled campaigns
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '<=', now());
    }
}
