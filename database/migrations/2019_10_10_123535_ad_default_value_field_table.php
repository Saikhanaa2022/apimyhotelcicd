<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdDefaultValueFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('day_rates', function (Blueprint $table) {
            $table->unsignedInteger('default_value')->default(0)->nullable()->after('value');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('day_rates', function (Blueprint $table) {
            $table->dropColumn('default_value');
        });
    }
}
