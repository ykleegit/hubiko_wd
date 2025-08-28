<?php

namespace Hubiko\EcommerceHub\Services;

use Hubiko\EcommerceHub\Entities\EcommerceOrder;
use Hubiko\EcommerceHub\Entities\EcommerceCustomer;
use Hubiko\Lead\Entities\Lead;
use Hubiko\Lead\Entities\Deal;
use App\Models\User;

class EcommerceIntegrationService
{
    /**
     * Sync e-commerce order to CRM system
     */
    public function syncOrderToCRM(EcommerceOrder $order)
    {
        // Create or update customer in CRM
        $lead = $this->createOrUpdateLead($order);
        
        // Create deal from order
        $deal = $this->createDealFromOrder($order, $lead);
        
        // Update customer record with lead reference
        if ($order->customer && !$order->customer->lead_id) {
            $order->customer->update(['lead_id' => $lead->id]);
        }
        
        return ['lead' => $lead, 'deal' => $deal];
    }

    /**
     * Sync e-commerce order to accounting system
     */
    public function syncOrderToAccounting(EcommerceOrder $order)
    {
        if (!class_exists('\Hubiko\Account\Entities\BankAccount')) {
            return null; // Account module not available
        }

        $entries = [];

        // Sales Revenue Entry
        $entries[] = [
            'account' => 'Sales Revenue',
            'debit' => 0,
            'credit' => $order->total_amount,
            'description' => "E-commerce Sale - Order #{$order->order_number}"
        ];

        // Accounts Receivable (if payment pending)
        if ($order->payment_status === 'pending') {
            $entries[] = [
                'account' => 'Accounts Receivable',
                'debit' => $order->total_amount,
                'credit' => 0,
                'description' => "Pending Payment - Order #{$order->order_number}"
            ];
        }

        // Cash/Bank Account (if payment received)
        if ($order->payment_status === 'paid') {
            $entries[] = [
                'account' => 'Cash/Bank',
                'debit' => $order->total_amount,
                'credit' => 0,
                'description' => "Payment Received - Order #{$order->order_number}"
            ];
        }

        // Tax entries
        if ($order->tax_amount > 0) {
            $entries[] = [
                'account' => 'Tax Payable',
                'debit' => 0,
                'credit' => $order->tax_amount,
                'description' => "Sales Tax - Order #{$order->order_number}"
            ];
        }

        return $this->createJournalEntry($order, $entries);
    }

    /**
     * Update inventory when order is placed
     */
    public function syncOrderToInventory(EcommerceOrder $order)
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product && $product->track_stock) {
                $newStock = $product->stock_quantity - $item->quantity;
                $product->update([
                    'stock_quantity' => max(0, $newStock),
                    'status' => $newStock <= 0 ? 'out_of_stock' : $product->status
                ]);

                // Create inventory movement record
                $this->createInventoryMovement($product, $item->quantity, 'sale', $order->id);
            }
        }
    }

    /**
     * Create project tasks for order fulfillment
     */
    public function syncOrderToProjects(EcommerceOrder $order)
    {
        if (!class_exists('\Hubiko\Taskly\Entities\Project')) {
            return null; // Taskly module not available
        }

        // Create project for order fulfillment
        $project = \Hubiko\Taskly\Entities\Project::create([
            'name' => "Order Fulfillment - #{$order->order_number}",
            'description' => "Fulfillment tasks for e-commerce order #{$order->order_number}",
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'workspace' => $order->workspace_id,
            'created_by' => $order->created_by
        ]);

        // Create default tasks
        $tasks = [
            'Process Payment' => $order->payment_status === 'paid' ? 'Done' : 'To Do',
            'Prepare Items' => 'To Do',
            'Package Order' => 'To Do',
            'Ship Order' => 'To Do',
            'Confirm Delivery' => 'To Do'
        ];

        foreach ($tasks as $taskName => $status) {
            \Hubiko\Taskly\Entities\Task::create([
                'title' => $taskName,
                'project_id' => $project->id,
                'stage_id' => $this->getTaskStageId($status, $project->id),
                'priority' => 'Medium',
                'workspace' => $order->workspace_id,
                'created_by' => $order->created_by
            ]);
        }

        return $project;
    }

    protected function createOrUpdateLead(EcommerceOrder $order)
    {
        $customerEmail = $order->customer_email;
        $customerName = $order->customer_name;

        // Check if lead already exists
        $lead = Lead::where('email', $customerEmail)
                   ->where('workspace_id', $order->workspace_id)
                   ->first();

        if (!$lead) {
            $lead = Lead::create([
                'name' => $customerName,
                'email' => $customerEmail,
                'phone' => $order->customer_details['phone'] ?? null,
                'subject' => 'E-commerce Customer',
                'source_id' => $this->getEcommerceSourceId(),
                'stage_id' => $this->getCustomerStageId(),
                'workspace_id' => $order->workspace_id,
                'created_by' => $order->created_by
            ]);
        } else {
            // Update existing lead with latest info
            $lead->update([
                'name' => $customerName,
                'phone' => $order->customer_details['phone'] ?? $lead->phone,
                'stage_id' => $this->getCustomerStageId()
            ]);
        }

        return $lead;
    }

    protected function createDealFromOrder(EcommerceOrder $order, Lead $lead)
    {
        return Deal::create([
            'name' => "E-commerce Order #{$order->order_number}",
            'price' => $order->total_amount,
            'stage_id' => $this->getClosedWonStageId(),
            'sources' => $this->getEcommerceSourceId(),
            'notes' => "Automatically created from e-commerce order #{$order->order_number}",
            'workspace_id' => $order->workspace_id,
            'created_by' => $order->created_by
        ]);
    }

    protected function createJournalEntry(EcommerceOrder $order, array $entries)
    {
        // This would integrate with the Account module
        // Implementation depends on the specific accounting module structure
        return [
            'reference' => "E-commerce Order #{$order->order_number}",
            'date' => $order->created_at,
            'entries' => $entries,
            'workspace_id' => $order->workspace_id
        ];
    }

    protected function createInventoryMovement($product, $quantity, $type, $referenceId)
    {
        // Create inventory movement record
        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'type' => $type, // 'sale', 'purchase', 'adjustment'
            'reference_type' => 'ecommerce_order',
            'reference_id' => $referenceId,
            'workspace_id' => $product->workspace_id,
            'created_at' => now()
        ];
    }

    protected function getEcommerceSourceId()
    {
        // Get or create "E-commerce Store" source
        if (class_exists('\Hubiko\Lead\Entities\Source')) {
            $source = \Hubiko\Lead\Entities\Source::firstOrCreate([
                'name' => 'E-commerce Store',
                'workspace_id' => getActiveWorkSpace()
            ]);
            return $source->id;
        }
        return null;
    }

    protected function getCustomerStageId()
    {
        // Get "Customer" stage or create it
        if (class_exists('\Hubiko\Lead\Entities\LeadStage')) {
            $stage = \Hubiko\Lead\Entities\LeadStage::firstOrCreate([
                'name' => 'Customer',
                'workspace_id' => getActiveWorkSpace()
            ]);
            return $stage->id;
        }
        return null;
    }

    protected function getClosedWonStageId()
    {
        // Get "Closed Won" deal stage
        if (class_exists('\Hubiko\Lead\Entities\DealStage')) {
            $stage = \Hubiko\Lead\Entities\DealStage::firstOrCreate([
                'name' => 'Closed Won',
                'workspace_id' => getActiveWorkSpace()
            ]);
            return $stage->id;
        }
        return null;
    }

    protected function getTaskStageId($status, $projectId)
    {
        // Map status to task stage
        $stageMap = [
            'To Do' => 'To Do',
            'Done' => 'Done'
        ];

        if (class_exists('\Hubiko\Taskly\Entities\Stage')) {
            $stage = \Hubiko\Taskly\Entities\Stage::where('name', $stageMap[$status])
                                                  ->where('project_id', $projectId)
                                                  ->first();
            return $stage ? $stage->id : null;
        }
        return null;
    }
}
