<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourceTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('translation_id')->unsigned();
            $table->integer('sync_id')->unsigned()->nullable();
            $table->string('locale')->default('en');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->timestamps();

            $table->foreign('translation_id')->references('id')->on('sources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('source_translations');
    }
}
