<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->increments('id');
            $table->text('notes')->nullable();
            $table->integer('amount');
            $table->unsignedInteger('user_clone_id');
            // Associated reservation
            $table->unsignedInteger('reservation_id');
            $table->timestamps();
            
            // $table->foreign('user_clone_id')
            //     ->references('id')->on('user_clones')
            //     ->onDelete('cascade');

            // $table->foreign('reservation_id')
            //     ->references('id')->on('reservations')
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
        Schema::dropIfExists('charges');
    }
}
