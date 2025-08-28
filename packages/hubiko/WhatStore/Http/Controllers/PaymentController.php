<?php

namespace Hubiko\WhatStore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Hubiko\WhatStore\Services\Payment\PaymentGatewayFactory;

class PaymentController extends Controller
{
    /**
     * The payment gateway factory.
     *
     * @var PaymentGatewayFactory
     */
    protected $paymentFactory;

    /**
     * Create a new controller instance.
     *
     * @param PaymentGatewayFactory $paymentFactory
     * @return void
     */
    public function __construct(PaymentGatewayFactory $paymentFactory)
    {
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * Process a Stripe payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processStripePayment(Request $request)
    {
        try {
            $gateway = $this->paymentFactory->gateway('stripe', [
                'api_key' => config('whatstore.payments.stripe.api_key'),
                'api_secret' => config('whatstore.payments.stripe.api_secret'),
                'webhook_secret' => config('whatstore.payments.stripe.webhook_secret'),
                'test_mode' => config('whatstore.payments.stripe.test_mode'),
            ]);

            $amount = $request->input('amount');
            $currency = $request->input('currency', 'USD');
            $metadata = [
                'order_id' => $request->input('order_id'),
                'customer_id' => $request->input('customer_id'),
                'store_id' => $request->input('store_id'),
                'description' => $request->input('description'),
            ];

            $result = $gateway->processPayment($amount, $currency, $metadata);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'client_secret' => $result['client_secret'],
                    'payment_intent' => $result['payment_intent']->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Stripe payment processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Stripe payment success.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stripeSuccess(Request $request)
    {
        $paymentIntentId = $request->input('payment_intent');
        $redirectUrl = $request->input('redirect_url', '/');

        // You can add additional logic here to update order status, etc.

        return redirect($redirectUrl)->with('success', __('Payment completed successfully.'));
    }

    /**
     * Handle Stripe payment cancellation.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stripeCancel(Request $request)
    {
        $redirectUrl = $request->input('redirect_url', '/');

        return redirect($redirectUrl)->with('error', __('Payment was cancelled.'));
    }

    /**
     * Process a PayPal payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPaypalPayment(Request $request)
    {
        try {
            $gateway = $this->paymentFactory->gateway('paypal', [
                'api_key' => config('whatstore.payments.paypal.client_id'),
                'api_secret' => config('whatstore.payments.paypal.client_secret'),
                'test_mode' => config('whatstore.payments.paypal.test_mode'),
                'currency' => config('whatstore.payments.paypal.currency'),
            ]);

            $amount = $request->input('amount');
            $currency = $request->input('currency', config('whatstore.payments.paypal.currency'));
            $metadata = [
                'order_id' => $request->input('order_id'),
                'custom_id' => $request->input('custom_id'),
                'return_url' => $request->input('return_url', route('whatstore.paypal.success')),
                'cancel_url' => $request->input('cancel_url', route('whatstore.paypal.cancel')),
                'brand_name' => $request->input('brand_name', config('app.name')),
            ];

            $result = $gateway->processPayment($amount, $currency, $metadata);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'order_id' => $result['order_id'],
                    'approval_url' => $result['approval_url'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PayPal payment processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle PayPal payment success.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paypalSuccess(Request $request)
    {
        try {
            $gateway = $this->paymentFactory->gateway('paypal', [
                'api_key' => config('whatstore.payments.paypal.client_id'),
                'api_secret' => config('whatstore.payments.paypal.client_secret'),
                'test_mode' => config('whatstore.payments.paypal.test_mode'),
                'currency' => config('whatstore.payments.paypal.currency'),
            ]);

            $result = $gateway->validateCallback($request->all());

            $redirectUrl = $request->input('redirect_url', '/');

            if ($result['success']) {
                // You can add additional logic here to update order status, etc.
                return redirect($redirectUrl)->with('success', __('Payment completed successfully.'));
            } else {
                return redirect($redirectUrl)->with('error', __('Payment verification failed.'));
            }
        } catch (\Exception $e) {
            Log::error('PayPal success callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/')->with('error', __('An error occurred while processing your payment.'));
        }
    }

    /**
     * Handle PayPal payment cancellation.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paypalCancel(Request $request)
    {
        $redirectUrl = $request->input('redirect_url', '/');

        return redirect($redirectUrl)->with('error', __('Payment was cancelled.'));
    }
} 