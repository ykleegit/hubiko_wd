<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('directors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_board_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('nationality')->nullable();
            $table->string('identification_number')->nullable();
            $table->enum('identification_type', ['passport', 'national_id', 'drivers_license', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('appointment_date');
            $table->date('resignation_date')->nullable();
            $table->enum('director_type', ['executive', 'non_executive', 'independent'])->default('non_executive');
            $table->boolean('is_chairman')->default(false);
            $table->boolean('is_independent')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('qualifications')->nullable();
            $table->text('experience')->nullable();
            $table->json('other_directorships')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('company_board_id')->references('id')->on('company_boards')->onDelete('cascade');
            $table->index(['company_board_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('directors');
    }
};
