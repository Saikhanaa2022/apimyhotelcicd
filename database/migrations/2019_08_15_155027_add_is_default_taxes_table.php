<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDefaultTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->string('key')->nullable()->after('name');
            $table->boolean('is_default')->default(false)->after('name');
            $table->boolean('is_enabled')->default(false)->after('inclusive');
        });

        Schema::table('tax_clones', function (Blueprint $table) {
            $table->string('key')->nullable()->after('name');
            $table->boolean('is_default')->default(false)->after('name');
            $table->boolean('is_enabled')->default(false)->after('inclusive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn('key');
            $table->dropColumn('is_default');
            $table->dropColumn('is_enabled');
        });

        Schema::table('tax_clones', function (Blueprint $table) {
            $table->dropColumn('key');
            $table->dropColumn('is_default');
            $table->dropColumn('is_enabled');
        });
    }
}
