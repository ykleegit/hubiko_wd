<?php

namespace Modules\WhatStore\Services;

use Modules\WhatStore\Entities\Customer;
use Modules\WhatStore\Entities\Message;
use Modules\WhatStore\Entities\Campaign;
use App\Models\UserModuleSubscription;
use App\Services\HybridBillingService;

class WhatsAppPremiumService
{
    protected $hybridBillingService;

    public function __construct(HybridBillingService $hybridBillingService)
    {
        $this->hybridBillingService = $hybridBillingService;
    }

    /**
     * Check if user has premium WhatsApp features
     */
    public function hasPremiumFeatures($userId = null, $workspaceId = null): bool
    {
        $userId = $userId ?? auth()->id();
        $workspaceId = $workspaceId ?? getActiveWorkSpace();

        $subscription = UserModuleSubscription::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('module_name', 'WhatStore')
            ->first();

        if (!$subscription) {
            return false;
        }

        // Check if user has Premium tier
        return $subscription->moduleTier && 
               $subscription->moduleTier->name === 'Premium' &&
               $subscription->is_active;
    }

    /**
     * Check if user can send WhatsApp message (quota check)
     */
    public function canSendMessage($userId = null, $workspaceId = null): array
    {
        $userId = $userId ?? auth()->id();
        $workspaceId = $workspaceId ?? getActiveWorkSpace();

        if (!$this->hasPremiumFeatures($userId, $workspaceId)) {
            return [
                'can_send' => false,
                'reason' => 'Premium subscription required',
                'remaining_quota' => 0
            ];
        }

        // Get current usage
        $currentUsage = $this->getCurrentMessageUsage($userId, $workspaceId);
        $quota = $this->getMessageQuota($userId, $workspaceId);

        $canSend = $quota === -1 || $currentUsage < $quota; // -1 means unlimited

        return [
            'can_send' => $canSend,
            'reason' => $canSend ? null : 'Message quota exceeded',
            'remaining_quota' => $quota === -1 ? -1 : max(0, $quota - $currentUsage),
            'current_usage' => $currentUsage,
            'total_quota' => $quota
        ];
    }

    /**
     * Track message usage for billing
     */
    public function trackMessageUsage($userId = null, $workspaceId = null, $messageCount = 1): void
    {
        $userId = $userId ?? auth()->id();
        $workspaceId = $workspaceId ?? getActiveWorkSpace();

        if (!$this->hasPremiumFeatures($userId, $workspaceId)) {
            return;
        }

        $this->hybridBillingService->trackUsage(
            $userId,
            $workspaceId,
            'WhatStore',
            'whatsapp_messages',
            $messageCount
        );
    }

    /**
     * Get current message usage for the month
     */
    public function getCurrentMessageUsage($userId = null, $workspaceId = null): int
    {
        $userId = $userId ?? auth()->id();
        $workspaceId = $workspaceId ?? getActiveWorkSpace();

        return Message::where('workspace', $workspaceId)
            ->where('created_by', $userId)
            ->where('is_from_customer', false) // Only count outgoing messages
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    /**
     * Get message quota for user
     */
    public function getMessageQuota($userId = null, $workspaceId = null): int
    {
        $userId = $userId ?? auth()->id();
        $workspaceId = $workspaceId ?? getActiveWorkSpace();

        $subscription = UserModuleSubscription::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('module_name', 'WhatStore')
            ->with('moduleTier')
            ->first();

        if (!$subscription || !$subscription->moduleTier) {
            return 0;
        }

        // Get quota from tier features
        $features = $subscription->moduleTier->features ?? [];
        return $features['whatsapp_messages_quota'] ?? 500; // Default 500 messages
    }

    /**
     * Check if specific premium feature is available
     */
    public function hasFeature(string $feature, $userId = null, $workspaceId = null): bool
    {
        if (!$this->hasPremiumFeatures($userId, $workspaceId)) {
            return false;
        }

        $premiumFeatures = [
            'campaigns' => true,
            'ai_chatbot' => true,
            'templates' => true,
            'customer_segmentation' => true,
            'analytics' => true,
            'agent_handover' => true,
            'bulk_messaging' => true,
            'auto_replies' => true,
        ];

        return $premiumFeatures[$feature] ?? false;
    }

    /**
     * Get premium features list
     */
    public function getPremiumFeatures(): array
    {
        return [
            'campaigns' => [
                'name' => 'Marketing Campaigns',
                'description' => 'Create and manage bulk WhatsApp marketing campaigns'
            ],
            'ai_chatbot' => [
                'name' => 'AI Chatbot',
                'description' => 'Automated customer support with intelligent responses'
            ],
            'templates' => [
                'name' => 'Message Templates',
                'description' => 'WhatsApp Business API template management'
            ],
            'customer_segmentation' => [
                'name' => 'Customer Segmentation',
                'description' => 'Advanced customer targeting and grouping'
            ],
            'analytics' => [
                'name' => 'Analytics Dashboard',
                'description' => 'Campaign performance and engagement metrics'
            ],
            'agent_handover' => [
                'name' => 'Agent Handover',
                'description' => 'Seamless bot-to-human support transition'
            ],
            'bulk_messaging' => [
                'name' => 'Bulk Messaging',
                'description' => 'Send messages to multiple customers at once'
            ],
            'auto_replies' => [
                'name' => 'Auto Replies',
                'description' => 'Automated responses to customer messages'
            ],
        ];
    }

    /**
     * Get usage statistics for dashboard
     */
    public function getUsageStats($userId = null, $workspaceId = null): array
    {
        $userId = $userId ?? auth()->id();
        $workspaceId = $workspaceId ?? getActiveWorkSpace();

        $currentUsage = $this->getCurrentMessageUsage($userId, $workspaceId);
        $quota = $this->getMessageQuota($userId, $workspaceId);
        
        $stats = [
            'messages_sent_this_month' => $currentUsage,
            'message_quota' => $quota,
            'quota_percentage' => $quota > 0 ? round(($currentUsage / $quota) * 100, 2) : 0,
            'remaining_messages' => $quota === -1 ? -1 : max(0, $quota - $currentUsage),
        ];

        // Add campaign stats if premium
        if ($this->hasPremiumFeatures($userId, $workspaceId)) {
            $stats['active_campaigns'] = Campaign::forWorkspace($workspaceId)
                ->where('status', 'running')
                ->count();
            
            $stats['total_campaigns'] = Campaign::forWorkspace($workspaceId)->count();
            
            $stats['customers_reached'] = Customer::forWorkspace($workspaceId)
                ->whereHas('messages', function($query) {
                    $query->where('is_campaign_message', true)
                          ->whereMonth('created_at', now()->month);
                })
                ->count();
        }

        return $stats;
    }

    /**
     * Calculate estimated cost for campaign
     */
    public function estimateCampaignCost(Campaign $campaign): array
    {
        $targetCustomers = $campaign->getTargetCustomers();
        $messageCount = $targetCustomers->count();
        
        $quota = $this->getMessageQuota();
        $currentUsage = $this->getCurrentMessageUsage();
        
        $includedMessages = max(0, $quota - $currentUsage);
        $overageMessages = max(0, $messageCount - $includedMessages);
        
        $overageCost = $overageMessages * 0.05; // $0.05 per message overage
        
        return [
            'total_messages' => $messageCount,
            'included_messages' => $includedMessages,
            'overage_messages' => $overageMessages,
            'overage_cost' => $overageCost,
            'currency' => 'USD'
        ];
    }

    /**
     * Check if user can create campaign
     */
    public function canCreateCampaign($userId = null, $workspaceId = null): array
    {
        if (!$this->hasFeature('campaigns', $userId, $workspaceId)) {
            return [
                'can_create' => false,
                'reason' => 'Premium subscription required for campaigns'
            ];
        }

        return [
            'can_create' => true,
            'reason' => null
        ];
    }

    /**
     * Check if user can use AI chatbot
     */
    public function canUseAIChatbot($userId = null, $workspaceId = null): array
    {
        if (!$this->hasFeature('ai_chatbot', $userId, $workspaceId)) {
            return [
                'can_use' => false,
                'reason' => 'Premium subscription required for AI chatbot'
            ];
        }

        return [
            'can_use' => true,
            'reason' => null
        ];
    }

    /**
     * Get billing summary for WhatsApp usage
     */
    public function getBillingSummary($userId = null, $workspaceId = null): array
    {
        $userId = $userId ?? auth()->id();
        $workspaceId = $workspaceId ?? getActiveWorkSpace();

        $subscription = UserModuleSubscription::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('module_name', 'WhatStore')
            ->with(['moduleTier', 'subscriptionCharges'])
            ->first();

        if (!$subscription) {
            return [
                'base_cost' => 0,
                'usage_cost' => 0,
                'total_cost' => 0,
                'currency' => 'USD'
            ];
        }

        $baseCost = $subscription->moduleTier->price ?? 0;
        $usageCost = $subscription->subscriptionCharges()
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return [
            'base_cost' => $baseCost,
            'usage_cost' => $usageCost,
            'total_cost' => $baseCost + $usageCost,
            'currency' => 'USD',
            'billing_period' => now()->format('F Y')
        ];
    }
}
