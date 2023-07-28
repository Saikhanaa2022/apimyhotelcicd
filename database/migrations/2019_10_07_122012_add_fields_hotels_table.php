<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('account_number')->nullable()->after('address');
            $table->string('bank_name')->nullable()->after('address');
            $table->string('phone')->nullable()->after('address');
            $table->string('email')->nullable()->after('address');
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
            $table->dropColumn('account_number');
            $table->dropColumn('bank_name');
            $table->dropColumn('phone');
            $table->dropColumn('email');

        });
    }
}
