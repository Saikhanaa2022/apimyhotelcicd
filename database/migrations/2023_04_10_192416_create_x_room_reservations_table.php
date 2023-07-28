<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXRoomReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xroom_reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('hotel_id');
            $table->unsignedInteger('room_type_id');
            $table->unsignedInteger('room_id');
            $table->integer('amount');
            $table->string('code', 4);
            $table->string('stay_type', 10);
            $table->string('payment_method', 10);
            $table->string('invoice_no', 10)->nullable();
            $table->json('invoice_data')->nullable();
            $table->string('client_id');
            $table->string('payment_status', 20)->default('pending');
            $table->index(['client_id']);

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
        Schema::dropIfExists('xroom_reservations');
    }
}
