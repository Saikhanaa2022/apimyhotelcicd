<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddByPersonReservedRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserved_room_types', function (Blueprint $table) {
            $table->boolean('by_person')->nullable()->default(false)->after('quantity');
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
            $table->dropColumn('by_person');
        });
    }
}
