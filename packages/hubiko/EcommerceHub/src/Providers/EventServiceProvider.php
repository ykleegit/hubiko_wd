<?php

namespace Hubiko\EcommerceHub\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Hubiko\EcommerceHub\Events\OrderCreated;
use Hubiko\EcommerceHub\Events\OrderPaid;
use Hubiko\EcommerceHub\Listeners\EcommerceEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderCreated::class => [
            [EcommerceEventListener::class, 'handleOrderCreated'],
        ],
        OrderPaid::class => [
            [EcommerceEventListener::class, 'handleOrderPaid'],
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
}
