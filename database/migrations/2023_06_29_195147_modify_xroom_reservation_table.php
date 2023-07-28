<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyXroomReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('xroom_reservations', function (Blueprint $table) {
            $table->datetime('check_in')->change();
            $table->datetime('check_out')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('xroom_reservations', function (Blueprint $table) {
            $table->date('check_in')->change();
            $table->date('check_out')->change();
        });
    }
}
