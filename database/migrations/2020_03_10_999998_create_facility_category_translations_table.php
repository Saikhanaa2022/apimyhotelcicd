<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacilityCategoryTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_category_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('translation_id')->unsigned();
            $table->integer('sync_id')->unsigned()->nullable();
            $table->string('locale')->default('en');
            $table->string('name');
            $table->timestamps();

            $table->foreign('translation_id')->references('id')->on('facility_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facility_category_translations');
    }
}
