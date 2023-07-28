<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('hotel_id');
            $table->string('bcc_emails')->nullable();
            $table->text('email_header')->nullable();
            $table->text('email_body')->nullable();
            $table->text('email_footer')->nullable();
            $table->text('email_contact')->nullable();
            $table->string('email_attachments')->nullable();
            $table->timestamps();

            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotel_settings');
    }
}
