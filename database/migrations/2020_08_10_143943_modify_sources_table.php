<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_default');
            $table->string('service_name')->nullable()->after('is_active');
        });

        Schema::table('source_clones', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_default');
            $table->string('service_name')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('service_name');
        });

        Schema::table('source_clones', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('service_name');
        });
    }
}
