<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXRoomTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xroom_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_id');
            $table->integer('room_type_id');
            $table->integer('bank_id');
            $table->string('account_name');
            $table->string('account_number');
            $table->double('amount');
            $table->integer('currency');
            $table->string('status')->default('started');
            $table->string('code')->nullable();
            $table->string('message')->nullable();
            $table->string('journal_code')->nullable();
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
        Schema::dropIfExists('x_room_transfers');
    }
}