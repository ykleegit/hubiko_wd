<?php

namespace Hubiko\WhatStore\Services\Webhook\Handlers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Hubiko\WhatStore\Services\Webhook\WebhookHandlerInterface;

class CustomWebhookHandler implements WebhookHandlerInterface
{
    /**
     * Handle an incoming webhook request.
     *
     * @param Request $request
     * @return array
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        // Process the custom webhook payload
        Log::info('Custom webhook received', ['payload' => $payload]);
        
        // You can add custom logic here to handle the webhook data
        // For example, updating order status, processing inventory, etc.
        
        return [
            'success' => true,
            'message' => 'Custom webhook processed successfully',
            'data' => $payload,
        ];
    }
} 