<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNonRefundableRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            $table->boolean('non_ref')->default(false);
        });

        Schema::table('rate_plan_clones', function (Blueprint $table) {
            $table->boolean('non_ref')->default(false);
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
            $table->dropColumn('non_ref');
        });

        Schema::table('rate_plan_clones', function (Blueprint $table) {
            $table->dropColumn('non_ref');
        });
    }
}
