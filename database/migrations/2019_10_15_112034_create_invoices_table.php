<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id');
            $table->unsignedInteger('reservation_id');
            $table->unsignedInteger('hotel_id');
            $table->string('email')->nullable();
            $table->string('reservation_number')->nullable();
            $table->string('hotel_image')->nullable();
            $table->string('hotel_name')->nullable();
            $table->string('hotel_register_no')->nullable();
            $table->string('hotel_address')->nullable();
            $table->string('hotel_phone_number')->nullable();
            $table->string('hotel_email')->nullable();
            $table->string('hotel_company_name')->nullable();
            $table->string('hotel_bank_name')->nullable();
            $table->string('hotel_account_number')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_surname')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('register_no')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('contract_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('payment_period')->nullable();
            $table->boolean('is_sent')->default(0);
            $table->timestamps();

            $table->foreign('group_id')
                ->references('id')->on('groups')
                ->onDelete('cascade');

            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');

            $table->foreign('hotel_id')
                ->references('id')->on('hotels')
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
        Schema::dropIfExists('invoices');
    }
}
