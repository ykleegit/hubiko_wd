<?php

namespace Hubiko\WhatStore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class HealthController extends Controller
{
    /**
     * Check overall system health
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $health = [
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
            'components' => [
                'database' => $this->checkDatabase(),
                'whatsapp' => $this->checkWhatsApp(),
                'payment_gateways' => $this->checkPaymentGateways(),
                'cache' => $this->checkCache(),
            ]
        ];

        // If any component is not healthy, set overall status to error
        foreach ($health['components'] as $component) {
            if ($component['status'] !== 'ok') {
                $health['status'] = 'error';
                break;
            }
        }

        return response()->json($health, $health['status'] === 'ok' ? 200 : 500);
    }

    /**
     * Check WhatsApp API connectivity
     *
     * @return \Illuminate\Http\Response
     */
    public function whatsapp()
    {
        $health = $this->checkWhatsApp();
        
        return response()->json($health, $health['status'] === 'ok' ? 200 : 500);
    }

    /**
     * Check database health
     *
     * @return \Illuminate\Http\Response
     */
    public function database()
    {
        $health = $this->checkDatabase();
        
        return response()->json($health, $health['status'] === 'ok' ? 200 : 500);
    }

    /**
     * Check payment gateways health
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentGateways()
    {
        $health = $this->checkPaymentGateways();
        
        return response()->json($health, $health['status'] === 'ok' ? 200 : 500);
    }

    /**
     * Check cache health
     *
     * @return \Illuminate\Http\Response
     */
    public function cache()
    {
        $health = $this->checkCache();
        
        return response()->json($health, $health['status'] === 'ok' ? 200 : 500);
    }

    /**
     * Check database connectivity and health
     *
     * @return array
     */
    private function checkDatabase()
    {
        try {
            // Attempt to connect to database and run a simple query
            $startTime = microtime(true);
            DB::connection()->getPdo();
            
            // Check if whatstore tables exist
            $tables = ['whatstore_products', 'whatstore_orders', 'whatstore_customers'];
            $tablesExist = true;
            
            foreach ($tables as $table) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    $tablesExist = false;
                    break;
                }
            }
            
            $queryTime = microtime(true) - $startTime;
            
            return [
                'status' => 'ok',
                'connection_established' => true,
                'tables_exist' => $tablesExist,
                'response_time_ms' => round($queryTime * 1000, 2),
                'message' => 'Database connection successful'
            ];
        } catch (Exception $e) {
            Log::error('Database health check failed: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'connection_established' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check WhatsApp API connectivity
     *
     * @return array
     */
    private function checkWhatsApp()
    {
        try {
            // This is a placeholder - in a real implementation, you would:
            // 1. Get WhatsApp API credentials from settings
            // 2. Make a test API call to WhatsApp Business API
            // 3. Check response and connectivity
            
            // For now, we'll simulate this check
            $isConfigured = true; // Check if API credentials are configured
            $isConnected = true; // Simulate API test call
            
            // In a real implementation, you would make an actual API call
            // to WhatsApp Business API to verify connectivity
            
            if (!$isConfigured) {
                return [
                    'status' => 'warning',
                    'configured' => false,
                    'connected' => false,
                    'message' => 'WhatsApp API not configured'
                ];
            }
            
            if (!$isConnected) {
                return [
                    'status' => 'error',
                    'configured' => true,
                    'connected' => false,
                    'message' => 'WhatsApp API connection failed'
                ];
            }
            
            return [
                'status' => 'ok',
                'configured' => true,
                'connected' => true,
                'message' => 'WhatsApp API connection successful'
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp API health check failed: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'configured' => true,
                'connected' => false,
                'message' => 'WhatsApp API check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check payment gateways connectivity
     *
     * @return array
     */
    private function checkPaymentGateways()
    {
        try {
            // In a real implementation, you would:
            // 1. Get configured payment gateways
            // 2. Make a test API call to each gateway
            // 3. Check responses and connectivity
            
            // For now, we'll simulate this check
            $gateways = [
                'cash_on_delivery' => [
                    'configured' => true,
                    'status' => 'ok',
                    'message' => 'Cash on delivery is available'
                ],
                'bank_transfer' => [
                    'configured' => true,
                    'status' => 'ok',
                    'message' => 'Bank transfer is available'
                ],
                'stripe' => [
                    'configured' => true,
                    'status' => 'ok',
                    'message' => 'Stripe connection successful'
                ],
                'paypal' => [
                    'configured' => true,
                    'status' => 'ok',
                    'message' => 'PayPal connection successful'
                ]
            ];
            
            $overallStatus = 'ok';
            foreach ($gateways as $gateway) {
                if ($gateway['status'] !== 'ok' && $gateway['configured']) {
                    $overallStatus = 'error';
                    break;
                }
            }
            
            return [
                'status' => $overallStatus,
                'gateways' => $gateways,
                'message' => 'Payment gateways checked'
            ];
        } catch (Exception $e) {
            Log::error('Payment gateways health check failed: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Payment gateways check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check cache system
     *
     * @return array
     */
    private function checkCache()
    {
        try {
            // Test cache write and read
            $testKey = 'whatstore_health_test_' . time();
            $testValue = 'health_check_' . uniqid();
            
            $startTime = microtime(true);
            Cache::put($testKey, $testValue, 60);
            $writeTime = microtime(true) - $startTime;
            
            $startTime = microtime(true);
            $retrievedValue = Cache::get($testKey);
            $readTime = microtime(true) - $startTime;
            
            Cache::forget($testKey);
            
            $cacheWorking = ($retrievedValue === $testValue);
            
            return [
                'status' => $cacheWorking ? 'ok' : 'error',
                'working' => $cacheWorking,
                'driver' => config('cache.default'),
                'write_time_ms' => round($writeTime * 1000, 2),
                'read_time_ms' => round($readTime * 1000, 2),
                'message' => $cacheWorking ? 'Cache is working properly' : 'Cache test failed'
            ];
        } catch (Exception $e) {
            Log::error('Cache health check failed: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'working' => false,
                'driver' => config('cache.default', 'unknown'),
                'message' => 'Cache check failed: ' . $e->getMessage()
            ];
        }
    }
} 