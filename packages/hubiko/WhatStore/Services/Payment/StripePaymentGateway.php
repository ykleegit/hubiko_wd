<?php

namespace Hubiko\WhatStore\Services\Payment;

use Exception;
use Illuminate\Support\Facades\Http;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\StripeClient;

class StripePaymentGateway extends AbstractPaymentGateway
{
    /**
     * Gateway identifier
     * 
     * @var string
     */
    protected $gateway = 'stripe';
    
    /**
     * Stripe client
     * 
     * @var \Stripe\StripeClient
     */
    protected $client;
    
    /**
     * Webhook validator
     * 
     * @var mixed
     */
    protected $webhookValidator;

    /**
     * Stripe webhook secret.
     *
     * @var string
     */
    protected $webhookSecret = '';

    /**
     * Constructor
     * 
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct(string $apiKey, string $apiSecret)
    {
        parent::__construct([
            'api_key' => $apiKey,
            'api_secret' => $apiSecret
        ]);
        
        $this->client = new StripeClient($apiSecret);
    }
    
    /**
     * Set client (for testing)
     * 
     * @param mixed $client
     * @return void
     */
    public function setClient($client): void
    {
        $this->client = $client;
    }
    
    /**
     * Set webhook validator (for testing)
     * 
     * @param mixed $validator
     * @return void
     */
    public function setWebhookValidator($validator): void
    {
        $this->webhookValidator = $validator;
    }
    
    /**
     * Process a payment
     *
     * @param array $paymentData
     * @return array
     */
    public function processPayment(array $paymentData): array
    {
        try {
            $amount = $this->formatAmount($paymentData['amount'], $paymentData['currency']);
            
            $paymentIntentData = [
                'amount' => $amount,
                'currency' => strtolower($paymentData['currency']),
                'payment_method_types' => ['card'],
                'description' => $paymentData['description'] ?? null,
                'metadata' => $paymentData['metadata'] ?? [],
            ];
            
            // Add optional parameters
            if (!empty($paymentData['customer_id'])) {
                $paymentIntentData['customer'] = $paymentData['customer_id'];
            }
            
            if (!empty($paymentData['shipping'])) {
                $paymentIntentData['shipping'] = $paymentData['shipping'];
            }
            
            // Create a payment intent
            $paymentIntent = $this->client->paymentIntents->create($paymentIntentData);
            
            $this->logPaymentEvent('create_payment_intent', $paymentData, $paymentIntent->id);
            
            return [
                'success' => true,
                'payment_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'gateway' => $this->gateway,
            ];
        } catch (ApiErrorException $e) {
            return $this->formatErrorResponse($e);
        }
    }
    
    /**
     * Validate a webhook callback
     *
     * @param string $payload
     * @param string $signature
     * @param string $secret
     * @return object
     * @throws \UnexpectedValueException
     */
    public function validateWebhook(string $payload, string $signature, string $secret)
    {
        try {
            if ($this->webhookValidator) {
                return $this->webhookValidator->constructEvent($payload, $signature, $secret);
            }
            
            return Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Exception $e) {
            $this->logPaymentEvent('webhook_validation_failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Get payment status
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $paymentIntent = $this->client->paymentIntents->retrieve($paymentId);
            
            $this->logPaymentEvent('get_payment_status', [], $paymentId);
            
            return [
                'success' => true,
                'payment_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'gateway' => $this->gateway,
            ];
        } catch (ApiErrorException $e) {
            return $this->formatErrorResponse($e);
        }
    }
    
    /**
     * Refund a payment
     *
     * @param string $paymentId
     * @param float|null $amount
     * @param string|null $reason
     * @return array
     */
    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array
    {
        try {
            $refundData = [
                'payment_intent' => $paymentId,
            ];
            
            // If amount is provided, format and add it
            if ($amount !== null) {
                $paymentIntent = $this->client->paymentIntents->retrieve($paymentId);
                $refundData['amount'] = $this->formatAmount($amount, $paymentIntent->currency);
            }
            
            // If reason is provided, add it
            if ($reason !== null) {
                $refundData['reason'] = $reason;
            }
            
            $refund = $this->client->refunds->create($refundData);
            
            $this->logPaymentEvent('refund_payment', [
                'amount' => $refund->amount,
                'reason' => $reason
            ], $paymentId);
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'payment_id' => $paymentId,
                'amount' => $refund->amount,
                'status' => $refund->status,
                'gateway' => $this->gateway,
            ];
        } catch (ApiErrorException $e) {
            return $this->formatErrorResponse($e);
        }
    }
    
    /**
     * Get supported currencies
     * 
     * @return array
     */
    protected function getSupportedCurrencies(): array
    {
        return [
            'USD', 'EUR', 'GBP', 'AUD', 'CAD', 'JPY', 'HKD', 'SGD', 'NOK', 'DKK', 
            'SEK', 'CHF', 'NZD', 'MXN', 'BRL', 'INR', 'MYR', 'PLN', 'AED', 'BGN', 
            'CZK', 'HUF', 'ILS', 'RON', 'TRY', 'ZAR'
        ];
    }
    
    /**
     * Check if gateway supports 3D Secure
     * 
     * @return bool
     */
    protected function supports3DS(): bool
    {
        return true;
    }

    /**
     * Initialize the payment gateway with configuration.
     *
     * @param array $config
     * @return self
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->webhookSecret = $config['webhook_secret'] ?? '';
        
        // Initialize Stripe API
        Stripe::setApiKey($this->apiKey);
        
        return $this;
    }

    /**
     * Format amount for Stripe (convert to cents).
     *
     * @param float $amount
     * @return int
     */
    protected function formatAmount(float $amount)
    {
        return (int) ($amount * 100);
    }

    /**
     * Get the payment gateway name.
     *
     * @return string
     */
    public function getName()
    {
        return 'stripe';
    }
} 