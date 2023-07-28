<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MealRatePlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_rate_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('meal_id');
            $table->unsignedInteger('rate_plan_id');

            $table->foreign('meal_id')
                ->references('id')->on('meals')
                ->onDelete('cascade');
                
            $table->foreign('rate_plan_id')
                ->references('id')->on('rate_plans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal_rate_plan');
    }
}
