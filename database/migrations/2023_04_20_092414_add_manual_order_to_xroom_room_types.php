<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManualOrderToXroomRoomTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xroom_room_types', function (Blueprint $table) {
            //
            $table->integer('manual_order')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xroom_room_types', function (Blueprint $table) {
            //
            $table->dropColumn('manual_order');
        });
    }
}
