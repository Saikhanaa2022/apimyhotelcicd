<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsNightauditAutoHotelSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_settings', function (Blueprint $table) {
            $table->renameColumn('has_night_audit', 'is_nightaudit_auto');
            $table->string('night_audit_time')->after('has_night_audit')->default('00:00');
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
            $table->renameColumn('is_nightaudit_auto', 'has_night_audit');
            $table->dropColumn('night_audit_time');
        });
    }
}
