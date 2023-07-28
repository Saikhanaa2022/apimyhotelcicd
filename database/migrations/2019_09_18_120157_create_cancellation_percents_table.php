<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCancellationPercentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cancellation_percents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('percent')->default(0);
            $table->boolean('is_first_night')->default(false);
            // $table->unsignedInteger('parent_id')->nullable();
            $table->timestamps();

            // $table->foreign('parent_id')
                // ->references('id')->on('cancellation_percents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cancellation_percents');
    }
}
