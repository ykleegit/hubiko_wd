<?php

namespace Hubiko\WhatStore\Services\Webhook\Handlers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Hubiko\WhatStore\Services\Webhook\WebhookHandlerInterface;

class StripeWebhookHandler implements WebhookHandlerInterface
{
    /**
     * Handle an incoming webhook request.
     *
     * @param Request $request
     * @return array
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('whatstore.payments.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );

            // Handle the event based on its type
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    return $this->handlePaymentIntentSucceeded($paymentIntent);

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    return $this->handlePaymentIntentFailed($paymentIntent);

                case 'charge.succeeded':
                    $charge = $event->data->object;
                    return $this->handleChargeSucceeded($charge);

                case 'charge.failed':
                    $charge = $event->data->object;
                    return $this->handleChargeFailed($charge);

                case 'checkout.session.completed':
                    $session = $event->data->object;
                    return $this->handleCheckoutSessionCompleted($session);

                default:
                    return [
                        'success' => true,
                        'message' => 'Unhandled event type: ' . $event->type,
                    ];
            }
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Signature verification failed: ' . $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error('Stripe webhook processing error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error processing webhook: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle payment intent succeeded event.
     *
     * @param \Stripe\PaymentIntent $paymentIntent
     * @return array
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        // Update order status or trigger other actions based on payment success
        Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100, // Convert from cents
            'currency' => $paymentIntent->currency,
            'metadata' => $paymentIntent->metadata->toArray(),
        ]);

        // Add custom logic here to update your database or trigger other events

        return [
            'success' => true,
            'message' => 'Payment intent succeeded',
            'payment_intent_id' => $paymentIntent->id,
        ];
    }

    /**
     * Handle payment intent failed event.
     *
     * @param \Stripe\PaymentIntent $paymentIntent
     * @return array
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $error = $paymentIntent->last_payment_error;
        
        Log::info('Payment intent failed', [
            'payment_intent_id' => $paymentIntent->id,
            'error_message' => $error ? $error->message : null,
            'error_code' => $error ? $error->code : null,
        ]);

        // Add custom logic here to handle failed payments

        return [
            'success' => true,
            'message' => 'Payment intent failed',
            'payment_intent_id' => $paymentIntent->id,
            'error' => $error ? $error->message : 'Unknown error',
        ];
    }

    /**
     * Handle charge succeeded event.
     *
     * @param \Stripe\Charge $charge
     * @return array
     */
    protected function handleChargeSucceeded($charge)
    {
        Log::info('Charge succeeded', [
            'charge_id' => $charge->id,
            'amount' => $charge->amount / 100, // Convert from cents
            'currency' => $charge->currency,
        ]);

        // Add custom logic here

        return [
            'success' => true,
            'message' => 'Charge succeeded',
            'charge_id' => $charge->id,
        ];
    }

    /**
     * Handle charge failed event.
     *
     * @param \Stripe\Charge $charge
     * @return array
     */
    protected function handleChargeFailed($charge)
    {
        Log::info('Charge failed', [
            'charge_id' => $charge->id,
            'error' => $charge->failure_message,
            'code' => $charge->failure_code,
        ]);

        // Add custom logic here

        return [
            'success' => true,
            'message' => 'Charge failed',
            'charge_id' => $charge->id,
            'error' => $charge->failure_message,
        ];
    }

    /**
     * Handle checkout session completed event.
     *
     * @param \Stripe\Checkout\Session $session
     * @return array
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        Log::info('Checkout session completed', [
            'session_id' => $session->id,
            'payment_status' => $session->payment_status,
            'customer' => $session->customer,
        ]);

        // Add custom logic here

        return [
            'success' => true,
            'message' => 'Checkout session completed',
            'session_id' => $session->id,
        ];
    }
} 