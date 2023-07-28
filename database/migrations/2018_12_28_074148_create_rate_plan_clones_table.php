<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatePlanClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_plan_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('is_daily')->default(false);
            $table->unsignedInteger('rate_plan_id')->nullable();
            $table->timestamps();
            
            // $table->foreign('rate_plan_id')
            //     ->references('id')->on('rate_plans')
            //     ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rate_plan_clones');
    }
}
