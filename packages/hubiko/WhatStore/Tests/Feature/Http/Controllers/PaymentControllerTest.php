<?php

namespace Hubiko\WhatStore\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Hubiko\WhatStore\Services\Payment\PaymentGatewayFactory;
use Hubiko\WhatStore\Services\Payment\StripePaymentGateway;
use Hubiko\WhatStore\Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock authentication for the test
        $this->actingAs(factory(\App\Models\User::class)->create());
        
        // Set up workspace context
        $this->app->bind('active.workspace', function() {
            return 1; // Default test workspace
        });
    }
    
    /** @test */
    public function it_shows_payment_page()
    {
        $response = $this->get(route('whatstore.payment.show', ['order_id' => 1]));
        
        $response->assertStatus(200);
        $response->assertViewIs('whatstore::payment.show');
        $response->assertViewHas('order');
    }
    
    /** @test */
    public function it_processes_stripe_payment()
    {
        // Mock the payment gateway
        $mockStripeGateway = Mockery::mock(StripePaymentGateway::class);
        $mockStripeGateway->shouldReceive('processPayment')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'txn_' . $this->faker->uuid,
                'redirect_url' => null
            ]);
            
        // Mock the factory to return our mocked gateway
        $mockFactory = Mockery::mock(PaymentGatewayFactory::class);
        $mockFactory->shouldReceive('create')
            ->with('stripe')
            ->once()
            ->andReturn($mockStripeGateway);
            
        $this->app->instance(PaymentGatewayFactory::class, $mockFactory);
        
        // Make the request
        $response = $this->post(route('whatstore.payment.process'), [
            'order_id' => 1,
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_visa'
        ]);
        
        // Assert the response
        $response->assertStatus(302); // Redirect on success
        $response->assertRedirect(route('whatstore.payment.success', ['order_id' => 1]));
    }
    
    /** @test */
    public function it_handles_failed_payments()
    {
        // Mock the payment gateway
        $mockStripeGateway = Mockery::mock(StripePaymentGateway::class);
        $mockStripeGateway->shouldReceive('processPayment')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Card declined'
            ]);
            
        // Mock the factory to return our mocked gateway
        $mockFactory = Mockery::mock(PaymentGatewayFactory::class);
        $mockFactory->shouldReceive('create')
            ->with('stripe')
            ->once()
            ->andReturn($mockStripeGateway);
            
        $this->app->instance(PaymentGatewayFactory::class, $mockFactory);
        
        // Make the request
        $response = $this->post(route('whatstore.payment.process'), [
            'order_id' => 1,
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_chargeDeclined'
        ]);
        
        // Assert the response
        $response->assertStatus(302); // Redirect on failure
        $response->assertRedirect(route('whatstore.payment.show', ['order_id' => 1]));
        $response->assertSessionHas('error', 'Card declined');
    }
} 