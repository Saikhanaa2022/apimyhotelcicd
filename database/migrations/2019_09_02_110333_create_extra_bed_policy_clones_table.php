<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtraBedPolicyClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extra_bed_policy_clones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('min')->nullable();
            $table->unsignedInteger('max')->nullable();
            $table->string('age_type')->default('adults');
            $table->string('price_type')->default('mnt');
            $table->unsignedInteger('price');
            $table->unsignedInteger('extra_bed_policy_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extra_bed_policy_clones');
    }
}
