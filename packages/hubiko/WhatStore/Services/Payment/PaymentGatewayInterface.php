<?php

namespace Hubiko\WhatStore\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initialize the payment gateway with configuration.
     *
     * @param array $config
     * @return self
     */
    public function initialize(array $config);

    /**
     * Process a payment
     *
     * @param array $paymentData Payment data including amount, currency, description, etc.
     * @return array Response data with payment ID, status, etc.
     */
    public function processPayment(array $paymentData): array;
    
    /**
     * Validate a webhook callback
     *
     * @param string $payload The raw payload
     * @param string $signature The signature/header
     * @param string $secret The webhook secret
     * @return object Validated event object
     */
    public function validateWebhook(string $payload, string $signature, string $secret);
    
    /**
     * Get payment status
     *
     * @param string $paymentId
     * @return array Payment status information
     */
    public function getPaymentStatus(string $paymentId): array;
    
    /**
     * Refund a payment
     *
     * @param string $paymentId
     * @param float|null $amount Amount to refund (null for full refund)
     * @param string|null $reason Reason for refund
     * @return array Refund response
     */
    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array;
    
    /**
     * Get configuration settings for frontend integration
     *
     * @return array Configuration settings
     */
    public function getClientConfig(): array;

    /**
     * Get the payment gateway name.
     *
     * @return string
     */
    public function getName();

    /**
     * Check if the payment gateway is configured and available.
     *
     * @return bool
     */
    public function isAvailable();
} 