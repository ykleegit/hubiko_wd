<?php

namespace Hubiko\WhatStore\Services\Webhook;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Hubiko\WhatStore\Entities\Webhook;

class WebhookService
{
    /**
     * Process an incoming webhook event.
     *
     * @param string $type
     * @param Request $request
     * @return array
     */
    public function processWebhook($type, Request $request)
    {
        $handler = $this->getWebhookHandler($type);
        
        if (!$handler) {
            return [
                'success' => false,
                'message' => "Webhook handler for [{$type}] not found",
            ];
        }
        
        try {
            $result = $handler->handle($request);
            
            $this->logWebhook($type, $request, $result);
            
            return $result;
        } catch (Exception $e) {
            Log::error("Webhook processing error", [
                'type' => $type,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get the appropriate webhook handler.
     *
     * @param string $type
     * @return WebhookHandlerInterface|null
     */
    protected function getWebhookHandler($type)
    {
        $handlers = [
            'stripe' => new Handlers\StripeWebhookHandler(),
            'paypal' => new Handlers\PaypalWebhookHandler(),
            'custom' => new Handlers\CustomWebhookHandler(),
        ];
        
        return $handlers[$type] ?? null;
    }
    
    /**
     * Send a webhook notification to an external URL.
     *
     * @param string $url
     * @param array $data
     * @param string $method
     * @return bool
     */
    public function sendWebhook($url, $data, $method = 'POST')
    {
        try {
            $payload = json_encode($data);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Log the webhook attempt
            Log::info('Webhook sent', [
                'url' => $url,
                'method' => $method,
                'status_code' => $httpCode,
                'response' => $response
            ]);
            
            return $httpCode >= 200 && $httpCode < 300;
        } catch (Exception $e) {
            Log::error('Webhook send error', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Log webhook activity.
     *
     * @param string $type
     * @param Request $request
     * @param array $result
     * @return void
     */
    protected function logWebhook($type, Request $request, $result)
    {
        Log::info('Webhook received', [
            'type' => $type,
            'payload' => $request->all(),
            'result' => $result,
            'ip' => $request->ip(),
        ]);
    }
    
    /**
     * Get all available webhooks for a workspace.
     *
     * @param int $workspaceId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWebhooks($workspaceId)
    {
        return Webhook::where('workspace', $workspaceId)
            ->where('created_by', creatorId())
            ->get();
    }
    
    /**
     * Get a specific webhook.
     *
     * @param int $id
     * @param int $workspaceId
     * @return Webhook|null
     */
    public function getWebhook($id, $workspaceId)
    {
        return Webhook::where('id', $id)
            ->where('workspace', $workspaceId)
            ->where('created_by', creatorId())
            ->first();
    }
} 