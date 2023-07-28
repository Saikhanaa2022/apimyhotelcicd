<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPartnerAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('partner_clone_id')->nullable();
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedInteger('reservation_id')->nullable();
            $table->float('amount', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onDelete('cascade');

            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_accounts');
    }
}
