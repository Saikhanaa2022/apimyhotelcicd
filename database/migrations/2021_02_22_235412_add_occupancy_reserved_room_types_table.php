<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOccupancyReservedRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserved_room_types', function (Blueprint $table) {
            $table->integer('occupancy')->nullable()->default(0)->after('short_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserved_room_types', function (Blueprint $table) {
            $table->dropColumn('occupancy');
        });
    }
}
