<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGroundsForRejection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grounds_for_rejection', function (Blueprint $table) {
            $table->increments('id');
            $table->string('namespace');
            $table->string('bid_status');
            $table->string('code');
            $table->string('title');
            $table->string('description');
            $table->boolean('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('grounds_for_rejection');
    }
}
