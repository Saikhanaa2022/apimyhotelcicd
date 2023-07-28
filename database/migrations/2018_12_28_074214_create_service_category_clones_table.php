<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceCategoryClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_category_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('service_category_id')->nullable();
            $table->timestamps();
            
            // $table->foreign('service_category_id')
            //     ->references('id')->on('service_categories')
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
        Schema::dropIfExists('service_category_clones');
    }
}
