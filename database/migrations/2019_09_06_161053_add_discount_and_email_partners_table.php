<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountAndEmailPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->integer('discount')->nullable()->after('phone_number');
            $table->string('email')->nullable()->unique()->after('phone_number');
        });
        Schema::table('partner_clones', function (Blueprint $table) {
            $table->integer('discount')->nullable()->after('phone_number');
            $table->string('email')->nullable()->unique()->after('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('discount');
            $table->dropColumn('email');
        });
        Schema::table('partner_clones', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('discount');
        });
    }
}
