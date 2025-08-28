<?php

namespace Modules\WhatStore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WhatStore\Entities\Customer;
use Modules\WhatStore\Entities\Message;
use Modules\WhatStore\Services\WhatsAppPremiumService;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected $premiumService;

    public function __construct(WhatsAppPremiumService $premiumService)
    {
        $this->premiumService = $premiumService;
    }

    /**
     * Handle WhatsApp webhook verification
     */
    public function verify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        // Verify the webhook
        $verifyToken = config('whatstore.whatsapp.webhook_verify_token', 'your_verify_token');
        
        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified successfully');
            return response($challenge, 200);
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token' => $token
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming WhatsApp messages
     */
    public function webhook(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('WhatsApp webhook received', $data);

            if (!isset($data['entry'])) {
                return response('OK', 200);
            }

            foreach ($data['entry'] as $entry) {
                if (!isset($entry['changes'])) {
                    continue;
                }

                foreach ($entry['changes'] as $change) {
                    if ($change['field'] !== 'messages') {
                        continue;
                    }

                    $this->processMessages($change['value']);
                    $this->processStatuses($change['value']);
                }
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Error', 500);
        }
    }

    /**
     * Process incoming messages
     */
    protected function processMessages($value)
    {
        if (!isset($value['messages'])) {
            return;
        }

        foreach ($value['messages'] as $message) {
            $this->handleIncomingMessage($message, $value['metadata']);
        }
    }

    /**
     * Process message status updates
     */
    protected function processStatuses($value)
    {
        if (!isset($value['statuses'])) {
            return;
        }

        foreach ($value['statuses'] as $status) {
            $this->handleMessageStatus($status);
        }
    }

    /**
     * Handle incoming message
     */
    protected function handleIncomingMessage($messageData, $metadata)
    {
        $phoneNumber = $messageData['from'];
        $whatsappMessageId = $messageData['id'];
        $timestamp = $messageData['timestamp'];

        // Find or create customer
        $customer = $this->findOrCreateCustomer($phoneNumber, $metadata);

        // Extract message content based on type
        $content = '';
        $messageType = 'text';

        if (isset($messageData['text'])) {
            $content = $messageData['text']['body'];
            $messageType = 'text';
        } elseif (isset($messageData['image'])) {
            $content = $messageData['image']['link'] ?? $messageData['image']['id'];
            $messageType = 'image';
        } elseif (isset($messageData['video'])) {
            $content = $messageData['video']['link'] ?? $messageData['video']['id'];
            $messageType = 'video';
        } elseif (isset($messageData['document'])) {
            $content = $messageData['document']['link'] ?? $messageData['document']['id'];
            $messageType = 'document';
        } elseif (isset($messageData['audio'])) {
            $content = $messageData['audio']['link'] ?? $messageData['audio']['id'];
            $messageType = 'audio';
        } elseif (isset($messageData['location'])) {
            $content = json_encode($messageData['location']);
            $messageType = 'location';
        }

        // Create message record
        $message = $customer->sendMessage(
            $content,
            true, // from customer
            false, // not campaign message
            $messageType,
            $whatsappMessageId
        );

        Log::info('Processed incoming WhatsApp message', [
            'customer_id' => $customer->id,
            'message_id' => $message->id,
            'type' => $messageType
        ]);
    }

    /**
     * Handle message status updates
     */
    protected function handleMessageStatus($statusData)
    {
        $whatsappMessageId = $statusData['id'];
        $status = $statusData['status'];
        $timestamp = $statusData['timestamp'];

        $message = Message::where('whatsapp_message_id', $whatsappMessageId)->first();

        if (!$message) {
            Log::warning('Message not found for status update', [
                'whatsapp_message_id' => $whatsappMessageId,
                'status' => $status
            ]);
            return;
        }

        // Update message status
        switch ($status) {
            case 'sent':
                $message->markAsSent();
                break;
            case 'delivered':
                $message->markAsDelivered();
                break;
            case 'read':
                $message->markAsRead();
                break;
            case 'failed':
                $errorMessage = $statusData['errors'][0]['title'] ?? 'Unknown error';
                $message->markAsFailed($errorMessage);
                break;
        }

        Log::info('Updated message status', [
            'message_id' => $message->id,
            'status' => $status
        ]);
    }

    /**
     * Find or create customer from phone number
     */
    protected function findOrCreateCustomer($phoneNumber, $metadata)
    {
        // Clean phone number
        $cleanNumber = $this->cleanPhoneNumber($phoneNumber);

        // Try to find existing customer
        $customer = Customer::where('whatsapp_number', $cleanNumber)->first();

        if ($customer) {
            return $customer;
        }

        // Create new customer
        $customer = Customer::create([
            'whatsapp_number' => $cleanNumber,
            'name' => null, // Will be updated when customer provides name
            'last_interaction' => now(),
            'subscribed' => true,
            'enabled_ai_bot' => true,
            'workspace' => $this->getWorkspaceFromMetadata($metadata),
            'created_by' => 1, // System user
        ]);

        Log::info('Created new customer from WhatsApp', [
            'customer_id' => $customer->id,
            'phone_number' => $cleanNumber
        ]);

        return $customer;
    }

    /**
     * Clean phone number format
     */
    protected function cleanPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters except +
        $cleaned = preg_replace('/[^+\d]/', '', $phoneNumber);
        
        // Ensure it starts with +
        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Get workspace from metadata
     */
    protected function getWorkspaceFromMetadata($metadata)
    {
        // This would need to be implemented based on how you map
        // WhatsApp Business accounts to workspaces
        return getActiveWorkSpace() ?? 1;
    }

    /**
     * Send WhatsApp message via API
     */
    public function sendMessage(Request $request)
    {
        try {
            $customerId = $request->customer_id;
            $content = $request->content;
            $messageType = $request->type ?? 'text';

            $customer = Customer::findOrFail($customerId);

            // Check premium features
            $canSend = $this->premiumService->canSendMessage();
            if (!$canSend['can_send']) {
                return response()->json([
                    'error' => $canSend['reason']
                ], 403);
            }

            // Send message via WhatsApp API
            $response = $this->sendToWhatsAppAPI($customer, $content, $messageType);

            if ($response['success']) {
                // Create message record
                $message = $customer->sendMessage(
                    $content,
                    false, // not from customer
                    false, // not campaign message
                    $messageType,
                    $response['message_id']
                );

                // Track usage for billing
                $this->premiumService->trackMessageUsage();

                return response()->json([
                    'success' => true,
                    'message_id' => $message->id,
                    'whatsapp_message_id' => $response['message_id']
                ]);
            }

            return response()->json([
                'error' => $response['error']
            ], 400);

        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Send message to WhatsApp Business API
     */
    protected function sendToWhatsAppAPI($customer, $content, $messageType)
    {
        // This is a placeholder - implement actual WhatsApp Business API integration
        $accessToken = config('whatstore.whatsapp.access_token');
        $phoneNumberId = config('whatstore.whatsapp.phone_number_id');

        if (!$accessToken || !$phoneNumberId) {
            return [
                'success' => false,
                'error' => 'WhatsApp API not configured'
            ];
        }

        // Prepare message data
        $messageData = [
            'messaging_product' => 'whatsapp',
            'to' => $customer->whatsapp_number,
        ];

        switch ($messageType) {
            case 'text':
                $messageData['type'] = 'text';
                $messageData['text'] = ['body' => $content];
                break;
            case 'image':
                $messageData['type'] = 'image';
                $messageData['image'] = ['link' => $content];
                break;
            case 'video':
                $messageData['type'] = 'video';
                $messageData['video'] = ['link' => $content];
                break;
            case 'document':
                $messageData['type'] = 'document';
                $messageData['document'] = ['link' => $content];
                break;
        }

        try {
            // Make API call to WhatsApp
            $response = $this->makeWhatsAppAPICall($phoneNumberId, $messageData, $accessToken);
            
            return [
                'success' => true,
                'message_id' => $response['messages'][0]['id'] ?? null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make HTTP call to WhatsApp Business API
     */
    protected function makeWhatsAppAPICall($phoneNumberId, $messageData, $accessToken)
    {
        $url = "https://graph.facebook.com/v17.0/{$phoneNumberId}/messages";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('WhatsApp API error: ' . $response);
        }

        return json_decode($response, true);
    }
}
