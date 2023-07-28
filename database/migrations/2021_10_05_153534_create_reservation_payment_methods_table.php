<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservation_payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->integer('reservation_id');
            $table->integer('transaction_id')->nullable();
            $table->string('number');
            $table->string('payment_method');
            $table->string('lend_invoice_number')->nullable();
            $table->string('lend_qr_string')->nullable();
            $table->string('lend_url')->nullable();
            $table->string('lend_transaction')->nullable();
            $table->string('qpay_qrcode')->nullable();
            $table->string('qpay_qrimage')->nullable();
            $table->string('qpay_url')->nullable();
            $table->string('qpay_qrimage_base64')->nullable();
            $table->string('qpay_invoice_id')->nullable();
            $table->string('qpay_transaction')->nullable();
            $table->string('mc_trans_id')->nullable();
            $table->string('mc_qrcode')->nullable();
            $table->boolean('paid')->default(false);
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
        Schema::dropIfExists('reservation_payment_methods');
    }
}
