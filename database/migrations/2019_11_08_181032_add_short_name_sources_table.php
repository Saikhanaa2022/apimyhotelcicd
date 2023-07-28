<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShortNameSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->string('short_name')->after('name')->nullable();
        });

        Schema::table('source_clones', function (Blueprint $table) {
            $table->string('short_name')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });

        Schema::table('source_clones', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });
    }
}
