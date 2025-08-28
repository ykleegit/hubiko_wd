<?php

namespace Hubiko\WhatStore\Services\Payment;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PaypalPaymentGateway extends AbstractPaymentGateway
{
    /**
     * PayPal API base URL.
     *
     * @var string
     */
    protected $apiBaseUrl;

    /**
     * PayPal access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Initialize the payment gateway with configuration.
     *
     * @param array $config
     * @return self
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->apiBaseUrl = $this->testMode 
            ? 'https://api-m.sandbox.paypal.com' 
            : 'https://api-m.paypal.com';

        // Get PayPal access token
        $this->getAccessToken();

        return $this;
    }

    /**
     * Get PayPal access token.
     *
     * @return string
     */
    protected function getAccessToken()
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->asForm()
                ->post("{$this->apiBaseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            $data = $response->json();

            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                return $data['access_token'];
            }

            Log::error('PayPal access token error', $data);
            return null;
        } catch (Exception $e) {
            Log::error('PayPal access token exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Process a payment.
     *
     * @param float $amount
     * @param string $currency
     * @param array $metadata
     * @return array
     */
    public function processPayment(float $amount, string $currency, array $metadata = [])
    {
        try {
            $returnUrl = $metadata['return_url'] ?? route('whatstore.paypal.success');
            $cancelUrl = $metadata['cancel_url'] ?? route('whatstore.paypal.cancel');
            
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiBaseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'amount' => [
                                'currency_code' => $currency,
                                'value' => number_format($amount, 2, '.', ''),
                            ],
                            'reference_id' => $metadata['order_id'] ?? uniqid(),
                            'custom_id' => $metadata['custom_id'] ?? null,
                        ],
                    ],
                    'application_context' => [
                        'return_url' => $returnUrl,
                        'cancel_url' => $cancelUrl,
                        'brand_name' => $metadata['brand_name'] ?? config('app.name'),
                        'shipping_preference' => 'NO_SHIPPING',
                    ],
                ]);

            $data = $response->json();

            if (isset($data['id'])) {
                // Find approval URL
                $approvalUrl = null;
                foreach ($data['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approvalUrl = $link['href'];
                        break;
                    }
                }

                $this->logActivity('order_created', [
                    'order_id' => $data['id'],
                    'amount' => $amount,
                    'currency' => $currency,
                ]);

                return [
                    'success' => true,
                    'order_id' => $data['id'],
                    'approval_url' => $approvalUrl,
                ];
            }

            $this->logActivity('payment_error', [
                'error' => $data['message'] ?? 'Unknown error',
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            $this->logActivity('payment_exception', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Capture a PayPal payment.
     *
     * @param string $orderId
     * @return array
     */
    public function capturePayment($orderId)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiBaseUrl}/v2/checkout/orders/{$orderId}/capture", []);

            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'COMPLETED') {
                $this->logActivity('payment_captured', [
                    'order_id' => $orderId,
                ]);

                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'transaction_id' => $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                ];
            }

            $this->logActivity('capture_error', [
                'error' => $data['message'] ?? 'Unknown error',
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Unknown error',
            ];
        } catch (Exception $e) {
            $this->logActivity('capture_exception', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate a payment callback/webhook.
     *
     * @param array $data
     * @return array
     */
    public function validateCallback(array $data)
    {
        if (isset($data['token']) && !empty($data['token'])) {
            $orderId = $data['token'];
            return $this->capturePayment($orderId);
        }

        return [
            'success' => false,
            'error' => 'Invalid callback data',
        ];
    }

    /**
     * Get the payment gateway name.
     *
     * @return string
     */
    public function getName()
    {
        return 'paypal';
    }
} 