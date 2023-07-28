<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsFetchPaymentReservationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_requests', function (Blueprint $table) {
            $table->boolean('is_fetch_payment')->nullable()->default(false)->after('is_paid');
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
            $table->dropColumn('is_fetch_payment');
        });
    }
}
