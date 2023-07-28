<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCommonLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('common_locations', function (Blueprint $table) {
            $table->string('name_en')->after('name');
            $table->string('description')->after('name_en');
            $table->string('longitude_latitude')->after('district_id');
            $table->string('slug')->after('district_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('common_locations', function (Blueprint $table) {
            //
        });
    }
}
