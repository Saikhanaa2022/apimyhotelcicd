<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('type')->default('contracter')->nullable()->after('register_no');
        });

        Schema::table('partner_clones', function (Blueprint $table) {
            $table->string('type')->default('contracter')->nullable()->after('register_no');
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
            $table->dropColumn('type');
        });

        Schema::table('partner_clones', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
