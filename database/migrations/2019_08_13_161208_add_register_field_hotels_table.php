<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegisterFieldHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('register_no')->nullable()->after('id');
            $table->boolean('is_vatpayer')->default(false)->after('name');
            $table->boolean('is_citypayer')->default(false)->after('name');
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
            $table->dropColumn('register_no');
            $table->dropColumn('is_vatpayer');
            $table->dropColumn('is_citypayer');
        });
    }
}
