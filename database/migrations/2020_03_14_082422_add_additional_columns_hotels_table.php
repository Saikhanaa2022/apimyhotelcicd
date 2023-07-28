<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnsHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->unsignedInteger('district_id')->nullable()->after('sync_id');
            $table->string('slug')->nullable()->after('hotel_type_id');
            $table->longText('images')->nullable()->after('image');
            $table->text('description')->nullable()->after('images');
            $table->integer('star_rating')->nullable()->after('is_active');
            $table->boolean('is_closed')->nullable()->after('is_active');
            $table->string('location')->nullable()->after('max_time');
            $table->string('default_locale')->nullable()->after('max_time');
            $table->string('website')->nullable()->after('max_time');

            $table->foreign('district_id')
                ->references('id')->on('districts')
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
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropForeign('hotels_district_id_foreign');
            $table->dropIndex('hotels_district_id_foreign');
            $table->dropColumn('district_id');
            $table->dropColumn('slug');
            $table->dropColumn('images');
            $table->dropColumn('description');
            $table->dropColumn('star_rating');
            $table->dropColumn('is_closed');
            $table->dropColumn('location');
            $table->dropColumn('default_locale');
            $table->dropColumn('website');
        });
    }
}
