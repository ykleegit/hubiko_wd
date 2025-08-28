<?php

namespace Hubiko\WhatStore\Services\Webhook\Handlers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Hubiko\WhatStore\Services\Webhook\WebhookHandlerInterface;

class PaypalWebhookHandler implements WebhookHandlerInterface
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
        $headers = $this->getPayPalHeaders($request);
        
        // Verify the webhook signature
        if (!$this->verifyWebhookSignature($payload, $headers)) {
            return [
                'success' => false,
                'message' => 'PayPal webhook signature verification failed',
            ];
        }
        
        try {
            $eventType = $payload['event_type'] ?? '';
            
            // Handle the event based on its type
            switch ($eventType) {
                case 'PAYMENT.AUTHORIZATION.CREATED':
                    return $this->handlePaymentAuthorizationCreated($payload);
                    
                case 'PAYMENT.AUTHORIZATION.VOIDED':
                    return $this->handlePaymentAuthorizationVoided($payload);
                    
                case 'PAYMENT.CAPTURE.COMPLETED':
                    return $this->handlePaymentCaptureCompleted($payload);
                    
                case 'PAYMENT.CAPTURE.DENIED':
                    return $this->handlePaymentCaptureDenied($payload);
                    
                case 'PAYMENT.CAPTURE.REFUNDED':
                    return $this->handlePaymentCaptureRefunded($payload);
                    
                case 'CHECKOUT.ORDER.COMPLETED':
                    return $this->handleCheckoutOrderCompleted($payload);
                    
                default:
                    return [
                        'success' => true,
                        'message' => 'Unhandled event type: ' . $eventType,
                    ];
            }
        } catch (Exception $e) {
            Log::error('PayPal webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            
            return [
                'success' => false,
                'message' => 'Error processing webhook: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get PayPal specific headers from the request.
     *
     * @param Request $request
     * @return array
     */
    protected function getPayPalHeaders(Request $request)
    {
        return [
            'PAYPAL-AUTH-ALGO' => $request->header('PAYPAL-AUTH-ALGO'),
            'PAYPAL-CERT-URL' => $request->header('PAYPAL-CERT-URL'),
            'PAYPAL-TRANSMISSION-ID' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'PAYPAL-TRANSMISSION-SIG' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'PAYPAL-TRANSMISSION-TIME' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];
    }
    
    /**
     * Verify PayPal webhook signature.
     *
     * @param array $payload
     * @param array $headers
     * @return bool
     */
    protected function verifyWebhookSignature($payload, $headers)
    {
        $webhookId = config('whatstore.payments.paypal.webhook_id');
        
        if (empty($webhookId)) {
            Log::warning('PayPal webhook ID not configured');
            return true; // Skip verification if webhook ID is not configured
        }
        
        try {
            $apiUrl = config('whatstore.payments.paypal.test_mode')
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';
            
            $response = Http::withBasicAuth(
                config('whatstore.payments.paypal.client_id'),
                config('whatstore.payments.paypal.client_secret')
            )->post("{$apiUrl}/v1/notifications/verify-webhook-signature", [
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'],
                'cert_url' => $headers['PAYPAL-CERT-URL'],
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'],
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'],
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'],
                'webhook_id' => $webhookId,
                'webhook_event' => $payload,
            ]);
            
            $data = $response->json();
            
            return isset($data['verification_status']) && $data['verification_status'] === 'SUCCESS';
        } catch (Exception $e) {
            Log::error('PayPal webhook signature verification error', [
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Handle payment authorization created event.
     *
     * @param array $payload
     * @return array
     */
    protected function handlePaymentAuthorizationCreated($payload)
    {
        $resource = $payload['resource'] ?? [];
        
        Log::info('PayPal payment authorization created', [
            'authorization_id' => $resource['id'] ?? null,
            'status' => $resource['status'] ?? null,
            'amount' => $resource['amount'] ?? null,
        ]);
        
        // Add custom logic here
        
        return [
            'success' => true,
            'message' => 'Payment authorization created',
            'authorization_id' => $resource['id'] ?? null,
        ];
    }
    
    /**
     * Handle payment authorization voided event.
     *
     * @param array $payload
     * @return array
     */
    protected function handlePaymentAuthorizationVoided($payload)
    {
        $resource = $payload['resource'] ?? [];
        
        Log::info('PayPal payment authorization voided', [
            'authorization_id' => $resource['id'] ?? null,
        ]);
        
        // Add custom logic here
        
        return [
            'success' => true,
            'message' => 'Payment authorization voided',
            'authorization_id' => $resource['id'] ?? null,
        ];
    }
    
    /**
     * Handle payment capture completed event.
     *
     * @param array $payload
     * @return array
     */
    protected function handlePaymentCaptureCompleted($payload)
    {
        $resource = $payload['resource'] ?? [];
        
        Log::info('PayPal payment capture completed', [
            'capture_id' => $resource['id'] ?? null,
            'status' => $resource['status'] ?? null,
            'amount' => $resource['amount'] ?? null,
        ]);
        
        // Add custom logic here to process successful payment
        
        return [
            'success' => true,
            'message' => 'Payment capture completed',
            'capture_id' => $resource['id'] ?? null,
        ];
    }
    
    /**
     * Handle payment capture denied event.
     *
     * @param array $payload
     * @return array
     */
    protected function handlePaymentCaptureDenied($payload)
    {
        $resource = $payload['resource'] ?? [];
        
        Log::info('PayPal payment capture denied', [
            'capture_id' => $resource['id'] ?? null,
            'status' => $resource['status'] ?? null,
        ]);
        
        // Add custom logic here to handle failed payment
        
        return [
            'success' => true,
            'message' => 'Payment capture denied',
            'capture_id' => $resource['id'] ?? null,
        ];
    }
    
    /**
     * Handle payment capture refunded event.
     *
     * @param array $payload
     * @return array
     */
    protected function handlePaymentCaptureRefunded($payload)
    {
        $resource = $payload['resource'] ?? [];
        
        Log::info('PayPal payment capture refunded', [
            'capture_id' => $resource['id'] ?? null,
            'status' => $resource['status'] ?? null,
            'amount' => $resource['amount'] ?? null,
        ]);
        
        // Add custom logic here to process refund
        
        return [
            'success' => true,
            'message' => 'Payment capture refunded',
            'capture_id' => $resource['id'] ?? null,
        ];
    }
    
    /**
     * Handle checkout order completed event.
     *
     * @param array $payload
     * @return array
     */
    protected function handleCheckoutOrderCompleted($payload)
    {
        $resource = $payload['resource'] ?? [];
        
        Log::info('PayPal checkout order completed', [
            'order_id' => $resource['id'] ?? null,
            'status' => $resource['status'] ?? null,
        ]);
        
        // Add custom logic here
        
        return [
            'success' => true,
            'message' => 'Checkout order completed',
            'order_id' => $resource['id'] ?? null,
        ];
    }
} 