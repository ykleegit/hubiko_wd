<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // WhatsApp Store Settings
        if (!Schema::hasTable('whatstore_settings')) {
            Schema::create('whatstore_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->text('value')->nullable();
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->unique(['key', 'workspace']);
            });
        }

        // WhatsApp Store Products
        if (!Schema::hasTable('whatstore_products')) {
            Schema::create('whatstore_products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sku')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 15, 2);
                $table->decimal('sale_price', 15, 2)->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->boolean('manage_stock')->default(true);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->json('images')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->decimal('weight', 8, 2)->nullable();
                $table->json('attributes')->nullable();
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
            });
        }

        // WhatsApp Store Product Categories
        if (!Schema::hasTable('whatstore_product_categories')) {
            Schema::create('whatstore_product_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('parent_id')->references('id')->on('whatstore_product_categories')->onDelete('set null');
            });
        }

        // WhatsApp Store Customers
        if (!Schema::hasTable('whatstore_customers')) {
            Schema::create('whatstore_customers', function (Blueprint $table) {
                $table->id();
                $table->string('whatsapp_number');
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('postal_code')->nullable();
                $table->json('preferences')->nullable();
                $table->timestamp('last_interaction')->nullable();
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->unique(['whatsapp_number', 'workspace']);
            });
        }

        // WhatsApp Store Orders
        if (!Schema::hasTable('whatstore_orders')) {
            Schema::create('whatstore_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->unsignedBigInteger('customer_id');
                $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
                $table->decimal('subtotal', 15, 2);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('shipping_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2);
                $table->string('currency', 3)->default('USD');
                $table->json('billing_address')->nullable();
                $table->json('shipping_address')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('customer_id')->references('id')->on('whatstore_customers')->onDelete('cascade');
            });
        }

        // WhatsApp Store Order Items
        if (!Schema::hasTable('whatstore_order_items')) {
            Schema::create('whatstore_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id');
                $table->string('product_name');
                $table->string('product_sku');
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_price', 15, 2);
                $table->json('product_attributes')->nullable();
                $table->timestamps();
                
                $table->foreign('order_id')->references('id')->on('whatstore_orders')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('whatstore_products')->onDelete('cascade');
            });
        }

        // WhatsApp Store Conversations
        if (!Schema::hasTable('whatstore_conversations')) {
            Schema::create('whatstore_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->string('whatsapp_message_id')->nullable();
                $table->enum('type', ['incoming', 'outgoing']);
                $table->text('message');
                $table->json('media')->nullable();
                $table->boolean('is_automated')->default(false);
                $table->timestamp('sent_at');
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('customer_id')->references('id')->on('whatstore_customers')->onDelete('cascade');
            });
        }

        // WhatsApp Store Payments
        if (!Schema::hasTable('whatstore_payments')) {
            Schema::create('whatstore_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('payment_method');
                $table->string('payment_gateway');
                $table->string('transaction_id')->nullable();
                $table->decimal('amount', 15, 2);
                $table->string('currency', 3);
                $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
                $table->json('gateway_response')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('order_id')->references('id')->on('whatstore_orders')->onDelete('cascade');
            });
        }

        // WhatsApp Store Webhooks
        if (!Schema::hasTable('whatstore_webhooks')) {
            Schema::create('whatstore_webhooks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url');
                $table->string('event');
                $table->boolean('is_active')->default(true);
                $table->string('secret')->nullable();
                $table->json('headers')->nullable();
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatstore_webhooks');
        Schema::dropIfExists('whatstore_payments');
        Schema::dropIfExists('whatstore_conversations');
        Schema::dropIfExists('whatstore_order_items');
        Schema::dropIfExists('whatstore_orders');
        Schema::dropIfExists('whatstore_customers');
        Schema::dropIfExists('whatstore_product_categories');
        Schema::dropIfExists('whatstore_products');
        Schema::dropIfExists('whatstore_settings');
    }
};
