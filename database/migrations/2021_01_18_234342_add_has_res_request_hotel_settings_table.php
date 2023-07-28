<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasResRequestHotelSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->boolean('has_res_request')->nullable()->after('night_audit_time')->default(false);
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
            $table->dropColumn('has_res_request');
        });
    }
}
