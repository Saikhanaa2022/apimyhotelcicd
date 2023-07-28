<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOccupancyRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occupancy_rate_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rate_plan_id');
            $table->unsignedInteger('occupancy');
            $table->string('discount_type');
            $table->integer('discount');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);    
            $table->timestamps();

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
        Schema::dropIfExists('occupancy_rate_plans');
    }
}
