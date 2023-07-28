<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddByPersonRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->boolean('by_person')->nullable()->default(false)->after('is_res_request');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->boolean('by_person')->nullable()->default(false)->after('is_res_request');
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
            $table->dropColumn('by_person');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->dropColumn('by_person');
        });
    }
}
