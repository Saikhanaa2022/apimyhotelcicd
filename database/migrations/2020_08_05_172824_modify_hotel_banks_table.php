<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyHotelBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_banks', function (Blueprint $table) {
            $table->string('qr_image')->nullable()->after('currency');
            $table->unsignedInteger('bank_id')->nullable()->after('hotel_id');

            $table->foreign('bank_id')->references('id')->on('banks')
                ->onDelete('set null')
                ->onUpdate('no action');

            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotel_banks', function (Blueprint $table) {
            $table->dropColumn('qr_image');
            // $table->dropIndex('hotel_banks_bank_id_foreign');
            $table->dropForeign(['bank_id']);
            $table->dropColumn('bank_id');
            $table->string('name');
        });
    }
}
