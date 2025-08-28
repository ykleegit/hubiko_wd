<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompSecGovernmentFormsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comp_sec_government_forms')) {
            Schema::create('comp_sec_government_forms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('title');
                $table->string('form_type');
                $table->unsignedBigInteger('document_id')->nullable();
                $table->enum('status', ['draft', 'generated', 'submitted', 'approved', 'rejected'])->default('draft');
                $table->json('form_data')->nullable();
                $table->date('submission_date')->nullable();
                $table->date('approval_date')->nullable();
                $table->string('reference_number')->nullable();
                $table->string('workspace')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('company_id')->references('id')->on('comp_sec_companies')->onDelete('cascade');
                $table->index(['workspace', 'status']);
                $table->index(['company_id', 'form_type']);
                $table->index('reference_number');
                $table->index('created_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comp_sec_government_forms');
    }
}
