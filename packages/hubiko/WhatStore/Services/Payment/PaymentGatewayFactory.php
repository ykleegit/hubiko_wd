<?php

namespace Hubiko\WhatStore\Services\Payment;

use InvalidArgumentException;
use Illuminate\Support\Facades\Config;

class PaymentGatewayFactory
{
    /**
     * Available gateways
     * 
     * @var array
     */
    protected $gateways = [
        'stripe' => StripePaymentGateway::class,
        'paypal' => PaypalPaymentGateway::class,
    ];
    
    /**
     * Create a new payment gateway instance
     * 
     * @param string $gateway
     * @return \Hubiko\WhatStore\Services\Payment\PaymentGatewayInterface
     * @throws \InvalidArgumentException
     */
    public function make(string $gateway): PaymentGatewayInterface
    {
        if (!array_key_exists($gateway, $this->gateways)) {
            throw new InvalidArgumentException("Payment gateway '{$gateway}' is not supported.");
        }
        
        $gatewayClass = $this->gateways[$gateway];
        
        switch ($gateway) {
            case 'stripe':
                return new $gatewayClass(
                    Config::get('whatstore.payment.stripe.key', ''),
                    Config::get('whatstore.payment.stripe.secret', '')
                );
                
            case 'paypal':
                return new $gatewayClass(
                    Config::get('whatstore.payment.paypal.client_id', ''),
                    Config::get('whatstore.payment.paypal.client_secret', '')
                );
                
            default:
                throw new InvalidArgumentException("Payment gateway '{$gateway}' is not configured.");
        }
    }
    
    /**
     * Get available gateways
     * 
     * @return array
     */
    public function getAvailableGateways(): array
    {
        $available = [];
        
        if (!empty(Config::get('whatstore.payment.stripe.key')) && 
            !empty(Config::get('whatstore.payment.stripe.secret'))) {
            $available[] = 'stripe';
        }
        
        if (!empty(Config::get('whatstore.payment.paypal.client_id')) && 
            !empty(Config::get('whatstore.payment.paypal.client_secret'))) {
            $available[] = 'paypal';
        }
        
        return $available;
    }
    
    /**
     * Register a new gateway
     * 
     * @param string $name
     * @param string $class
     * @return self
     */
    public function register(string $name, string $class): self
    {
        $this->gateways[$name] = $class;
        
        return $this;
    }
} 