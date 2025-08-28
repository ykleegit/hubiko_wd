<?php

namespace Hubiko\WhatStore\Services\Payment;

use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Gateway identifier
     * 
     * @var string
     */
    protected $gateway;
    
    /**
     * Configuration parameters
     * 
     * @var array
     */
    protected $config;
    
    /**
     * Logger instance
     * 
     * @var \Illuminate\Log\Logger
     */
    protected $logger;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration parameters
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->logger = Log::channel('payment');
    }
    
    /**
     * Get the gateway identifier
     * 
     * @return string
     */
    public function getGateway(): string
    {
        return $this->gateway;
    }
    
    /**
     * Get the gateway configuration
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * Format the amount according to the gateway requirements
     * 
     * @param float $amount
     * @param string $currency
     * @return int|float
     */
    protected function formatAmount(float $amount, string $currency)
    {
        // Most payment gateways require amounts in the smallest currency unit (e.g., cents)
        $zeroDecimalCurrencies = ['JPY', 'KRW', 'VND', 'BIF', 'CLP', 'DJF', 'GNF', 'MGA', 'PYG', 'RWF', 'UGX', 'VUV', 'XAF', 'XOF', 'XPF'];
        
        if (in_array(strtoupper($currency), $zeroDecimalCurrencies)) {
            return round($amount);
        }
        
        return round($amount * 100);
    }
    
    /**
     * Log a payment event
     * 
     * @param string $action
     * @param array $data
     * @param string|null $paymentId
     * @return void
     */
    protected function logPaymentEvent(string $action, array $data, ?string $paymentId = null): void
    {
        $logData = [
            'gateway' => $this->gateway,
            'action' => $action,
            'payment_id' => $paymentId,
            'workspace_id' => getActiveWorkSpace(),
            'timestamp' => now()->toIso8601String(),
        ];
        
        // Remove sensitive data
        $safeData = $this->removeSensitiveData($data);
        
        $this->logger->info('Payment event', array_merge($logData, ['data' => $safeData]));
    }
    
    /**
     * Remove sensitive data from logs
     * 
     * @param array $data
     * @return array
     */
    protected function removeSensitiveData(array $data): array
    {
        $sensitiveKeys = [
            'password', 'secret', 'token', 'key', 'api_key', 'access_token', 
            'auth', 'authorization', 'cvv', 'cvc', 'number', 'card_number',
            'account_number', 'ssn', 'social_security'
        ];
        
        $result = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->removeSensitiveData($value);
            } else {
                $isSensitive = false;
                foreach ($sensitiveKeys as $sensitiveKey) {
                    if (stripos($key, $sensitiveKey) !== false) {
                        $isSensitive = true;
                        break;
                    }
                }
                
                $result[$key] = $isSensitive ? '***REDACTED***' : $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Format error response
     * 
     * @param \Throwable $e
     * @return array
     */
    protected function formatErrorResponse(\Throwable $e): array
    {
        $this->logger->error('Payment error', [
            'gateway' => $this->gateway,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'workspace_id' => getActiveWorkSpace(),
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'gateway' => $this->gateway,
        ];
    }
    
    /**
     * Get client-side configuration
     * 
     * @return array
     */
    public function getClientConfig(): array
    {
        return [
            'gateway' => $this->gateway,
            'supported_currencies' => $this->getSupportedCurrencies(),
            'supports_3ds' => $this->supports3DS(),
        ];
    }
    
    /**
     * Get supported currencies
     * 
     * @return array
     */
    abstract protected function getSupportedCurrencies(): array;
    
    /**
     * Check if gateway supports 3D Secure
     * 
     * @return bool
     */
    abstract protected function supports3DS(): bool;
} 