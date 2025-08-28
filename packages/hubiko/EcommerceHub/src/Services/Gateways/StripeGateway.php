<?php

namespace Hubiko\EcommerceHub\Services\Gateways;

use Exception;

class StripeGateway implements PaymentGatewayInterface
{
    protected $apiKey;
    protected $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('ecommercehub.stripe.secret_key');
        $this->webhookSecret = config('ecommercehub.stripe.webhook_secret');
    }

    public function processPayment($orderData, $paymentData)
    {
        try {
            \Stripe\Stripe::setApiKey($this->apiKey);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $orderData['amount'] * 100, // Convert to cents
                'currency' => $orderData['currency'],
                'payment_method' => $paymentData['payment_method_id'],
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'order_id' => $orderData['order_id'],
                    'workspace_id' => $orderData['workspace_id']
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'status' => 'success',
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                    'gateway_response' => $paymentIntent->toArray()
                ];
            }

            return [
                'status' => 'pending',
                'requires_action' => true,
                'client_secret' => $paymentIntent->client_secret
            ];

        } catch (\Stripe\Exception\CardException $e) {
            return [
                'status' => 'failed',
                'message' => $e->getError()->message
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function handleWebhook($payload, $signature)
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $signature, $this->webhookSecret
            );

            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event['data']['object'];
                    $this->handlePaymentSuccess($paymentIntent);
                    break;
                
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event['data']['object'];
                    $this->handlePaymentFailure($paymentIntent);
                    break;
            }

            return ['status' => 'success'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function refundPayment($transactionId, $amount = null, $reason = null)
    {
        try {
            \Stripe\Stripe::setApiKey($this->apiKey);

            $refundData = ['payment_intent' => $transactionId];
            if ($amount) {
                $refundData['amount'] = $amount * 100; // Convert to cents
            }
            if ($reason) {
                $refundData['reason'] = $reason;
            }

            $refund = \Stripe\Refund::create($refundData);

            return [
                'status' => 'success',
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    protected function handlePaymentSuccess($paymentIntent)
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        if ($orderId) {
            $order = \Hubiko\EcommerceHub\Entities\EcommerceOrder::find($orderId);
            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $paymentIntent['id']
                ]);
                
                event(new \Hubiko\EcommerceHub\Events\OrderPaid($order));
            }
        }
    }

    protected function handlePaymentFailure($paymentIntent)
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        if ($orderId) {
            $order = \Hubiko\EcommerceHub\Entities\EcommerceOrder::find($orderId);
            if ($order) {
                $order->update(['payment_status' => 'failed']);
                
                event(new \Hubiko\EcommerceHub\Events\OrderPaymentFailed($order));
            }
        }
    }
}
