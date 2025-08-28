<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('regulatory_filings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_board_id');
            $table->enum('filing_type', ['annual_return', 'financial_statements', 'change_of_directors', 'change_of_address', 'share_allotment', 'other'])->default('annual_return');
            $table->string('filing_reference')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('regulatory_body')->default('Companies House');
            $table->date('due_date');
            $table->date('filed_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'filed', 'overdue', 'rejected'])->default('pending');
            $table->decimal('filing_fee', 10, 2)->default(0);
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->json('documents')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('company_board_id')->references('id')->on('company_boards')->onDelete('cascade');
            $table->index(['company_board_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('regulatory_filings');
    }
};
