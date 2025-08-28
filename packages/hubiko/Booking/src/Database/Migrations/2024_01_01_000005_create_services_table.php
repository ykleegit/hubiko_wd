<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('service_categories')->onDelete('cascade');
            $table->integer('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->integer('buffer_time_minutes')->default(0);
            $table->integer('max_advance_booking_days')->default(30);
            $table->integer('min_advance_booking_hours')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_staff')->default(true);
            $table->boolean('requires_resource')->default(false);
            $table->string('image')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->integer('workspace')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['workspace', 'created_by']);
            $table->index('is_active');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
};
