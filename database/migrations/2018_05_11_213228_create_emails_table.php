<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbound_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->datetime('sent');
            $table->mediumText('content');
            $table->string('subject');
            $table->string('to');
            $table->string('from');
            $table->timestamps();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        Schema::create('contact_inbound_email', function (Blueprint $table) {
            $table->unsignedInteger('inbound_email_id');
            $table->unsignedInteger('contact_id');
            $table->unsignedInteger('account_id');
            $table->timestamps();
            $table->foreign('inbound_email_id')->references('id')->on('inbound_emails')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }
}
