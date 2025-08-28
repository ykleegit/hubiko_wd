<?php

namespace Hubiko\WhatStore\Tests\Feature\Http\Controllers;

use Mockery;
use Hubiko\WhatStore\Services\Webhook\WebhookService;
use Hubiko\WhatStore\Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    protected $mockWebhookService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the webhook service
        $this->mockWebhookService = Mockery::mock(WebhookService::class);
        $this->app->instance(WebhookService::class, $this->mockWebhookService);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /** @test */
    public function it_handles_stripe_webhooks()
    {
        // Create webhook data
        $webhookData = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => ['id' => 'pi_123456']
            ]
        ];
        
        // The webhook service should process the webhook
        $this->mockWebhookService->shouldReceive('processWebhook')
            ->once()
            ->andReturn(true);
        
        // Make the webhook request
        $response = $this->postJson('/webhooks/stripe', $webhookData, [
            'Stripe-Signature' => 'test_signature'
        ]);
        
        // Assert the response
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
    
    /** @test */
    public function it_handles_paypal_webhooks()
    {
        // Create webhook data
        $webhookData = [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'PAYID-123456',
                'status' => 'COMPLETED'
            ]
        ];
        
        // The webhook service should process the webhook
        $this->mockWebhookService->shouldReceive('processWebhook')
            ->once()
            ->andReturn(true);
        
        // Make the webhook request
        $response = $this->postJson('/webhooks/paypal', $webhookData);
        
        // Assert the response
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
    
    /** @test */
    public function it_handles_webhook_processing_failure()
    {
        // The webhook service should return false for failed processing
        $this->mockWebhookService->shouldReceive('processWebhook')
            ->once()
            ->andReturn(false);
        
        // Make the webhook request
        $response = $this->postJson('/webhooks/stripe', ['invalid' => 'data']);
        
        // Assert the response
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
} 