<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPaidPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('is_paid')->nullable()->default(true)->after('is_default');
            $table->text('income_types')->nullable()->after('is_default');
        });

        Schema::table('payment_method_clones', function (Blueprint $table) {
            $table->boolean('is_paid')->nullable()->default(true)->after('is_default');
            $table->text('income_types')->nullable()->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_paid');
            $table->dropColumn('income_types');
        });

        Schema::table('payment_method_clones', function (Blueprint $table) {
            $table->dropColumn('is_paid');
            $table->dropColumn('income_types');
        });
    }
}
