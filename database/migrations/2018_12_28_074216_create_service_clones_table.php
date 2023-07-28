<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->timestamps();
            
            // $table->foreign('service_id')
            //     ->references('id')->on('services')
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
        Schema::dropIfExists('service_clones');
    }
}
