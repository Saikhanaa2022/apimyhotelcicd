<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalFieldsHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('image')->nullable()->after('address');
            $table->string('check_in_time')->nullable()->after('is_active');
            $table->string('check_out_time')->nullable()->after('check_in_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('check_in_time');
            $table->dropColumn('check_out_time');
        });
    }
}
