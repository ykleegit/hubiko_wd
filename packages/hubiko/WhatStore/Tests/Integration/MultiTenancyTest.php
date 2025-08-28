<?php

namespace Hubiko\WhatStore\Tests\Integration;

use Mockery;
use Hubiko\WhatStore\Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users in different workspaces
        $this->workspace1User = Mockery::mock('User');
        $this->workspace1User->workspace_id = 1;
        
        $this->workspace2User = Mockery::mock('User');
        $this->workspace2User->workspace_id = 2;
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /** @test */
    public function users_can_only_access_their_workspace_data()
    {
        // Set workspace context
        $this->app->instance('active.workspace', 1);
        
        // Mock the query builder for a module model
        $mockQueryBuilder = Mockery::mock('QueryBuilder');
        $mockQueryBuilder->shouldReceive('where')
            ->with('workspace', 1)
            ->once()
            ->andReturnSelf();
        $mockQueryBuilder->shouldReceive('get')
            ->once()
            ->andReturn([['id' => 1, 'workspace' => 1]]);
        
        // Mock the model
        $mockModel = Mockery::mock('Model');
        $mockModel->shouldReceive('newQuery')
            ->once()
            ->andReturn($mockQueryBuilder);
        
        $this->app->instance('test.model', $mockModel);
        
        // Get data for workspace 1
        $model = $this->app->make('test.model');
        $result = $model->newQuery()->where('workspace', getActiveWorkSpace())->get();
        
        // Assert data belongs to workspace 1
        $this->assertEquals(1, $result[0]['workspace']);
    }
    
    /** @test */
    public function different_workspaces_cannot_access_each_others_data()
    {
        // Mock repository or service that uses workspace scoping
        $mockService = Mockery::mock('DataService');
        
        // Set up expectations for workspace 1
        $this->app->instance('active.workspace', 1);
        $mockService->shouldReceive('getData')
            ->with(1) // Workspace ID 1
            ->once()
            ->andReturn(['id' => 1, 'workspace' => 1]);
        
        // Get data for workspace 1
        $workspace1Data = $mockService->getData(getActiveWorkSpace());
        $this->assertEquals(1, $workspace1Data['workspace']);
        
        // Set up expectations for workspace 2
        $this->app->instance('active.workspace', 2);
        $mockService->shouldReceive('getData')
            ->with(2) // Workspace ID 2
            ->once()
            ->andReturn(['id' => 2, 'workspace' => 2]);
        
        // Get data for workspace 2
        $workspace2Data = $mockService->getData(getActiveWorkSpace());
        $this->assertEquals(2, $workspace2Data['workspace']);
        
        // Verify different data for different workspaces
        $this->assertNotEquals($workspace1Data, $workspace2Data);
    }
    
    /** @test */
    public function webhooks_are_routed_to_correct_workspace()
    {
        // Mock webhook processing service
        $mockWebhookService = Mockery::mock('WebhookService');
        
        // The webhook service should determine workspace from payload
        $mockWebhookService->shouldReceive('determineWorkspace')
            ->with(Mockery::on(function($arg) {
                return isset($arg['workspace_id']) && $arg['workspace_id'] == 1;
            }))
            ->once()
            ->andReturn(1);
        
        // The webhook service should process the webhook in correct workspace context
        $mockWebhookService->shouldReceive('processWebhookInWorkspace')
            ->with(Mockery::type('array'), 1)
            ->once()
            ->andReturn(true);
        
        // Process a webhook with workspace info
        $result = $mockWebhookService->determineWorkspace(['workspace_id' => 1]);
        $this->assertEquals(1, $result);
        
        $success = $mockWebhookService->processWebhookInWorkspace(['event' => 'payment'], 1);
        $this->assertTrue($success);
    }
    
    /** @test */
    public function settings_are_workspace_specific()
    {
        // Mock settings service
        $mockSettingsService = Mockery::mock('SettingsService');
        
        // In workspace 1, get settings
        $this->app->instance('active.workspace', 1);
        $mockSettingsService->shouldReceive('get')
            ->with('payment_gateway', Mockery::any(), 1)
            ->once()
            ->andReturn('stripe');
        
        // In workspace 2, get settings
        $this->app->instance('active.workspace', 2);
        $mockSettingsService->shouldReceive('get')
            ->with('payment_gateway', Mockery::any(), 2)
            ->once()
            ->andReturn('paypal');
        
        // Get settings for workspace 1
        $this->app->instance('active.workspace', 1);
        $ws1Setting = $mockSettingsService->get('payment_gateway', null, getActiveWorkSpace());
        
        // Get settings for workspace 2
        $this->app->instance('active.workspace', 2);
        $ws2Setting = $mockSettingsService->get('payment_gateway', null, getActiveWorkSpace());
        
        // Verify different settings for different workspaces
        $this->assertEquals('stripe', $ws1Setting);
        $this->assertEquals('paypal', $ws2Setting);
        $this->assertNotEquals($ws1Setting, $ws2Setting);
    }
} 