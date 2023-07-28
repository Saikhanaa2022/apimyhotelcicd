<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPartnerServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('partner_id')->after('bar_code')->nullable();

            $table->foreign('partner_id')
                ->references('id')->on('partners')
                ->onDelete('cascade');
        });

        Schema::table('service_clones', function (Blueprint $table) {
            $table->unsignedInteger('partner_id')->after('bar_code')->nullable();

            $table->foreign('partner_id')
                ->references('id')->on('partners')
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
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign('services_partner_id_foreign');
            $table->dropColumn('partner_id');
        });

        Schema::table('service_clones', function (Blueprint $table) {
            $table->dropForeign('service_clones_partner_id_foreign');
            $table->dropColumn('partner_id');
        });
    }
}
