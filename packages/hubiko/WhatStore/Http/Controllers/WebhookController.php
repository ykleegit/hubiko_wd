<?php

namespace Hubiko\WhatStore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Hubiko\WhatStore\Entities\Order;
use Hubiko\WhatStore\Entities\OrderTransaction;
use Hubiko\WhatStore\Services\Payment\PaymentGatewayFactory;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhook
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $webhookSecret = config('whatstore.payment.stripe.webhook_secret');
        
        $factory = new PaymentGatewayFactory();
        $gateway = $factory->make('stripe');
        
        try {
            $event = $gateway->validateWebhook($payload, $sig_header, $webhookSecret);
            
            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    return $this->handlePaymentSuccess($event->data->object, 'stripe');
                    
                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailure($event->data->object, 'stripe');
                    
                case 'charge.refunded':
                    return $this->handleRefund($event->data->object, 'stripe');
                    
                default:
                    // Unexpected event type
                    Log::info('Received unknown event type: ' . $event->type);
            }
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Handle PayPal webhook
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function paypalWebhook(Request $request)
    {
        $payload = $request->getContent();
        $webhookId = config('whatstore.payment.paypal.webhook_id');
        $webhookSecret = config('whatstore.payment.paypal.webhook_secret');
        
        $factory = new PaymentGatewayFactory();
        $gateway = $factory->make('paypal');
        
        try {
            $event = $gateway->validateWebhook($payload, $webhookId, $webhookSecret);
            
            // Handle the event
            switch ($event->event_type) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    return $this->handlePaymentSuccess($event->resource, 'paypal');
                    
                case 'PAYMENT.CAPTURE.DENIED':
                    return $this->handlePaymentFailure($event->resource, 'paypal');
                    
                case 'PAYMENT.CAPTURE.REFUNDED':
                    return $this->handleRefund($event->resource, 'paypal');
                    
                default:
                    // Unexpected event type
                    Log::info('Received unknown event type from PayPal: ' . $event->event_type);
            }
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Handle successful payment
     *
     * @param object $payload
     * @param string $gateway
     * @return \Illuminate\Http\Response
     */
    protected function handlePaymentSuccess($payload, $gateway)
    {
        try {
            // Extract order ID from metadata
            $orderId = null;
            
            if ($gateway === 'stripe') {
                $orderId = $payload->metadata->order_id ?? null;
                $transactionId = $payload->id;
                $amount = $payload->amount / 100; // Convert cents to dollars
            } elseif ($gateway === 'paypal') {
                $orderId = $payload->custom_id ?? null;
                $transactionId = $payload->id;
                $amount = $payload->amount->value;
            }
            
            if (!$orderId) {
                Log::error("No order ID found in {$gateway} payment metadata");
                return response()->json(['error' => 'No order ID found'], 400);
            }
            
            // Find order
            $order = Order::where('order_number', $orderId)->first();
            
            if (!$order) {
                Log::error("Order not found for {$gateway} payment: {$orderId}");
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // Update order payment status
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->save();
            
            // Create transaction record
            OrderTransaction::create([
                'order_id' => $order->id,
                'transaction_id' => $transactionId,
                'payment_method' => $gateway,
                'amount' => $amount,
                'status' => 'success',
                'metadata' => json_encode($payload),
                'company_id' => $order->company_id,
                'workspace_id' => $order->workspace_id,
            ]);
            
            // Log the success
            Log::info("Payment successful for order {$orderId} via {$gateway}");
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error processing {$gateway} payment success: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle failed payment
     *
     * @param object $payload
     * @param string $gateway
     * @return \Illuminate\Http\Response
     */
    protected function handlePaymentFailure($payload, $gateway)
    {
        try {
            // Extract order ID from metadata
            $orderId = null;
            
            if ($gateway === 'stripe') {
                $orderId = $payload->metadata->order_id ?? null;
                $transactionId = $payload->id;
                $errorMessage = $payload->last_payment_error->message ?? 'Payment failed';
            } elseif ($gateway === 'paypal') {
                $orderId = $payload->custom_id ?? null;
                $transactionId = $payload->id;
                $errorMessage = $payload->status_details->reason ?? 'Payment failed';
            }
            
            if (!$orderId) {
                Log::error("No order ID found in {$gateway} payment failure metadata");
                return response()->json(['error' => 'No order ID found'], 400);
            }
            
            // Find order
            $order = Order::where('order_number', $orderId)->first();
            
            if (!$order) {
                Log::error("Order not found for {$gateway} payment failure: {$orderId}");
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // Update order payment status
            $order->payment_status = 'failed';
            $order->save();
            
            // Create transaction record
            OrderTransaction::create([
                'order_id' => $order->id,
                'transaction_id' => $transactionId,
                'payment_method' => $gateway,
                'amount' => $order->total,
                'status' => 'failed',
                'metadata' => json_encode([
                    'payload' => $payload,
                    'error' => $errorMessage
                ]),
                'company_id' => $order->company_id,
                'workspace_id' => $order->workspace_id,
            ]);
            
            // Log the failure
            Log::info("Payment failed for order {$orderId} via {$gateway}: {$errorMessage}");
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error processing {$gateway} payment failure: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle refund
     *
     * @param object $payload
     * @param string $gateway
     * @return \Illuminate\Http\Response
     */
    protected function handleRefund($payload, $gateway)
    {
        try {
            // Extract data
            $refundId = null;
            $transactionId = null;
            $amount = null;
            
            if ($gateway === 'stripe') {
                $chargeId = $payload->id;
                $refundId = $payload->refunds->data[0]->id ?? null;
                $amount = ($payload->refunds->data[0]->amount ?? 0) / 100;
                
                // Find transaction for this charge
                $transaction = OrderTransaction::where('transaction_id', $chargeId)
                    ->where('payment_method', 'stripe')
                    ->first();
            } elseif ($gateway === 'paypal') {
                $transactionId = $payload->id;
                $refundId = $payload->links[1]->href ?? null; // Link to refund details
                $amount = $payload->amount->value;
                
                // Find transaction
                $transaction = OrderTransaction::where('transaction_id', $transactionId)
                    ->where('payment_method', 'paypal')
                    ->first();
            }
            
            if (!$transaction) {
                Log::error("Transaction not found for {$gateway} refund");
                return response()->json(['error' => 'Transaction not found'], 404);
            }
            
            // Get order
            $order = Order::find($transaction->order_id);
            
            if (!$order) {
                Log::error("Order not found for {$gateway} refund transaction: {$transaction->id}");
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // Update order status if full refund
            if ($amount >= $order->total) {
                $order->payment_status = 'refunded';
                $order->status = 'refunded';
            } else {
                $order->payment_status = 'partially_refunded';
            }
            
            $order->save();
            
            // Create refund transaction record
            OrderTransaction::create([
                'order_id' => $order->id,
                'transaction_id' => $refundId,
                'payment_method' => $gateway,
                'amount' => -$amount, // Negative to indicate refund
                'status' => 'refund',
                'metadata' => json_encode($payload),
                'company_id' => $order->company_id,
                'workspace_id' => $order->workspace_id,
            ]);
            
            // Log the refund
            Log::info("Refund processed for order {$order->order_number} via {$gateway}");
            
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error processing {$gateway} refund: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 