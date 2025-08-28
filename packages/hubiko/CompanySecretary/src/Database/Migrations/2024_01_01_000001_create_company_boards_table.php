<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('company_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('company_registration_number')->unique();
            $table->date('incorporation_date');
            $table->text('registered_address');
            $table->text('business_address')->nullable();
            $table->enum('company_type', ['private', 'public', 'limited', 'partnership', 'sole_proprietorship'])->default('private');
            $table->decimal('share_capital', 15, 2)->default(0);
            $table->bigInteger('authorized_shares')->default(0);
            $table->bigInteger('issued_shares')->default(0);
            $table->decimal('par_value', 10, 2)->default(0);
            $table->date('financial_year_end');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('created_by');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'is_active']);
            $table->index('company_registration_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_boards');
    }
};
