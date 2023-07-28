<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('service_category_clone_id');
            $table->unsignedInteger('service_clone_id');
            $table->unsignedInteger('user_clone_id');
            // Associated reservation
            $table->unsignedInteger('reservation_id');
            $table->timestamps();

            // $table->foreign('service_category_clone_id')
            //     ->references('id')->on('service_category_clones')
            //     ->onDelete('cascade');

            // $table->foreign('service_clone_id')
            //     ->references('id')->on('service_clones')
            //     ->onDelete('cascade');
            
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
        Schema::dropIfExists('items');
    }
}
