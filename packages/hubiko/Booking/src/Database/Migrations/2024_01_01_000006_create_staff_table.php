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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('avatar')->nullable();
            $table->json('working_hours')->nullable(); // {Monday: {is_working: true, start: '09:00', end: '17:00'}}
            $table->json('break_times')->nullable(); // {lunch: {start: '12:00', end: '13:00'}}
            $table->unsignedBigInteger('created_by');
            $table->integer('workspace')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['workspace', 'created_by']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff');
    }
};
