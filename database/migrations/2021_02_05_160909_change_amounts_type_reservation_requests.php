<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountsTypeReservationRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_requests', function (Blueprint $table) {
            $table->float('amount', 8, 2)->change();
            $table->float('amount_paid', 8, 2)->change();
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
            $table->integer('amount')->change();
            $table->integer('amount_paid')->change();
        });
    }
}
