<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSaleQuantityToXroomRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xroom_room_types', function (Blueprint $table) {
            $table->integer('sale_quantity')->after('room_type_id');
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
            $table->dropColumn('sale_quantity');
        });
    }
}
