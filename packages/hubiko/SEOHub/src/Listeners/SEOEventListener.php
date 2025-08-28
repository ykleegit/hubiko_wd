<?php

namespace Hubiko\SEOHub\Listeners;

use Hubiko\SEOHub\Events\AuditCompleted;
use Hubiko\SEOHub\Events\WebsiteCreated;
use Hubiko\SEOHub\Events\IssueDetected;
use Hubiko\SEOHub\Services\SEOIntegrationService;
use Illuminate\Events\Dispatcher;

class SEOEventListener
{
    protected $integrationService;

    public function __construct(SEOIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Handle audit completed event
     */
    public function handleAuditCompleted(AuditCompleted $event)
    {
        // Sync audit results with CRM if EcommerceHub is active
        if (module_is_active('EcommerceHub')) {
            $this->integrationService->syncAuditWithCRM($event->audit);
        }

        // Send notification to user
        $this->integrationService->sendAuditCompletedNotification($event->audit);

        // Update website next audit schedule
        $this->integrationService->scheduleNextAudit($event->audit->website);
    }

    /**
     * Handle website created event
     */
    public function handleWebsiteCreated(WebsiteCreated $event)
    {
        // Schedule initial audit
        $this->integrationService->scheduleInitialAudit($event->website);

        // Create default keyword tracking if applicable
        $this->integrationService->setupDefaultKeywords($event->website);
    }

    /**
     * Handle issue detected event
     */
    public function handleIssueDetected(IssueDetected $event)
    {
        // Send critical issue notifications
        if ($event->issue->severity === 'major') {
            $this->integrationService->sendCriticalIssueAlert($event->issue);
        }

        // Log issue for reporting
        $this->integrationService->logIssueForReporting($event->issue);
    }

    /**
     * Subscribe to events
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            AuditCompleted::class,
            [SEOEventListener::class, 'handleAuditCompleted']
        );

        $events->listen(
            WebsiteCreated::class,
            [SEOEventListener::class, 'handleWebsiteCreated']
        );

        $events->listen(
            IssueDetected::class,
            [SEOEventListener::class, 'handleIssueDetected']
        );
    }
}
