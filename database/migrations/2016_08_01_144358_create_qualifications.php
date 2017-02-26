<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQualifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cbd_id')->index();
            $table->integer('bid_id')->index();
            $table->integer('lot_id');
            $table->string('status');
            $table->integer('qualified')->nullable();
            $table->integer('eligible')->nullable();
            $table->string('unsuccessful_title')->nullable();
            $table->text('unsuccessful_description')->nullable();
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
        Schema::drop('qualifications');
    }
}
