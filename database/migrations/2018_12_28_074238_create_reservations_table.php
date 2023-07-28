<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number')->unique();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('number_of_guests');
            $table->unsignedInteger('amount')->default(0);
            $table->time('arrival_time')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('hotel_id');
            $table->unsignedInteger('user_clone_id');
            $table->unsignedInteger('source_clone_id');
            $table->unsignedInteger('partner_clone_id')->nullable();
            $table->unsignedInteger('rate_plan_clone_id')->nullable();
            $table->unsignedInteger('room_type_clone_id');
            $table->unsignedInteger('room_clone_id')->nullable();
            $table->unsignedInteger('group_id');
            $table->timestamps();
            
            // $table->foreign('hotel_id')
            //     ->references('id')->on('hotels')
            //     ->onDelete('cascade');
            
            // $table->foreign('group_id')
            //     ->references('id')->on('groups')
            //     ->onDelete('cascade');
            
            // $table->foreign('user_clone_id')
            //     ->references('id')->on('user_clones')
            //     ->onDelete('cascade');
            
            // $table->foreign('source_clone_id')
            //     ->references('id')->on('source_clones')
            //     ->onDelete('cascade');
            
            // $table->foreign('partner_clone_id')
            //     ->references('id')->on('partner_clones')
            //     ->onDelete('set null');
            
            // $table->foreign('rate_plan_clone_id')
            //     ->references('id')->on('rate_plan_clones')
            //     ->onDelete('set null');
            
            // $table->foreign('room_type_clone_id')
            //     ->references('id')->on('room_type_clones')
            //     ->onDelete('cascade');
            
            // $table->foreign('room_clone_id')
            //     ->references('id')->on('room_clones')
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
        Schema::dropIfExists('reservations');
    }
}
