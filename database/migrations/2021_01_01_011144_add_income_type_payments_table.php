<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncomeTypePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('income_type')->nullable()->default('paid')->after('notes');
            $table->longText('income_pays')->nullable()->after('income_type');
            $table->dateTime('paid_at')->nullable()->after('income_pays');
            $table->text('payer')->nullable()->after('paid_at');
            $table->date('posted_date')->nullable()->after('paid_at');
            $table->unsignedInteger('ref_id')->nullable()->after('reservation_id');
            $table->boolean('is_audited')->nullable()->default(false)->after('is_active');
            $table->boolean('is_ignored')->nullable()->default(false)->after('is_audited');
            $table->string('ignored_reason')->nullable()->after('is_ignored');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('posted_date');
            $table->dropColumn('income_type');
            $table->dropColumn('income_pays');
            $table->dropColumn('paid_at');
            $table->dropColumn('payer');
            $table->dropColumn('ref_id');
            $table->dropColumn('is_audited');
            $table->dropColumn('is_ignored');
            $table->dropColumn('ignored_reason');
            $table->dropSoftDeletes();
        });
    }
}
