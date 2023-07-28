<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelHotelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_hotel', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('channel_id');
            $table->unsignedInteger('hotel_id');
            $table->timestamps();

            $table->foreign('channel_id')
                ->references('id')->on('channels')
                ->onDelete('cascade');
                
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
        Schema::dropIfExists('channel_hotel');
    }
}
