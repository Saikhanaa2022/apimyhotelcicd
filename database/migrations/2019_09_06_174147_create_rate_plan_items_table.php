<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatePlanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_plan_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('service_category_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('rate_plan_id');
            $table->timestamps();

            $table->foreign('service_id')
                ->references('id')->on('items')
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
        Schema::dropIfExists('rate_plan_items');
    }
}
