<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservedRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserved_room_types', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('reservation_request_id');
            $table->unsignedInteger('room_type_id');
            $table->unsignedInteger('sync_id');
            $table->string('name')->nullable();
            $table->string('short_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('amount')->default(0);
            $table->timestamps();

            $table->foreign('reservation_request_id')
                ->references('id')->on('reservation_requests')
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
        Schema::dropIfExists('reserved_room_types');
    }
}
