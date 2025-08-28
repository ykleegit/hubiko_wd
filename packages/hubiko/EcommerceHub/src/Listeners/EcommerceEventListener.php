<?php

namespace Hubiko\EcommerceHub\Listeners;

use Hubiko\EcommerceHub\Events\OrderCreated;
use Hubiko\EcommerceHub\Events\OrderPaid;
use Hubiko\EcommerceHub\Services\EcommerceIntegrationService;
use Illuminate\Events\Dispatcher;

class EcommerceEventListener
{
    protected $integrationService;

    public function __construct(EcommerceIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Handle order created event
     */
    public function handleOrderCreated(OrderCreated $event)
    {
        $order = $event->order;

        // Sync to CRM
        $this->integrationService->syncOrderToCRM($order);

        // Update inventory
        $this->integrationService->syncOrderToInventory($order);

        // Create project tasks for fulfillment
        $this->integrationService->syncOrderToProjects($order);

        // Send notifications
        $this->sendOrderNotifications($order);
    }

    /**
     * Handle order paid event
     */
    public function handleOrderPaid(OrderPaid $event)
    {
        $order = $event->order;

        // Sync to accounting
        $this->integrationService->syncOrderToAccounting($order);

        // Update CRM deal status
        $this->updateDealStatus($order);

        // Send payment confirmation
        $this->sendPaymentConfirmation($order);
    }

    /**
     * Register event listeners
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            OrderCreated::class,
            [EcommerceEventListener::class, 'handleOrderCreated']
        );

        $events->listen(
            OrderPaid::class,
            [EcommerceEventListener::class, 'handleOrderPaid']
        );
    }

    protected function sendOrderNotifications($order)
    {
        // Send email to customer
        // Send notification to store owner
        // Add to activity log
    }

    protected function updateDealStatus($order)
    {
        // Update deal to "Closed Won" status
        // Add payment information to deal notes
    }

    protected function sendPaymentConfirmation($order)
    {
        // Send payment confirmation email
        // Update customer communication history
    }
}
