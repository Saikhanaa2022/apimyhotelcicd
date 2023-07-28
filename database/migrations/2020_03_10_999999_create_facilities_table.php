<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('facility_category_id')->unsigned()->nullable();
            $table->integer('sync_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('mobile_icon')->nullable();
            $table->boolean('is_default')->default(true);
            $table->boolean('is_most')->default(false);
            $table->timestamps();

            $table->foreign('facility_category_id')->references('id')->on('facility_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facilities');
    }
}
