<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmenityCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amenity_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sync_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('mobile_icon')->nullable();
            $table->boolean('is_default')->default(true);
            $table->boolean('is_most')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amenity_categories');
    }
}
