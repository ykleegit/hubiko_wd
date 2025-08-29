<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('seo_websites')) {
            Schema::create('seo_websites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('domain');
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->json('target_keywords')->nullable();
            $table->json('competitors')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_search_console_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_crawled_at')->nullable();
            $table->enum('crawl_frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('created_by');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'is_active']);
            $table->index('domain');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('seo_websites');
    }
};
