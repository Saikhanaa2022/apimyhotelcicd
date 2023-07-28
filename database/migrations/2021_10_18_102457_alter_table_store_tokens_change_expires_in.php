<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableStoreTokensChangeExpiresIn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_tokens', function (Blueprint $table) {
            $table->datetime('expires_in')->change();
            $table->datetime('refresh_expires_in')->change();
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
            $table->dropColumn('expires_in');
            $table->dropColumn('refresh_expires_in');
        });
    }
}
