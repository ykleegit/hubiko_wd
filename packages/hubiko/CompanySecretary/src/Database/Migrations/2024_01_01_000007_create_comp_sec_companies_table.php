<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompSecCompaniesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comp_sec_companies')) {
            Schema::create('comp_sec_companies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('registration_id')->nullable();
                $table->string('company_name_en');
                $table->string('company_name_zh')->nullable();
                $table->string('business_registration_number')->nullable();
                $table->string('incorporation_number')->nullable();
                $table->date('incorporation_date')->nullable();
                $table->date('business_registration_expiry')->nullable();
                $table->string('company_type')->nullable();
                $table->text('business_nature')->nullable();
                $table->date('annual_return_date')->nullable();
                $table->bigInteger('total_shares')->nullable();
                $table->json('share_classes')->nullable();
                $table->text('business_address')->nullable();
                $table->text('registered_address')->nullable();
                $table->string('status')->default('active');
                $table->string('workspace')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['workspace', 'status']);
                $table->index('business_registration_number');
                $table->index('incorporation_number');
                $table->index('created_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comp_sec_companies');
    }
}
