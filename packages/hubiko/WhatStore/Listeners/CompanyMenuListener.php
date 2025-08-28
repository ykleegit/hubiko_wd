<?php

namespace Hubiko\WhatStore\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Add WhatStore menu items to the company dashboard.
     *
     * @param CompanyMenuEvent $event
     * @return void
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'WhatStore';
        $menu = $event->menu;
        
        // Add main menu item
        $menu->add([
            'category' => 'Sales',
            'title' => __('WhatsApp Store'),
            'icon' => 'brand-whatsapp',
            'name' => 'whatstore',
            'parent' => null,
            'order' => 400,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'whatstore manage'
        ]);
        
        // Add dashboard submenu item
        $menu->add([
            'category' => 'Sales',
            'title' => __('Dashboard'),
            'icon' => '',
            'name' => 'whatstore-dashboard',
            'parent' => 'whatstore',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'whatstore.dashboard',
            'module' => $module,
            'permission' => 'whatstore manage'
        ]);
        
        // Add products submenu item
        $menu->add([
            'category' => 'Sales',
            'title' => __('Products'),
            'icon' => '',
            'name' => 'whatstore-products',
            'parent' => 'whatstore',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'whatstore.products.index',
            'module' => $module,
            'permission' => 'whatstore product show'
        ]);
        
        // Add orders submenu item
        $menu->add([
            'category' => 'Sales',
            'title' => __('Orders'),
            'icon' => '',
            'name' => 'whatstore-orders',
            'parent' => 'whatstore',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'whatstore.orders.index',
            'module' => $module,
            'permission' => 'whatstore order show'
        ]);
        
        // Add customers submenu item
        $menu->add([
            'category' => 'Sales',
            'title' => __('Customers'),
            'icon' => '',
            'name' => 'whatstore-customers',
            'parent' => 'whatstore',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'whatstore.customers.index',
            'module' => $module,
            'permission' => 'whatstore customer show'
        ]);
        
        // Add settings submenu item
        $menu->add([
            'category' => 'Sales',
            'title' => __('Settings'),
            'icon' => '',
            'name' => 'whatstore-settings',
            'parent' => 'whatstore',
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'whatstore.settings.index',
            'module' => $module,
            'permission' => 'whatstore settings manage'
        ]);
    }
} 