<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompSecAddressesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comp_sec_addresses')) {
            Schema::create('comp_sec_addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('person_id')->nullable();
                $table->string('type');
                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('city');
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country');
                $table->boolean('is_primary')->default(false);
                $table->string('workspace')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('company_id')->references('id')->on('comp_sec_companies')->onDelete('cascade');
                $table->foreign('person_id')->references('id')->on('comp_sec_directors_shareholders')->onDelete('cascade');
                $table->index(['workspace', 'type']);
                $table->index(['company_id', 'is_primary']);
                $table->index('created_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comp_sec_addresses');
    }
}
