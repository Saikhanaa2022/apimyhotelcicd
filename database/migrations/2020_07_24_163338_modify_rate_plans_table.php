<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            $table->boolean('is_default')->nullable()->default(false)->after('name');
            $table->boolean('is_online_book')->nullable()->default(false)->after('is_ota');
        });

        Schema::table('rate_plan_clones', function (Blueprint $table) {
            $table->boolean('is_default')->nullable()->default(false)->after('name');
            $table->boolean('is_ota')->nullable()->default(false)->after('is_default');
            $table->boolean('is_online_book')->nullable()->default(false)->after('is_ota');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->dropColumn('is_online_book');
        });

        Schema::table('rate_plan_clones', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->dropColumn('is_ota');
            $table->dropColumn('is_online_book');
        });
    }
}
