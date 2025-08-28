<?php

namespace Hubiko\Ticket\Listeners;

use App\Events\CompanySettingEvent;

class CompanySettingListener
{
    public function handle(CompanySettingEvent $event): void
    {
        $event->add([
            'title' => __('Ticket Settings'),
            'navigation' => 'ticket-settings',
            'module' => 'Ticket',
            'order' => 50,
            'content' => view('ticket::settings.index')->render()
        ]);
    }
} 