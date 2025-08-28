<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meeting_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->unsignedBigInteger('director_id');
            $table->enum('attendance_status', ['present', 'absent', 'proxy'])->default('present');
            $table->enum('attendance_type', ['physical', 'virtual', 'proxy'])->default('physical');
            $table->datetime('check_in_time')->nullable();
            $table->datetime('check_out_time')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('proxy_for')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('board_meetings')->onDelete('cascade');
            $table->foreign('director_id')->references('id')->on('directors')->onDelete('cascade');
            $table->foreign('proxy_for')->references('id')->on('directors')->onDelete('set null');
            $table->index(['meeting_id', 'director_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_attendances');
    }
};
