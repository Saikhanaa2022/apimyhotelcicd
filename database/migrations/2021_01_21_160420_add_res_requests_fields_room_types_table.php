<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResRequestsFieldsRoomTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->text('discount_percent')->nullable()->after('description');
            $table->boolean('is_res_request')->nullable()->default(false)->after('discount_percent');
            $table->integer('sale_quantity')->nullable()->after('is_res_request');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->text('discount_percent')->nullable()->after('room_type_id');
            $table->boolean('is_res_request')->nullable()->default(false)->after('discount_percent');
            $table->integer('sale_quantity')->nullable()->after('is_res_request');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
            $table->dropColumn('is_res_request');
            $table->dropColumn('sale_quantity');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
            $table->dropColumn('is_res_request');
            $table->dropColumn('sale_quantity');
        });
    }
}
