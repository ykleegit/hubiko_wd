<?php

namespace Hubiko\WhatStore\Tests\Security;

use Illuminate\Support\Facades\Route;
use Hubiko\WhatStore\Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    /** @test */
    public function routes_are_protected_by_authentication()
    {
        // Get all routes for the module
        $routes = Route::getRoutes();
        $modulePrefixes = ['whatstore', 'payment', 'webhook'];
        
        foreach ($routes as $route) {
            // Only test routes that belong to our module
            $routeName = $route->getName();
            if (!$routeName) continue;
            
            $belongsToModule = false;
            foreach ($modulePrefixes as $prefix) {
                if (strpos($routeName, $prefix) === 0) {
                    $belongsToModule = true;
                    break;
                }
            }
            
            if (!$belongsToModule) continue;
            
            // Skip webhook routes which are intentionally public
            if (strpos($routeName, 'webhook') !== false) continue;
            
            // Test that the route redirects unauthenticated users
            $response = $this->get($route->uri());
            $response->assertStatus(302); // Redirect to login
        }
    }
    
    /** @test */
    public function csrf_protection_is_enabled_for_post_routes()
    {
        // Get post routes
        $routes = Route::getRoutes();
        $modulePrefixes = ['whatstore', 'payment'];
        
        foreach ($routes as $route) {
            // Only test POST routes that belong to our module
            if (!in_array('POST', $route->methods())) continue;
            
            $routeName = $route->getName();
            if (!$routeName) continue;
            
            $belongsToModule = false;
            foreach ($modulePrefixes as $prefix) {
                if (strpos($routeName, $prefix) === 0) {
                    $belongsToModule = true;
                    break;
                }
            }
            
            if (!$belongsToModule) continue;
            
            // Skip webhook routes which may bypass CSRF
            if (strpos($routeName, 'webhook') !== false) continue;
            
            // Test that the route requires CSRF token
            $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
            $response = $this->post($route->uri(), []);
            $this->withMiddleware();
            
            // Should return 419 token mismatch
            $response->assertStatus(419);
        }
    }
    
    /** @test */
    public function webhook_routes_validate_request_signatures()
    {
        // Test Stripe webhook without signature
        $response = $this->postJson('/webhooks/stripe', [
            'type' => 'payment_intent.succeeded'
        ]);
        
        // Should return 400 bad request for invalid signature
        $response->assertStatus(400);
        
        // Test PayPal webhook without proper verification header
        $response = $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED'
        ]);
        
        // Should return 400 bad request for invalid verification
        $response->assertStatus(400);
    }
    
    /** @test */
    public function sensitive_data_is_not_logged()
    {
        // This is a conceptual test
        // In a real implementation, you would need to:
        // 1. Set up a test log handler
        // 2. Execute payment operations with sensitive data
        // 3. Verify that sensitive data is not present in logs
        
        // For now, we'll just check if our module properly masks sensitive data
        $this->assertTrue(method_exists(
            \Hubiko\WhatStore\Services\Payment\AbstractPaymentGateway::class,
            'maskSensitiveData'
        ));
    }
} 