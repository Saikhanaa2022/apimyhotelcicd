<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->unsignedInteger('value');
            $table->unsignedInteger('min_los')->nullable();
            $table->unsignedInteger('max_los')->nullable();
            $table->unsignedInteger('rate_plan_id');
            $table->timestamps();

            // $table->foreign('rate_plan_id')
            //     ->references('id')->on('rate_plans')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_rates');
    }
}
