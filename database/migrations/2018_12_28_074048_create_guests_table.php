<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('nationality')->nullable();
            $table->unsignedInteger('hotel_id');
            $table->timestamps();

            // $table->foreign('hotel_id')
            //     ->references('id')->on('hotels')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guests');
    }
}
