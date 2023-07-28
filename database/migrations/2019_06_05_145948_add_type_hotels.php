<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->unsignedInteger('hotel_type_id')->nullable()->after('address');
            $table->foreign('hotel_type_id')
                ->references('id')->on('hotel_types')
                ->onDelete('set null')
                ->onUpdate('no action');
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
            $table->dropIndex('hotels_hotel_type_id_foreign');
            $table->dropForeign(['hotels_hotel_type_id']);
            $table->dropColumn('hotel_type_id');
        });
    }
}
