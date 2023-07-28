<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('percentage');
            $table->boolean('inclusive')->default(false);
            $table->unsignedInteger('reservation_id');
            $table->unsignedInteger('tax_id')->nullable();
            $table->timestamps();

            // $table->foreign('tax_id')
            //     ->references('id')->on('taxes')
            //     ->onDelete('set null');
            
            // $table->foreign('reservation_id')
            //     ->references('id')->on('reservations')
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
        Schema::dropIfExists('tax_clones');
    }
}
