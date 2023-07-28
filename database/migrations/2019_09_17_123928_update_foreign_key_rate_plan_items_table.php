<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateForeignKeyRatePlanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_plan_items', function (Blueprint $table) {
            $table->dropForeign('rate_plan_items_service_id_foreign');
            $table->dropIndex('rate_plan_items_service_id_foreign');
            $table->dropColumn('service_id');
        });

        Schema::table('rate_plan_items', function (Blueprint $table) {
            $table->unsignedInteger('service_id')->after('service_category_id');

            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rate_plan_items', function (Blueprint $table) {
            //
        });
    }
}
