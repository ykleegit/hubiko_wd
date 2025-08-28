<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seo_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            $table->unsignedBigInteger('audit_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->enum('issue_type', ['technical', 'content', 'meta', 'performance', 'mobile', 'accessibility'])->default('technical');
            $table->enum('severity', ['critical', 'warning', 'notice'])->default('warning');
            $table->string('category')->nullable();
            $table->text('url')->nullable();
            $table->string('element')->nullable();
            $table->text('recommendation')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'ignored'])->default('open');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
            $table->foreign('audit_id')->references('id')->on('seo_audits')->onDelete('set null');
            $table->index(['website_id', 'status']);
            $table->index(['severity', 'priority']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_issues');
    }
};
