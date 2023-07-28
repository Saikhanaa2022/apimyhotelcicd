<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guest_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('nationality')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('reservation_id');
            $table->unsignedInteger('guest_id')->nullable();
            $table->timestamps();
            
            // $table->foreign('reservation_id')
            //     ->references('id')->on('reservations')
            //     ->onDelete('cascade');
            
            // $table->foreign('guest_id')
            //     ->references('id')->on('guests')
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
        Schema::dropIfExists('guest_clones');
    }
}
