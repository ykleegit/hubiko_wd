<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Ticket';
        $menu = $event->menu;
        
        // Add main menu item
        $menu->add([
            'category' => 'Support',
            'title' => __('Tickets'),
            'icon' => 'ticket',
            'name' => 'ticket',
            'parent' => null,
            'order' => 400,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'ticket manage'
        ]);
        
        // Add submenu items
        $menu->add([
            'category' => 'Support',
            'title' => __('Tickets'),
            'icon' => '',
            'name' => 'ticket-list',
            'parent' => 'ticket',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'ticket.index',
            'module' => $module,
            'permission' => 'ticket manage'
        ]);
        
        $menu->add([
            'category' => 'Support',
            'title' => __('Categories'),
            'icon' => '',
            'name' => 'ticket-categories',
            'parent' => 'ticket',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'category.index',
            'module' => $module,
            'permission' => 'ticket category manage'
        ]);
        
        $menu->add([
            'category' => 'Support',
            'title' => __('Priorities'),
            'icon' => '',
            'name' => 'ticket-priorities',
            'parent' => 'ticket',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'priority.index',
            'module' => $module,
            'permission' => 'ticket priority manage'
        ]);
    }
} 