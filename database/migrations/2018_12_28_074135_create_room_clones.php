<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomClones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status')->default('clean');
            $table->unsignedInteger('room_id')->nullable();
            $table->timestamps();
            
            // $table->foreign('room_id')
            //     ->references('id')->on('rooms')
            //     ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_clones');
    }
}
