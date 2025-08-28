<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resolutions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_board_id');
            $table->unsignedBigInteger('meeting_id')->nullable();
            $table->string('resolution_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('resolution_type', ['ordinary', 'special', 'written', 'unanimous'])->default('ordinary');
            $table->unsignedBigInteger('proposed_by')->nullable();
            $table->unsignedBigInteger('seconded_by')->nullable();
            $table->enum('voting_method', ['show_of_hands', 'poll', 'written', 'electronic'])->default('show_of_hands');
            $table->integer('votes_for')->default(0);
            $table->integer('votes_against')->default(0);
            $table->integer('votes_abstain')->default(0);
            $table->enum('status', ['draft', 'proposed', 'passed', 'rejected', 'withdrawn'])->default('draft');
            $table->date('passed_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('company_board_id')->references('id')->on('company_boards')->onDelete('cascade');
            $table->foreign('meeting_id')->references('id')->on('board_meetings')->onDelete('set null');
            $table->foreign('proposed_by')->references('id')->on('directors')->onDelete('set null');
            $table->foreign('seconded_by')->references('id')->on('directors')->onDelete('set null');
            $table->index(['company_board_id', 'status']);
            $table->index('resolution_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('resolutions');
    }
};
