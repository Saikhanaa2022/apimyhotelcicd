<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransHotelCreatedToReservationPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservation_payment_methods', function (Blueprint $table) {
            $table->boolean('trans_hotel_created')->default(false)->after('paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservation_payment_methods', function (Blueprint $table) {
            $table->dropColumn('trans_hotel_created');
        });
    }
}
