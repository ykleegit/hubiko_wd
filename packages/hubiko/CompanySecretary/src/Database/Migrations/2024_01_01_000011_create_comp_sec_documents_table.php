<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompSecDocumentsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comp_sec_documents')) {
            Schema::create('comp_sec_documents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('type');
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->bigInteger('file_size')->nullable();
                $table->string('file_extension')->nullable();
                $table->unsignedBigInteger('person_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('registration_id')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->string('workspace')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('person_id')->references('id')->on('comp_sec_directors_shareholders')->onDelete('cascade');
                $table->foreign('company_id')->references('id')->on('comp_sec_companies')->onDelete('cascade');
                $table->index(['workspace', 'type']);
                $table->index(['company_id', 'is_verified']);
                $table->index('created_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comp_sec_documents');
    }
}
