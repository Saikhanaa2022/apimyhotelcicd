<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerIdentityToQpayInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qpay_invoices', function (Blueprint $table) {
            $table->string('customer_identity')->default(null)->after('room_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qpay_invoices', function (Blueprint $table) {
            $table->dropColumn('customer_identity');
        });
    }
}
