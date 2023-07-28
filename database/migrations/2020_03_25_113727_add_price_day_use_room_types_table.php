<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceDayUseRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->integer('price_day_use')->default(0)->after('default_price');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->integer('price_day_use')->default(0)->after('default_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('price_day_use');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->dropColumn('price_day_use');
        });
    }
}
