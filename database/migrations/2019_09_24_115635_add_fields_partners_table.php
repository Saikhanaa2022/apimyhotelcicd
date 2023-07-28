<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('register_no')->nullable()->after('name');

            $table->string('address')->nullable()->after('email');
            $table->string('finance_email')->nullable()->after('email');
            $table->string('finance_phone_number')->nullable()->after('email');
            $table->string('finance_person')->nullable()->after('email');
        });

        Schema::table('partner_clones', function (Blueprint $table) {
            $table->string('register_no')->nullable()->after('name');

            $table->string('address')->nullable()->after('email');
            $table->string('finance_email')->nullable()->after('email');
            $table->string('finance_phone_number')->nullable()->after('email');
            $table->string('finance_person')->nullable()->after('email');
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
            $table->dropColumn('register_no');
            $table->dropColumn('finance_person');
            $table->dropColumn('finance_phone_number');
            $table->dropColumn('finance_email');
            $table->dropColumn('address');
        });


        Schema::table('partner_clones', function (Blueprint $table) {
            $table->dropColumn('register_no');
            $table->dropColumn('finance_person');
            $table->dropColumn('finance_phone_number');
            $table->dropColumn('finance_email');
            $table->dropColumn('address');
        });
    }
}
