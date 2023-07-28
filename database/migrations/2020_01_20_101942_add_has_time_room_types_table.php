<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasTimeRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->integer('has_time')->nullable()->default(true)->after('occupancy');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->integer('has_time')->nullable()->default(true)->after('occupancy');
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
            $table->dropColumn('has_time');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->dropColumn('has_time');
        });
    }
}
