<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompSecReferralsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('comp_sec_referrals')) {
            Schema::create('comp_sec_referrals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('contact_name');
                $table->string('contact_email');
                $table->string('contact_phone')->nullable();
                $table->string('referral_type');
                $table->string('referral_code')->unique();
                $table->enum('status', ['pending', 'contacted', 'converted', 'declined'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamp('last_invited_at')->nullable();
                $table->string('workspace')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('company_id')->references('id')->on('comp_sec_companies')->onDelete('cascade');
                $table->index(['workspace', 'status']);
                $table->index(['company_id', 'referral_type']);
                $table->index('referral_code');
                $table->index('created_by');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comp_sec_referrals');
    }
}
