<?php

namespace Hubiko\SEOHub\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Hubiko\SEOHub\Events\AuditCompleted::class => [
            \Hubiko\SEOHub\Listeners\SEOEventListener::class,
        ],
        \Hubiko\SEOHub\Events\WebsiteCreated::class => [
            \Hubiko\SEOHub\Listeners\SEOEventListener::class,
        ],
        \Hubiko\SEOHub\Events\IssueDetected::class => [
            \Hubiko\SEOHub\Listeners\SEOEventListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
