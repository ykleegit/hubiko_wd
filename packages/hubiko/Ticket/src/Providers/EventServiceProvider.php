<?php

namespace Hubiko\Ticket\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use Illuminate\Support\Facades\Event;

// Core Hubiko Events
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\DefaultData;
use App\Events\GivePermissionToRole;

// Module Listeners
use Hubiko\Ticket\Listeners\CompanyMenuListener;
use Hubiko\Ticket\Listeners\CompanySettingListener;
use Hubiko\Ticket\Listeners\DataDefault;
use Hubiko\Ticket\Listeners\GiveRoleToPermission;
use Hubiko\Ticket\Listeners\CreateTicketListener;
use Hubiko\Ticket\Listeners\UpdateTicketListener;
use Hubiko\Ticket\Listeners\DestroyTicketListener;
use Hubiko\Ticket\Listeners\TicketReplyListener;
use Hubiko\Ticket\Listeners\UpdateTicketStatusListener;

// Module Events
use Hubiko\Ticket\Events\CreateTicket;
use Hubiko\Ticket\Events\UpdateTicket;
use Hubiko\Ticket\Events\DestroyTicket;
use Hubiko\Ticket\Events\TicketReply;
use Hubiko\Ticket\Events\UpdateTicketStatus;

class EventServiceProvider extends Provider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Core Hubiko platform events
        CompanyMenuEvent::class => [
            CompanyMenuListener::class
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class
        ],
        DefaultData::class => [
            DataDefault::class
        ],
        
        // Ticket module specific events
        CreateTicket::class => [
            CreateTicketListener::class,
        ],
        UpdateTicket::class => [
            UpdateTicketListener::class,
        ],
        DestroyTicket::class => [
            DestroyTicketListener::class,
        ],
        TicketReply::class => [
            TicketReplyListener::class,
        ],
        UpdateTicketStatus::class => [
            UpdateTicketStatusListener::class,
        ],
        
        // Legacy event mappings for backward compatibility
        'App\Events\CreateTicket' => [
            'Hubiko\Ticket\Listeners\CreateTicketListener',
        ],
        'App\Events\UpdateTicket' => [
            'Hubiko\Ticket\Listeners\UpdateTicketListener',
        ],
        'App\Events\DestroyTicket' => [
            'Hubiko\Ticket\Listeners\DestroyTicketListener',
        ],
        'App\Events\TicketReply' => [
            'Hubiko\Ticket\Listeners\TicketReplyListener',
        ],
        'App\Events\UpdateTicketStatus' => [
            'Hubiko\Ticket\Listeners\UpdateTicketStatusListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
    
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
    
    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
} 