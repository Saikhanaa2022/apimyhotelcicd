<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sync_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('international')->nullable();
            $table->string('code')->nullable();
            $table->string('locale')->nullable();
            $table->string('curriency_id')->nullable();
            $table->string('image')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->integer('order_no')->default(0)->nullable();
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
        Schema::dropIfExists('countries');
    }
}
