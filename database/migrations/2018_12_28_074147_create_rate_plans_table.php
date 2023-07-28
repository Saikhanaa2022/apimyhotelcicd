<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('is_daily')->default(false);
            $table->unsignedInteger('room_type_id');
            $table->timestamps();

            // $table->foreign('room_type_id')
            //     ->references('id')->on('room_types')
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
        Schema::dropIfExists('rate_plans');
    }
}
