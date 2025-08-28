<?php

namespace Hubiko\EcommerceHub\Services\Gateways;

interface PaymentGatewayInterface
{
    /**
     * Process a payment
     *
     * @param array $orderData
     * @param array $paymentData
     * @return array
     */
    public function processPayment($orderData, $paymentData);

    /**
     * Handle webhook from payment gateway
     *
     * @param string $payload
     * @param string $signature
     * @return array
     */
    public function handleWebhook($payload, $signature);

    /**
     * Refund a payment
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param string|null $reason
     * @return array
     */
    public function refundPayment($transactionId, $amount = null, $reason = null);
}
