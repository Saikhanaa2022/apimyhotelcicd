<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->unsignedInteger('wubook_id')->nullable()->after('id');
            $table->string('currency')->nullable()->default('USD')->after('code');
            $table->boolean('is_active')->default(true)->after('code');
            $table->string('logo')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('wubook_id');
            $table->dropColumn('currency');
            $table->dropColumn('is_active');
            $table->dropColumn('logo');
        });
    }
}
