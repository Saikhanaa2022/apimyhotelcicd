<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCancellationPolicyCloneReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedInteger('cancellation_policy_clone_id')->nullable()->after('group_id');

            $table->foreign('cancellation_policy_clone_id')
                ->references('id')->on('cancellation_policy_clones')
                ->onDelete('set null');
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
            $table->dropForeign('reservations_cancellation_policy_clone_id_foreign');
            $table->dropIndex('reservations_cancellation_policy_clone_id_foreign');
            $table->dropColumn('cancellation_policy_clone_id');
        });
    }
}
