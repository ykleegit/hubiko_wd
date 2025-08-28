<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompSecDirectorsShareholdersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comp_sec_directors_shareholders')) {
            Schema::create('comp_sec_directors_shareholders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('registration_id')->nullable();
                $table->enum('type', ['director', 'shareholder', 'both']);
                $table->string('name');
                $table->string('id_number')->nullable();
                $table->string('nationality')->nullable();
                $table->text('address')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->date('appointment_date')->nullable();
                $table->date('resignation_date')->nullable();
                $table->bigInteger('shares')->nullable();
                $table->decimal('percentage', 5, 2)->nullable();
                $table->string('position')->nullable();
                $table->string('status')->default('active');
                $table->string('workspace')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('company_id')->references('id')->on('comp_sec_companies')->onDelete('cascade');
                $table->index(['workspace', 'type']);
                $table->index(['company_id', 'status']);
                $table->index('created_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comp_sec_directors_shareholders');
    }
}
