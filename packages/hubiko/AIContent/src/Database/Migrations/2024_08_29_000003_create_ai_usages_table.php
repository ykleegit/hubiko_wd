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
        Schema::create('ai_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('action_type'); // generate, regenerate, edit
            $table->integer('tokens_consumed')->default(0);
            $table->decimal('cost', 10, 4)->default(0);
            $table->decimal('response_time', 8, 2)->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('workspace_id');
            $table->timestamps();

            $table->index(['workspace_id', 'success']);
            $table->index(['user_id', 'workspace_id']);
            $table->index('action_type');
            $table->foreign('content_id')->references('id')->on('ai_contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usages');
    }
};
