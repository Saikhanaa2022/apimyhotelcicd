<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourceRoomTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_room_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_id');
            $table->integer('room_type_id');
            $table->integer('source_id');
            $table->integer('sale_quantity');
            $table->integer('active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('source_room_types');
    }
}
