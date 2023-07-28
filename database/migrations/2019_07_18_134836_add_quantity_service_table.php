<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuantityServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('countable')->default(false)->after('price');
            $table->unsignedInteger('quantity')->nullable()->after('price');
        });

        Schema::table('service_clones', function (Blueprint $table) {
            $table->boolean('countable')->default(false)->after('price');
            $table->unsignedInteger('quantity')->nullable()->after('price');
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
            $table->dropColumn('countable');
            $table->dropColumn('quantity');
        });

        Schema::table('service_clones', function (Blueprint $table) {
            $table->dropColumn('countable');            
            $table->dropColumn('quantity');
        });
    }
}
