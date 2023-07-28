<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalFieldHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->integer('zip_code')->nullable()->after('address');
            $table->string('res_email')->nullable()->after('phone');
            $table->string('res_phone')->nullable()->after('phone');
            $table->unsignedInteger('wubook_lcode')->nullable()->after('register_no');
            $table->dropColumn('account_number');
            $table->dropColumn('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('zip_code');
            $table->dropColumn('res_email');
            $table->dropColumn('res_phone');
            $table->dropColumn('wubook_lcode');
            $table->string('account_number')->nullable()->after('address');
            $table->string('bank_name')->nullable()->after('address');
        });
    }
}
