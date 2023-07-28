<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('bar_code')->nullable()->after('countable');
            $table->unsignedInteger('product_category_id')->after('countable')->nullable();

            $table->foreign('product_category_id')
                ->references('id')->on('product_categories')
                ->onDelete('cascade');
        });

        Schema::table('service_clones', function (Blueprint $table) {
            $table->string('bar_code')->nullable()->after('countable');
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
            $table->dropForeign('services_product_category_id_foreign');
            $table->dropColumn('product_category_id');
            $table->dropColumn('bar_code');
        });

        Schema::table('service_clones', function (Blueprint $table) {
            $table->dropColumn('bar_code');
        });
    }
}
