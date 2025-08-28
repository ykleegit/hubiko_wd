<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationService
{
    /**
     * Migrate orders
     *
     * @param int $company_id
     * @param int $workspace_id
     * @return int
     */
    protected function migrateOrders($company_id, $workspace_id)
    {
        $count = 0;
        
        // Get old orders
        $oldOrders = DB::table('orders')->get();
        
        foreach ($oldOrders as $oldOrder) {
            // Find customer in new system
            $customer = Customer::where('workspace_id', $workspace_id)
                    ->where('old_customer_id', $oldOrder->customer_id)
                    ->first();
            
            if (!$customer) {
                // Skip if customer not found
                continue;
            }
            
            // Find store in new system
            $store = Store::where('workspace_id', $workspace_id)
                    ->where(function($query) use ($oldOrder) {
                        // Find by ID or name
                        $query->where('id', $oldOrder->store_id)
                            ->orWhereIn('name', DB::table('stores')
                                ->where('id', $oldOrder->store_id)
                                ->pluck('name'));
                    })
                    ->first();
            
            if (!$store) {
                // Skip if store not found
                continue;
            }
            
            // Create new order
            $newOrder = new Order([
                'order_number' => $oldOrder->order_number,
                'customer_id' => $customer->id,
                'store_id' => $store->id,
                'status' => $oldOrder->status,
                'subtotal' => $oldOrder->subtotal,
                'tax' => $oldOrder->tax,
                'discount' => $oldOrder->discount,
                'total' => $oldOrder->total,
                'shipping_cost' => $oldOrder->shipping_cost,
                'shipping_method' => $oldOrder->shipping_method,
                'payment_method' => $oldOrder->payment_method,
                'payment_status' => $oldOrder->payment_status,
                'notes' => $oldOrder->notes,
                'billing_name' => $oldOrder->billing_name,
                'billing_email' => $oldOrder->billing_email,
                'billing_phone' => $oldOrder->billing_phone,
                'billing_address' => $oldOrder->billing_address,
                'billing_city' => $oldOrder->billing_city,
                'billing_state' => $oldOrder->billing_state,
                'billing_zipcode' => $oldOrder->billing_zipcode,
                'billing_country' => $oldOrder->billing_country,
                'shipping_name' => $oldOrder->shipping_name,
                'shipping_email' => $oldOrder->shipping_email,
                'shipping_phone' => $oldOrder->shipping_phone,
                'shipping_address' => $oldOrder->shipping_address,
                'shipping_city' => $oldOrder->shipping_city,
                'shipping_state' => $oldOrder->shipping_state,
                'shipping_zipcode' => $oldOrder->shipping_zipcode,
                'shipping_country' => $oldOrder->shipping_country,
                'company_id' => $company_id,
                'workspace_id' => $workspace_id,
                'created_by' => $oldOrder->created_by,
                'old_order_id' => $oldOrder->id, // Keep track of old ID for reference
                'created_at' => $oldOrder->created_at,
                'updated_at' => $oldOrder->updated_at,
            ]);
            
            $newOrder->save();
            
            // Get order items
            $oldOrderItems = DB::table('order_items')->where('order_id', $oldOrder->id)->get();
            
            foreach ($oldOrderItems as $oldItem) {
                // Find product in new system
                $product = Product::where('workspace_id', $workspace_id)
                        ->where('old_product_id', $oldItem->product_id)
                        ->first();
                
                if (!$product) {
                    // Skip if product not found
                    continue;
                }
                
                // Find variant if applicable
                $variant = null;
                if (!empty($oldItem->variant_id)) {
                    $variant = ProductVariant::where('workspace_id', $workspace_id)
                            ->where('old_variant_id', $oldItem->variant_id)
                            ->first();
                }
                
                // Create new order item
                $newItem = new OrderItem([
                    'order_id' => $newOrder->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'quantity' => $oldItem->quantity,
                    'price' => $oldItem->price,
                    'total' => $oldItem->total,
                    'company_id' => $company_id,
                    'workspace_id' => $workspace_id,
                    'old_item_id' => $oldItem->id, // Keep track of old ID for reference
                    'created_at' => $oldItem->created_at,
                    'updated_at' => $oldItem->updated_at,
                ]);
                
                $newItem->save();
            }
            
            // Get order transactions
            if (Schema::hasTable('order_transactions')) {
                $oldTransactions = DB::table('order_transactions')->where('order_id', $oldOrder->id)->get();
                
                foreach ($oldTransactions as $oldTransaction) {
                    // Create new transaction
                    $newTransaction = new OrderTransaction([
                        'order_id' => $newOrder->id,
                        'transaction_id' => $oldTransaction->transaction_id,
                        'payment_method' => $oldTransaction->payment_method,
                        'amount' => $oldTransaction->amount,
                        'status' => $oldTransaction->status,
                        'metadata' => $oldTransaction->metadata,
                        'company_id' => $company_id,
                        'workspace_id' => $workspace_id,
                        'created_at' => $oldTransaction->created_at,
                        'updated_at' => $oldTransaction->updated_at,
                    ]);
                    
                    $newTransaction->save();
                }
            }
            
            $count++;
        }
        
        return $count;
    }
} 