<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescriptionBedTypeRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('floor_size');
            $table->unsignedInteger('bed_type_id')->nullable()->after('hotel_id');

            $table->foreign('bed_type_id')->references('id')->on('bed_types')
                ->onDelete('set null')
                ->onUpdate('no action');
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
            $table->dropColumn('description');
            $table->dropForeign(['bed_type_id']);
            $table->dropColumn('bed_type_id');
        });
    }
}
