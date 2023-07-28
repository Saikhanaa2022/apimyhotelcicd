<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQpayInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qpay_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('res_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->integer('reservation_id')->nullable();
            $table->integer('room_id');
            $table->integer('room_type_id');
            $table->string('number')->nullable();
            $table->integer('amount');
            $table->string('payment_method');
            $table->string('qpay_qrcode');
            $table->string('qpay_qrimage');
            $table->string('qpay_url');
            $table->string('qpay_urls');
            $table->string('qpay_qrimage_base64');
            $table->string('qpay_invoice_id');
            $table->string('qpay_transaction');
            $table->string('token');
            $table->string('stay_type');
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
        Schema::dropIfExists('qpay_invoices');
    }
}
