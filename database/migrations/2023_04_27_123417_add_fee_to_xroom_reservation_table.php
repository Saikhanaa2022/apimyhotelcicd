<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeeToXroomReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xroom_reservations', function (Blueprint $table) {
            //
            $table->float('fee', 8, 2)->default(0)->after('amount');
            $table->date('check_in')->after('room_type_id');
            $table->date('check_out')->after('check_in');
            $table->float('amount', 8, 2)->change();
            // $table->dropColumn('room_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xroom_reservations', function (Blueprint $table) {
            //
            $table->dropColumn('fee');
            $table->dropColumn('check_in');
            $table->dropColumn('check_out');
        });
    }
}
