<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtraBedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extra_beds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('extra_bed_policy_clone_id');
            $table->unsignedInteger('user_clone_id');
            $table->unsignedInteger('reservation_id');
            $table->integer('amount');
            $table->integer('nights')->default(1);
            $table->timestamps();

            $table->foreign('extra_bed_policy_clone_id')
                ->references('id')->on('extra_bed_policy_clones')
                ->onDelete('cascade');
            
            $table->foreign('user_clone_id')
                ->references('id')->on('user_clones')
                ->onDelete('cascade');

            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
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
        Schema::dropIfExists('extra_beds');
    }
}
