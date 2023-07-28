<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultPriceRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedInteger('default_price')->default(0)->after('short_name');
        });
        // Room type clones
        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->unsignedInteger('default_price')->default(0)->after('short_name');
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
            $table->dropColumn('default_price');
        });
        // Room type clones
        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->dropColumn('default_price');
        });
    }
}
