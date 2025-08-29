<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // E-commerce Stores
        if (!Schema::hasTable('ecommerce_stores')) {
            Schema::create('ecommerce_stores', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('domain')->nullable();
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->string('currency', 3)->default('USD');
                $table->string('timezone')->default('UTC');
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->index(['workspace_id', 'is_active']);
            });
        }

        // E-commerce Products (extends ProductService)
        if (!Schema::hasTable('ecommerce_products')) {
            Schema::create('ecommerce_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_service_id'); // Link to existing ProductService
                $table->unsignedBigInteger('store_id');
                $table->string('sku')->nullable();
                $table->decimal('price', 15, 2);
                $table->decimal('sale_price', 15, 2)->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(5);
                $table->boolean('track_stock')->default(true);
                $table->boolean('is_digital')->default(false);
                $table->decimal('weight', 8, 2)->nullable();
                $table->json('dimensions')->nullable(); // length, width, height
                $table->json('images')->nullable();
                $table->json('variants')->nullable(); // size, color, etc.
                $table->boolean('is_featured')->default(false);
                $table->integer('sort_order')->default(0);
                $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('product_service_id')->references('id')->on('product_services')->onDelete('cascade');
                $table->foreign('store_id')->references('id')->on('ecommerce_stores')->onDelete('cascade');
                $table->index(['store_id', 'status']);
                $table->index(['workspace_id', 'is_featured']);
            });
        }

        // E-commerce Categories
        if (!Schema::hasTable('ecommerce_categories')) {
            Schema::create('ecommerce_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('parent_id')->references('id')->on('ecommerce_categories')->onDelete('cascade');
                $table->foreign('store_id')->references('id')->on('ecommerce_stores')->onDelete('cascade');
                $table->index(['store_id', 'is_active']);
            });
        }

        // Product-Category pivot
        if (!Schema::hasTable('ecommerce_product_categories')) {
            Schema::create('ecommerce_product_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('category_id');
                $table->timestamps();
                
                $table->foreign('product_id')->references('id')->on('ecommerce_products')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('ecommerce_categories')->onDelete('cascade');
                $table->unique(['product_id', 'category_id']);
            });
        }

        // E-commerce Customers (extends Lead/Customer)
        if (!Schema::hasTable('ecommerce_customers')) {
            Schema::create('ecommerce_customers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable(); // Link to existing Lead
                $table->unsignedBigInteger('store_id');
                $table->string('email')->unique();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
                $table->json('addresses')->nullable(); // billing, shipping addresses
                $table->decimal('total_spent', 15, 2)->default(0);
                $table->integer('total_orders')->default(0);
                $table->timestamp('last_order_at')->nullable();
                $table->boolean('accepts_marketing')->default(false);
                $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
                $table->foreign('store_id')->references('id')->on('ecommerce_stores')->onDelete('cascade');
                $table->index(['store_id', 'status']);
                $table->index(['workspace_id', 'email']);
            });
        }

        // E-commerce Orders
        if (!Schema::hasTable('ecommerce_orders')) {
            Schema::create('ecommerce_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->boolean('is_guest')->default(false);
                $table->json('customer_details')->nullable(); // for guest orders
                $table->json('billing_address');
                $table->json('shipping_address');
                $table->decimal('subtotal', 15, 2);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('shipping_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2);
                $table->string('currency', 3);
                $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('pending');
                $table->string('payment_method')->nullable();
                $table->string('payment_reference')->nullable();
                $table->json('payment_details')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('store_id')->references('id')->on('ecommerce_stores')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('ecommerce_customers')->onDelete('set null');
                $table->index(['store_id', 'status']);
                $table->index(['workspace_id', 'order_number']);
                $table->index(['payment_status', 'created_at']);
            });
        }

        // Order Items
        if (!Schema::hasTable('ecommerce_order_items')) {
            Schema::create('ecommerce_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id');
                $table->string('product_name'); // snapshot at time of order
                $table->string('product_sku')->nullable();
                $table->json('product_variant')->nullable(); // size, color, etc.
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_price', 15, 2);
                $table->timestamps();
                
                $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('ecommerce_products')->onDelete('cascade');
                $table->index(['order_id']);
            });
        }

        // Shopping Cart
        if (!Schema::hasTable('ecommerce_cart')) {
            Schema::create('ecommerce_cart', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('session_id')->nullable(); // for guest users
                $table->unsignedBigInteger('product_id');
                $table->json('product_variant')->nullable();
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->timestamps();
                
                $table->foreign('store_id')->references('id')->on('ecommerce_stores')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('ecommerce_customers')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('ecommerce_products')->onDelete('cascade');
                $table->index(['store_id', 'customer_id']);
                $table->index(['store_id', 'session_id']);
            });
        }

        // Coupons
        if (!Schema::hasTable('ecommerce_coupons')) {
            Schema::create('ecommerce_coupons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping']);
                $table->decimal('value', 15, 2);
                $table->decimal('minimum_amount', 15, 2)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('used_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('store_id')->references('id')->on('ecommerce_stores')->onDelete('cascade');
                $table->index(['store_id', 'is_active']);
                $table->index(['code', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_coupons');
        Schema::dropIfExists('ecommerce_cart');
        Schema::dropIfExists('ecommerce_order_items');
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_customers');
        Schema::dropIfExists('ecommerce_product_categories');
        Schema::dropIfExists('ecommerce_categories');
        Schema::dropIfExists('ecommerce_products');
        Schema::dropIfExists('ecommerce_stores');
    }
};
