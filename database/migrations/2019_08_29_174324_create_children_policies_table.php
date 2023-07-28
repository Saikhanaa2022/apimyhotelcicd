<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChildrenPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('children_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('hotel_id');
            $table->unsignedInteger('min')->nullable();
            $table->unsignedInteger('max')->nullable();
            $table->string('age_type')->default('children');
            $table->string('price_type')->default('mnt');
            $table->unsignedInteger('price');
            $table->timestamps();

            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
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
        Schema::dropIfExists('children_policies');
    }
}
