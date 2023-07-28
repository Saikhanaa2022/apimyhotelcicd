<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatIdGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('vat_id')->nullable()->after('hotel_id');
            $table->string('qr')->nullable()->after('hotel_id');
            $table->string('ddtd')->nullable()->after('hotel_id');
            $table->string('lottery')->nullable()->after('hotel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('vat_id');
            $table->dropColumn('qr');
            $table->dropColumn('ddtd');
            $table->dropColumn('lottery');
        });
    }
}
