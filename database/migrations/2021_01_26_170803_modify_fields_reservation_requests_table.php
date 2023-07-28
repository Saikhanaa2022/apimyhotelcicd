<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFieldsReservationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_requests', function (Blueprint $table) {
            $table->unsignedInteger('number_of_guests')->nullable()->change();
            $table->unsignedInteger('number_of_children')->nullable()->change();
            $table->boolean('is_group')->nullable()->change();
            $table->boolean('is_org')->nullable()->change();
            $table->boolean('is_paid')->nullable()->change();
            $table->text('transaction_response')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_requests', function (Blueprint $table) {
            //
        });
    }
}
