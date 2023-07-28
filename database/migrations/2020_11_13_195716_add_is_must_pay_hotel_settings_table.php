<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsMustPayHotelSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->boolean('has_night_audit')->nullable()->default(false)->after('hotel_id');
            $table->boolean('is_must_pay')->nullable()->default(false)->after('has_night_audit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->dropColumn('has_night_audit');
            $table->dropColumn('is_must_pay');
        });
    }
}
