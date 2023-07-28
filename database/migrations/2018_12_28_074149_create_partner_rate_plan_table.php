<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerRatePlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_rate_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('partner_id');
            $table->unsignedInteger('rate_plan_id');

            // $table->foreign('partner_id')
            //     ->references('id')->on('partners')
            //     ->onDelete('cascade');
                
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
        Schema::dropIfExists('partner_rate_plan');
    }
}
