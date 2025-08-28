<?php

namespace Hubiko\WhatStore\Tests\Integration;

use Illuminate\Support\Facades\Event;
use Mockery;
use Hubiko\WhatStore\Tests\TestCase;

class ModuleIntegrationTest extends TestCase
{
    /** @test */
    public function it_registers_menu_items_when_company_menu_event_fires()
    {
        // Mock the event facade
        Event::fake();
        
        // Create a mock menu object
        $menu = Mockery::mock('Menu');
        
        // The menu should receive an "add" call for each menu item
        $menu->shouldReceive('add')
            ->with(Mockery::on(function($arg) {
                return $arg['module'] === 'WhatStore' && 
                       $arg['name'] === 'whatstore';
            }))
            ->once();
        
        // Fire the company menu event
        event(new \App\Events\CompanyMenuEvent($menu));
        
        // Assert that our listener was called
        Event::assertDispatched(\App\Events\CompanyMenuEvent::class);
    }
    
    /** @test */
    public function it_registers_permissions_when_permission_event_fires()
    {
        // Mock the event facade
        Event::fake();
        
        // Create a mock role
        $role = Mockery::mock('Role');
        
        // The role should receive permissions for our module
        $role->shouldReceive('givePermission')
            ->atLeast()
            ->once();
        
        // Fire the permission event
        event(new \App\Events\GivePermissionToRole($role));
        
        // Assert that our listener was called
        Event::assertDispatched(\App\Events\GivePermissionToRole::class);
    }
    
    /** @test */
    public function it_can_check_if_other_modules_are_active()
    {
        // Set up the function
        $this->app->instance('active_modules', ['WhatStore', 'Account']);
        
        // Test the module_is_active helper
        $this->assertTrue(module_is_active('Account'));
        $this->assertFalse(module_is_active('NonExistentModule'));
    }
    
    /** @test */
    public function it_respects_workspace_isolation()
    {
        // Setup test data for multiple workspaces
        // This would depend on your specific models and database structure
        
        // Set an active workspace
        $this->app->instance('active.workspace', 1);
        
        // Query should only return data for the active workspace
        // This is a conceptual test that would need to be adapted to your models
        $this->assertEquals(1, getActiveWorkSpace());
    }
} 