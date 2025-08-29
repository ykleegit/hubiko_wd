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
        Schema::create('ai_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('content_type');
            $table->text('prompt');
            $table->longText('generated_content');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('tone');
            $table->string('length');
            $table->json('keywords')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('ai_provider')->default('openai');
            $table->string('ai_model')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->decimal('generation_time', 8, 2)->nullable();
            $table->integer('quality_score')->nullable();
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['content_type', 'workspace_id']);
            $table->index('created_by');
            $table->foreign('template_id')->references('id')->on('ai_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_contents');
    }
};
