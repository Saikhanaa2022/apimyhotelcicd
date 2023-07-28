<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedInteger('sync_id')->nullable()->after('id');
            $table->boolean('is_online')->default(true)->after('occupancy');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->unsignedInteger('sync_id')->nullable()->after('id');
            $table->boolean('is_online')->default(true)->after('occupancy');
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
            $table->dropColumn('sync_id');
            $table->dropColumn('is_online');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
             $table->dropColumn('sync_id');
            $table->dropColumn('is_online');
        });
    }
}
