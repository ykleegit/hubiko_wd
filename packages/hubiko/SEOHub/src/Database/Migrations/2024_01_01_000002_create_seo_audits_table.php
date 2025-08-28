<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            $table->enum('audit_type', ['full', 'technical', 'content', 'performance', 'mobile'])->default('full');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->integer('score')->nullable();
            $table->integer('total_pages_crawled')->default(0);
            $table->integer('total_issues_found')->default(0);
            $table->integer('critical_issues')->default(0);
            $table->integer('warning_issues')->default(0);
            $table->integer('notice_issues')->default(0);
            $table->json('audit_data')->nullable();
            $table->json('recommendations')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
            $table->index(['website_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_audits');
    }
};
