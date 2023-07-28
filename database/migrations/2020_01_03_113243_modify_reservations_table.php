<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->datetime('check_in')->change();
            $table->datetime('check_out')->change();
            $table->boolean('is_time')->default(false)->after('check_out');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('check_in')->change();
            $table->date('check_out')->change();
            $table->dropColumn('is_time');
        });
    }
}
