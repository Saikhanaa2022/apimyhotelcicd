<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceTimeToRoomTypeClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->integer('price_time')->after('price_day_use');
            $table->integer('price_time_count')->after('price_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->dropColumn('price_time');
            $table->dropColumn('price_time_count');
        });
    }
}
