<?php

namespace Hubiko\WhatStore\Providers;

use App\Events\CompanyMenuEvent;
use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Hubiko\WhatStore\Listeners\CompanyMenuListener;
use Hubiko\WhatStore\Listeners\DataDefault;
use Hubiko\WhatStore\Listeners\GiveRoleToPermission;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class
        ],
        DefaultData::class => [
            DataDefault::class
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

    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
} 