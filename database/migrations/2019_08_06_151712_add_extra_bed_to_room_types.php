<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraBedToRoomTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedInteger('occupancy_children')->default(0)->after('occupancy');
            $table->unsignedInteger('extra_beds')->nullable()->after('occupancy');
            $table->boolean('has_extra_bed')->default(false)->after('occupancy');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->unsignedInteger('occupancy_children')->default(0)->after('occupancy');
            $table->unsignedInteger('extra_beds')->nullable()->after('occupancy');
            $table->boolean('has_extra_bed')->default(false)->after('occupancy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('occupancy_children');
            $table->dropColumn('extra_beds');
            $table->dropColumn('has_extra_bed');
        });

        Schema::table('room_type_clones', function (Blueprint $table) {
            $table->dropColumn('occupancy_children');
            $table->dropColumn('extra_beds');
            $table->dropColumn('has_extra_bed');
        });
    }
}
