<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyChannelHotelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channel_hotel', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('hotel_id');
            $table->integer('property_id')->nullable()->after('hotel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channel_hotel', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('property_id');
        });
    }
}
