<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsBlockDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->text('description')->nullable()->after('nationality');
            $table->boolean('is_blacklist')->default(false)->after('description');
            $table->text('blacklist_reason')->nullable()->after('is_blacklist');
        });

        Schema::table('guest_clones', function (Blueprint $table) {
            $table->text('description')->nullable()->after('nationality');
            $table->boolean('is_blacklist')->default(false)->after('description');
            $table->text('blacklist_reason')->nullable()->after('is_blacklist');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('is_blacklist');
            $table->dropColumn('blacklist_reason');
        });

        Schema::table('guest_clones', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('is_blacklist');
            $table->dropColumn('blacklist_reason');
        });
    }
}
