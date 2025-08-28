<?php

namespace Hubiko\WhatStore\Tests\Unit\Services\Webhook;

use Illuminate\Http\Request;
use Mockery;
use Hubiko\WhatStore\Services\Webhook\Handlers\StripeWebhookHandler;
use Hubiko\WhatStore\Services\Webhook\WebhookService;
use Hubiko\WhatStore\Tests\TestCase;

class WebhookServiceTest extends TestCase
{
    protected $webhookService;
    protected $mockStripeHandler;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockStripeHandler = Mockery::mock(StripeWebhookHandler::class);
        
        // Register the mock handler with the service
        $this->webhookService = new WebhookService();
        $this->app->instance(StripeWebhookHandler::class, $this->mockStripeHandler);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /** @test */
    public function it_routes_stripe_webhook_to_appropriate_handler()
    {
        // Create a mock request with Stripe event data
        $request = Request::create('/webhook/stripe', 'POST', [], [], [], [], json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => ['id' => 'pi_123456']]
        ]));
        
        // The stripe handler should be called once with the event data
        $this->mockStripeHandler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['type'] === 'payment_intent.succeeded' && 
                       $arg['data']['object']['id'] === 'pi_123456';
            }))
            ->andReturn(true);
        
        // Process the webhook
        $result = $this->webhookService->processWebhook($request, 'stripe');
        
        // Verify the result
        $this->assertTrue($result);
    }
    
    /** @test */
    public function it_handles_invalid_json_data()
    {
        // Create a request with invalid JSON
        $request = Request::create('/webhook/stripe', 'POST', [], [], [], [], 'invalid json');
        
        // Process the webhook
        $result = $this->webhookService->processWebhook($request, 'stripe');
        
        // Verify the result is false for invalid data
        $this->assertFalse($result);
    }
} 