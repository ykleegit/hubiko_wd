<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('board_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_board_id');
            $table->string('title');
            $table->enum('meeting_type', ['regular', 'special', 'annual', 'extraordinary'])->default('regular');
            $table->date('meeting_date');
            $table->datetime('meeting_time');
            $table->string('location')->nullable();
            $table->string('virtual_meeting_link')->nullable();
            $table->json('agenda')->nullable();
            $table->text('minutes')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->integer('quorum_required')->default(0);
            $table->integer('quorum_present')->default(0);
            $table->unsignedBigInteger('chairman_id')->nullable();
            $table->unsignedBigInteger('secretary_id')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('company_board_id')->references('id')->on('company_boards')->onDelete('cascade');
            $table->foreign('chairman_id')->references('id')->on('directors')->onDelete('set null');
            $table->foreign('secretary_id')->references('id')->on('directors')->onDelete('set null');
            $table->index(['company_board_id', 'meeting_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('board_meetings');
    }
};
