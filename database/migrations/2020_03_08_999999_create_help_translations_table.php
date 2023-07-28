<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('translation_id')->unsigned();
            $table->string('locale')->default('en');
            $table->string('name');
            $table->string('url')->nullable();
            $table->longText('content');
            $table->timestamps();

            $table->foreign('translation_id')->references('id')->on('helps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('help_translations');
    }
}
