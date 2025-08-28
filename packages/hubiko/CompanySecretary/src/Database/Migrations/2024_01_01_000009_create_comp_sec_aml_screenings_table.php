<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompSecAmlScreeningsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comp_sec_aml_screenings')) {
            Schema::create('comp_sec_aml_screenings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('person_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
                $table->string('reference_number')->unique();
                $table->string('screening_source')->nullable();
                $table->text('result')->nullable();
                $table->decimal('risk_score', 5, 2)->nullable();
                $table->text('notes')->nullable();
                $table->string('follow_up_action')->nullable();
                $table->timestamp('screened_at')->nullable();
                $table->unsignedBigInteger('screened_by')->nullable();
                $table->enum('auto_manual_flag', ['auto', 'manual'])->default('manual');
                $table->string('attachment_path')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->string('workspace')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('person_id')->references('id')->on('comp_sec_directors_shareholders')->onDelete('cascade');
                $table->foreign('company_id')->references('id')->on('comp_sec_companies')->onDelete('cascade');
                $table->index(['workspace', 'status']);
                $table->index(['person_id', 'status']);
                $table->index('reference_number');
                $table->index('created_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comp_sec_aml_screenings');
    }
}
