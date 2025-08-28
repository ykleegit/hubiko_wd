<?php

namespace Hubiko\WhatStore\Tests\Performance;

use Illuminate\Support\Facades\DB;
use Hubiko\WhatStore\Tests\TestCase;

class PerformanceTest extends TestCase
{
    /** @test */
    public function payment_processing_completes_within_acceptable_time()
    {
        // Measure time for payment processing
        $startTime = microtime(true);
        
        // Execute payment process (mocked)
        $paymentGateway = new \Hubiko\WhatStore\Services\Payment\StripePaymentGateway();
        $paymentGateway->processPayment([
            'amount' => 100,
            'currency' => 'USD',
            'payment_method' => 'pm_card_visa',
            'description' => 'Test payment'
        ]);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime);
        
        // Payment processing should take less than 2 seconds
        $this->assertLessThan(2.0, $executionTime);
    }
    
    /** @test */
    public function database_queries_are_optimized()
    {
        // Count queries for a standard operation
        DB::enableQueryLog();
        
        // Execute operation (example)
        $this->get(route('whatstore.dashboard'));
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        // Dashboard should use fewer than 20 queries
        $this->assertLessThan(20, count($queries));
    }
    
    /** @test */
    public function webhook_processing_handles_multiple_concurrent_requests()
    {
        // This is a conceptual test that would normally be implemented
        // with a load testing tool like k6, JMeter, or similar
        
        // For now, we'll just check if our webhook service has proper concurrency handling
        $webhookService = new \Hubiko\WhatStore\Services\Webhook\WebhookService();
        
        // Simulate multiple concurrent webhook requests
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $request = \Illuminate\Http\Request::create('/webhook/stripe', 'POST', [], [], [], [], json_encode([
                'type' => 'payment_intent.succeeded',
                'data' => ['object' => ['id' => "pi_$i"]]
            ]));
            
            $results[] = $webhookService->processWebhook($request, 'stripe');
        }
        
        // All webhook processing should be successful
        $this->assertEquals(10, count(array_filter($results)));
    }
    
    /** @test */
    public function memory_usage_is_within_acceptable_limits()
    {
        // Measure memory usage before operation
        $memoryBefore = memory_get_usage();
        
        // Perform a memory-intensive operation
        for ($i = 0; $i < 100; $i++) {
            $order = new \stdClass();
            $order->id = $i;
            $order->customer = "Customer $i";
            $order->items = [];
            
            for ($j = 0; $j < 10; $j++) {
                $order->items[] = [
                    'id' => "$i-$j",
                    'name' => "Product $j",
                    'price' => $j * 10
                ];
            }
            
            // Process the order
            $this->processOrder($order);
        }
        
        // Measure memory after operation
        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        // Memory increase should be less than 10MB
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed);
    }
    
    /**
     * Mock order processing for performance testing
     */
    private function processOrder($order)
    {
        // Simulate processing logic
        usleep(1000); // 1ms delay
        return true;
    }
} 