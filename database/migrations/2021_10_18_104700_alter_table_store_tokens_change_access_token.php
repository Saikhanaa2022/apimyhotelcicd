<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableStoreTokensChangeAccessToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_tokens', function (Blueprint $table) {
            $table->text('access_token')->change();
            $table->text('refresh_token')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_tokens', function (Blueprint $table) {
            $table->dropColumn('access_token');
            $table->dropColumn('refresh_token');
        });
    }
}
