<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservation_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('res_number')->unique();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('status')->default('pending');
            $table->string('stay_type')->default('night');
            $table->unsignedInteger('stay_nights')->default(1);
            $table->unsignedInteger('number_of_guests')->default(1);
            $table->unsignedInteger('number_of_children')->default(0);
            $table->text('age_of_children')->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('amount_paid')->nullable()->default(0);
            $table->float('discount_avg_percent', 8, 2)->default(0);
            $table->text('guest');
            $table->boolean('is_group')->default(false);
            $table->boolean('is_org')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->dateTime('paid_at')->nullable();
            $table->text('transaction_response')->nullable(0);
            $table->text('notes')->nullable();
            $table->unsignedInteger('number_of_rooms')->default(1);
            $table->unsignedInteger('sync_id')->nullable();
            $table->unsignedInteger('hotel_id');
            $table->unsignedInteger('source_clone_id');
            $table->timestamps();

            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
                ->onDelete('cascade');
            $table->foreign('source_clone_id')
                ->references('id')->on('source_clones')
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
        Schema::dropIfExists('reservation_requests');
    }
}
