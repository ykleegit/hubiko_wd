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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('resource_id')->nullable()->constrained()->onDelete('set null');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->integer('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('client_notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->integer('workspace')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['workspace', 'created_by']);
            $table->index('status');
            $table->index('payment_status');
            $table->index(['start_time', 'end_time']);
            $table->index(['staff_id', 'start_time']);
            $table->index(['resource_id', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
