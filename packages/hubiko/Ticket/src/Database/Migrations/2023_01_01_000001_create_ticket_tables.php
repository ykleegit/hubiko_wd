<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketTables extends Migration
{
    public function up()
    {
        // Create categories table
        if(!Schema::hasTable('categories'))
        {
            Schema::create('categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('color')->default('#6571FF');
                $table->unsignedBigInteger('parent')->default(0);
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('workspace');
                $table->timestamps();
            });
        }
        
        // Create priorities table
        if(!Schema::hasTable('priorities'))
        {
            Schema::create('priorities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('color')->default('#6571FF');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('workspace');
                $table->timestamps();
            });
        }
        
        // Create tickets table
        if(!Schema::hasTable('tickets'))
        {
            Schema::create('tickets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('ticket_id');
                $table->string('name');
                $table->string('email');
                $table->string('mobile_no')->nullable();
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('priority');
                $table->string('subject');
                $table->string('status');
                $table->text('description');
                $table->unsignedBigInteger('is_assign')->nullable();
                $table->string('type')->default('Assigned');
                $table->timestamp('reslove_at')->nullable();
                $table->text('note')->nullable();
                $table->longText('attachments')->nullable();
                $table->string('tags_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('workspace');
                $table->timestamps();
            });
        }
        
        // Create conversions table
        if(!Schema::hasTable('conversions'))
        {
            Schema::create('conversions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('ticket_id');
                $table->text('description');
                $table->longText('attachments')->nullable();
                $table->string('sender');
                $table->boolean('is_read')->default(0);
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('workspace');
                $table->timestamps();
            });
        }
        
        // Create custom fields table
        if(!Schema::hasTable('custom_fields'))
        {
            Schema::create('custom_fields', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('type');
                $table->string('module');
                $table->text('field_value')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('workspace');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('conversions');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('priorities');
        Schema::dropIfExists('categories');
    }
} 