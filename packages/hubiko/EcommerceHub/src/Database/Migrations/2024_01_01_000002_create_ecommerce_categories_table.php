<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ecommerce_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('ecommerce_categories')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('ecommerce_stores')->onDelete('cascade');
            $table->index(['store_id', 'is_active']);
            $table->index('slug');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ecommerce_categories');
    }
};
