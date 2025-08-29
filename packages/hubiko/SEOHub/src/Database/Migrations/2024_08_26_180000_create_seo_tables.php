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
        // SEO Websites table
        if (!Schema::hasTable('seo_websites')) {
            Schema::create('seo_websites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->string('name');
                $table->string('url');
                $table->string('host')->nullable();
                $table->text('description')->nullable();
                $table->json('settings')->nullable();
                $table->enum('status', ['active', 'inactive', 'monitoring'])->default('active');
                $table->timestamp('last_audit_at')->nullable();
                $table->timestamp('next_audit_at')->nullable();
                $table->timestamps();
                
                $table->index(['workspace_id', 'user_id']);
                $table->index('status');
                $table->index('last_audit_at');
            });
        }

        // SEO Audits table
        if (!Schema::hasTable('seo_audits')) {
            Schema::create('seo_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('website_id');
                $table->string('url');
                $table->string('title')->nullable();
                $table->text('meta_description')->nullable();
                $table->integer('score')->default(0);
                $table->integer('total_issues')->default(0);
                $table->integer('major_issues')->default(0);
                $table->integer('moderate_issues')->default(0);
                $table->integer('minor_issues')->default(0);
                $table->integer('passed_tests')->default(0);
                $table->json('audit_data')->nullable();
                $table->json('performance_metrics')->nullable();
                $table->json('seo_metrics')->nullable();
                $table->json('accessibility_metrics')->nullable();
                $table->json('best_practices')->nullable();
                $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
                $table->index(['workspace_id', 'user_id']);
                $table->index(['website_id', 'status']);
                $table->index('score');
                $table->index('completed_at');
            });
        }

        // SEO Keywords table
        if (!Schema::hasTable('seo_keywords')) {
            Schema::create('seo_keywords', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('website_id');
                $table->string('keyword');
                $table->integer('search_volume')->default(0);
                $table->decimal('difficulty', 3, 1)->default(0);
                $table->integer('current_position')->nullable();
                $table->integer('previous_position')->nullable();
                $table->string('target_url')->nullable();
                $table->enum('status', ['tracking', 'paused', 'archived'])->default('tracking');
                $table->json('ranking_history')->nullable();
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();
                
                $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
                $table->index(['workspace_id', 'user_id']);
                $table->index(['website_id', 'status']);
                $table->index('keyword');
                $table->index('current_position');
            });
        }

        // SEO Issues table
        if (!Schema::hasTable('seo_issues')) {
            Schema::create('seo_issues', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('audit_id');
                $table->string('type'); // meta, images, links, performance, etc.
                $table->enum('severity', ['major', 'moderate', 'minor'])->default('minor');
                $table->string('title');
                $table->text('description');
                $table->text('recommendation')->nullable();
                $table->string('element')->nullable(); // CSS selector or element identifier
                $table->json('details')->nullable();
                $table->enum('status', ['open', 'fixed', 'ignored'])->default('open');
                $table->timestamp('fixed_at')->nullable();
                $table->timestamps();
                
                $table->foreign('audit_id')->references('id')->on('seo_audits')->onDelete('cascade');
                $table->index(['workspace_id', 'user_id']);
                $table->index(['audit_id', 'severity']);
                $table->index(['type', 'status']);
            });
        }

        // SEO Monitoring table
        if (!Schema::hasTable('seo_monitoring')) {
            Schema::create('seo_monitoring', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('website_id');
                $table->string('metric_type'); // uptime, speed, seo_score, etc.
                $table->decimal('value', 10, 2);
                $table->json('metadata')->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();
                
                $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
                $table->index(['workspace_id', 'user_id']);
                $table->index(['website_id', 'metric_type']);
                $table->index('recorded_at');
            });
        }

        // SEO Reports table
        if (!Schema::hasTable('seo_reports')) {
            Schema::create('seo_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('website_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['audit', 'keyword', 'monitoring', 'comprehensive'])->default('audit');
                $table->enum('frequency', ['manual', 'daily', 'weekly', 'monthly'])->default('manual');
                $table->json('filters')->nullable();
                $table->json('settings')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamp('last_generated_at')->nullable();
                $table->timestamp('next_generation_at')->nullable();
                $table->timestamps();
                
                $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
                $table->index(['workspace_id', 'user_id']);
                $table->index(['type', 'status']);
                $table->index('next_generation_at');
            });
        }

        // SEO Competitors table
        if (!Schema::hasTable('seo_competitors')) {
            Schema::create('seo_competitors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('website_id');
                $table->string('name');
                $table->string('url');
                $table->string('domain');
                $table->integer('domain_authority')->nullable();
                $table->integer('page_authority')->nullable();
                $table->integer('backlinks_count')->nullable();
                $table->json('keyword_overlap')->nullable();
                $table->json('metrics')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamp('last_analyzed_at')->nullable();
                $table->timestamps();
                
                $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
                $table->index(['workspace_id', 'user_id']);
                $table->index(['website_id', 'status']);
                $table->index('domain');
            });
        }

        // SEO Settings table
        if (!Schema::hasTable('seo_settings')) {
            Schema::create('seo_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->string('key');
                $table->text('value')->nullable();
                $table->string('type')->default('string'); // string, json, boolean, integer
                $table->timestamps();
                
                $table->index(['workspace_id', 'user_id']);
                $table->unique(['workspace_id', 'user_id', 'key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
        Schema::dropIfExists('seo_competitors');
        Schema::dropIfExists('seo_reports');
        Schema::dropIfExists('seo_monitoring');
        Schema::dropIfExists('seo_issues');
        Schema::dropIfExists('seo_keywords');
        Schema::dropIfExists('seo_audits');
        Schema::dropIfExists('seo_websites');
    }
};
