<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('ic_name')->nullable()->after('name');
        });

        Schema::table('room_clones', function (Blueprint $table) {
            $table->string('ic_name')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('ic_name');
        });

        Schema::table('room_clones', function (Blueprint $table) {
            $table->dropColumn('ic_name');
        });
    }
}
