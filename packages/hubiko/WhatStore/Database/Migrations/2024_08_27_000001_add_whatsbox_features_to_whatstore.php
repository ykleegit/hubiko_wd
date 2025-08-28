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
        // WhatsApp Templates (from WhatsBox)
        if (!Schema::hasTable('whatstore_templates')) {
            Schema::create('whatstore_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('language', 10)->default('en');
                $table->enum('category', ['MARKETING', 'UTILITY', 'AUTHENTICATION'])->default('MARKETING');
                $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'DISABLED'])->default('PENDING');
                $table->text('components');
                $table->text('header_text')->nullable();
                $table->string('header_format')->nullable();
                $table->text('body_text');
                $table->text('footer_text')->nullable();
                $table->json('buttons')->nullable();
                $table->string('template_id')->nullable(); // WhatsApp Business API template ID
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // WhatsApp Campaigns (from WhatsBox)
        if (!Schema::hasTable('whatstore_campaigns')) {
            Schema::create('whatstore_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('template_id');
                $table->enum('status', ['draft', 'scheduled', 'running', 'completed', 'paused', 'cancelled'])->default('draft');
                $table->enum('target_type', ['all_customers', 'segment', 'specific_customers'])->default('all_customers');
                $table->json('target_criteria')->nullable(); // Segmentation criteria
                $table->json('variables')->nullable(); // Template variable values
                $table->json('variables_match')->nullable(); // Variable mapping
                $table->string('media_link')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->integer('total_recipients')->default(0);
                $table->integer('sent_count')->default(0);
                $table->integer('delivered_count')->default(0);
                $table->integer('read_count')->default(0);
                $table->integer('replied_count')->default(0);
                $table->boolean('is_bot')->default(false);
                $table->boolean('is_bot_active')->default(false);
                $table->enum('bot_type', ['exact_match', 'contains'])->nullable();
                $table->text('trigger')->nullable(); // Bot trigger keywords
                $table->integer('used')->default(0); // Bot usage count
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('template_id')->references('id')->on('whatstore_templates')->onDelete('cascade');
            });
        }

        // Enhanced Messages table (extending conversations)
        if (!Schema::hasTable('whatstore_messages')) {
            Schema::create('whatstore_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('campaign_id')->nullable();
                $table->string('whatsapp_message_id')->nullable();
                $table->enum('type', ['text', 'image', 'video', 'document', 'audio', 'location', 'template'])->default('text');
                $table->text('content')->nullable();
                $table->text('header_text')->nullable();
                $table->string('header_image')->nullable();
                $table->string('header_video')->nullable();
                $table->string('header_audio')->nullable();
                $table->string('header_document')->nullable();
                $table->text('header_location')->nullable();
                $table->text('footer_text')->nullable();
                $table->json('buttons')->nullable();
                $table->json('components')->nullable(); // WhatsApp Business API components
                $table->boolean('is_from_customer')->default(true);
                $table->boolean('is_campaign_message')->default(false);
                $table->boolean('is_automated')->default(false);
                $table->boolean('is_note')->default(false);
                $table->boolean('bot_has_replied')->default(false);
                $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
                $table->text('error_message')->nullable();
                $table->string('sender_name')->nullable(); // Agent name for outgoing messages
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->json('extra')->nullable(); // Additional metadata
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                
                $table->foreign('customer_id')->references('id')->on('whatstore_customers')->onDelete('cascade');
                $table->foreign('campaign_id')->references('id')->on('whatstore_campaigns')->onDelete('set null');
            });
        }

        // Bot Replies (from WhatsBox)
        if (!Schema::hasTable('whatstore_replies')) {
            Schema::create('whatstore_replies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('trigger_keywords'); // Keywords that trigger this reply
                $table->enum('match_type', ['exact', 'contains', 'starts_with', 'ends_with'])->default('contains');
                $table->text('reply_text');
                $table->text('header_text')->nullable();
                $table->text('footer_text')->nullable();
                $table->string('button1')->nullable();
                $table->string('button1_id')->nullable();
                $table->string('button2')->nullable();
                $table->string('button2_id')->nullable();
                $table->string('button3')->nullable();
                $table->string('button3_id')->nullable();
                $table->string('button_name')->nullable(); // CTA button name
                $table->string('button_url')->nullable(); // CTA button URL
                $table->boolean('is_active')->default(true);
                $table->integer('usage_count')->default(0);
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Customer Groups/Segments (from WhatsBox Contacts module)
        if (!Schema::hasTable('whatstore_customer_groups')) {
            Schema::create('whatstore_customer_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('criteria')->nullable(); // Segmentation criteria
                $table->boolean('is_dynamic')->default(false); // Auto-update based on criteria
                $table->integer('customer_count')->default(0);
                $table->unsignedBigInteger('workspace');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
            });
        }

        // Customer Group Memberships
        if (!Schema::hasTable('whatstore_customer_group_members')) {
            Schema::create('whatstore_customer_group_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('customer_id');
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();
                
                $table->foreign('group_id')->references('id')->on('whatstore_customer_groups')->onDelete('cascade');
                $table->foreign('customer_id')->references('id')->on('whatstore_customers')->onDelete('cascade');
                $table->unique(['group_id', 'customer_id']);
            });
        }

        // Add new columns to existing customers table
        if (Schema::hasTable('whatstore_customers')) {
            Schema::table('whatstore_customers', function (Blueprint $table) {
                if (!Schema::hasColumn('whatstore_customers', 'subscribed')) {
                    $table->boolean('subscribed')->default(true)->after('preferences');
                }
                if (!Schema::hasColumn('whatstore_customers', 'enabled_ai_bot')) {
                    $table->boolean('enabled_ai_bot')->default(true)->after('subscribed');
                }
                if (!Schema::hasColumn('whatstore_customers', 'has_chat')) {
                    $table->boolean('has_chat')->default(false)->after('enabled_ai_bot');
                }
                if (!Schema::hasColumn('whatstore_customers', 'is_last_message_by_customer')) {
                    $table->boolean('is_last_message_by_customer')->default(false)->after('has_chat');
                }
                if (!Schema::hasColumn('whatstore_customers', 'last_message')) {
                    $table->text('last_message')->nullable()->after('is_last_message_by_customer');
                }
                if (!Schema::hasColumn('whatstore_customers', 'last_reply_at')) {
                    $table->timestamp('last_reply_at')->nullable()->after('last_interaction');
                }
                if (!Schema::hasColumn('whatstore_customers', 'last_client_reply_at')) {
                    $table->timestamp('last_client_reply_at')->nullable()->after('last_reply_at');
                }
                if (!Schema::hasColumn('whatstore_customers', 'last_support_reply_at')) {
                    $table->timestamp('last_support_reply_at')->nullable()->after('last_client_reply_at');
                }
                if (!Schema::hasColumn('whatstore_customers', 'extra_data')) {
                    $table->json('extra_data')->nullable()->after('preferences');
                }
            });
        }

        // WhatsApp API Configuration
        if (!Schema::hasTable('whatstore_whatsapp_configs')) {
            Schema::create('whatstore_whatsapp_configs', function (Blueprint $table) {
                $table->id();
                $table->string('phone_number_id');
                $table->string('access_token');
                $table->string('webhook_verify_token');
                $table->string('business_account_id');
                $table->boolean('is_active')->default(false);
                $table->json('webhook_events')->nullable();
                $table->timestamp('last_webhook_at')->nullable();
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
        Schema::dropIfExists('whatstore_whatsapp_configs');
        Schema::dropIfExists('whatstore_customer_group_members');
        Schema::dropIfExists('whatstore_customer_groups');
        Schema::dropIfExists('whatstore_replies');
        Schema::dropIfExists('whatstore_messages');
        Schema::dropIfExists('whatstore_campaigns');
        Schema::dropIfExists('whatstore_templates');
        
        // Remove added columns from customers table
        if (Schema::hasTable('whatstore_customers')) {
            Schema::table('whatstore_customers', function (Blueprint $table) {
                $table->dropColumn([
                    'subscribed', 'enabled_ai_bot', 'has_chat', 
                    'is_last_message_by_customer', 'last_message',
                    'last_reply_at', 'last_client_reply_at', 'last_support_reply_at',
                    'extra_data'
                ]);
            });
        }
    }
};
