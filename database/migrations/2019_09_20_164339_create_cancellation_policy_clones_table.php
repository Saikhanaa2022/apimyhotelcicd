<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCancellationPolicyClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cancellation_policy_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cancellation_policy_id');
            $table->unsignedInteger('cancellation_time_id')->nullable();
            $table->unsignedInteger('cancellation_percent_id')->nullable();
            $table->unsignedInteger('addition_percent_id')->nullable(); 
            $table->boolean('is_free')->default(true);
            $table->boolean('has_prepayment')->default(false);
            $table->timestamps();

            $table->foreign('cancellation_policy_id')
                ->references('id')->on('cancellation_policies')
                ->onDelete('cascade');

            $table->foreign('cancellation_time_id')
                ->references('id')->on('cancellation_times')
                ->onDelete('set null');

            $table->foreign('cancellation_percent_id')
                ->references('id')->on('cancellation_percents')
                ->onDelete('set null');

            $table->foreign('addition_percent_id')
                ->references('id')->on('cancellation_percents')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cancellation_policy_clones');
    }
}
