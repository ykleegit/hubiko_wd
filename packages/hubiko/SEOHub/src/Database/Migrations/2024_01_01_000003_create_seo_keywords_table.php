<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            $table->string('keyword');
            $table->integer('search_volume')->nullable();
            $table->integer('difficulty')->nullable();
            $table->integer('current_position')->nullable();
            $table->integer('target_position')->nullable();
            $table->string('url')->nullable();
            $table->enum('competition', ['low', 'medium', 'high'])->nullable();
            $table->decimal('cpc', 8, 2)->nullable();
            $table->json('trend_data')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->enum('status', ['tracking', 'paused', 'archived'])->default('tracking');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('website_id')->references('id')->on('seo_websites')->onDelete('cascade');
            $table->index(['website_id', 'status']);
            $table->index('keyword');
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_keywords');
    }
};
