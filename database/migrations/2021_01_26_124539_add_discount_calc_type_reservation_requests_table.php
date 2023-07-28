<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountCalcTypeReservationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_requests', function (Blueprint $table) {
            $table->string('discount_calc_type')->nullable()->default('perPercent')->after('amount_paid');
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
            $table->dropColumn('discount_calc_type');
        });
    }
}
