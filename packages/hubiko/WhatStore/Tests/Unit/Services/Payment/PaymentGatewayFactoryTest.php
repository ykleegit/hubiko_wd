<?php

namespace Hubiko\WhatStore\Tests\Unit\Services\Payment;

use PHPUnit\Framework\TestCase;
use Hubiko\WhatStore\Services\Payment\PaymentGatewayFactory;
use Hubiko\WhatStore\Services\Payment\PaypalPaymentGateway;
use Hubiko\WhatStore\Services\Payment\StripePaymentGateway;

class PaymentGatewayFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_stripe_payment_gateway()
    {
        $factory = new PaymentGatewayFactory();
        $gateway = $factory->create('stripe');
        
        $this->assertInstanceOf(StripePaymentGateway::class, $gateway);
    }
    
    /** @test */
    public function it_creates_paypal_payment_gateway()
    {
        $factory = new PaymentGatewayFactory();
        $gateway = $factory->create('paypal');
        
        $this->assertInstanceOf(PaypalPaymentGateway::class, $gateway);
    }
    
    /** @test */
    public function it_throws_exception_for_unknown_gateway()
    {
        $factory = new PaymentGatewayFactory();
        
        $this->expectException(\InvalidArgumentException::class);
        $factory->create('unknown');
    }
} 