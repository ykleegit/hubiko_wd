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
        Schema::create('ai_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('category');
            $table->text('prompt_template');
            $table->json('variables')->nullable();
            $table->string('content_type');
            $table->string('default_tone')->nullable();
            $table->string('default_length')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['workspace_id', 'is_active']);
            $table->index(['category', 'workspace_id']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_templates');
    }
};
