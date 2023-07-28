<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_pays', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('payment_method_clone_id');
            $table->integer('amount');            

            $table->foreign('payment_id')
                ->references('id')->on('payments')
                ->onDelete('cascade');

            $table->foreign('payment_method_clone_id')
                ->references('id')->on('payment_method_clones')
                ->onDelete('cascade');
            
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
        Schema::dropIfExists('payment_pays');
    }
}
