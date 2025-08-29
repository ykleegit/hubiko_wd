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
        Schema::create('ai_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('user_id');
            $table->string('export_type'); // pdf, docx, txt, html
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->string('format');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('workspace_id');
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['user_id', 'workspace_id']);
            $table->foreign('content_id')->references('id')->on('ai_contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_exports');
    }
};
