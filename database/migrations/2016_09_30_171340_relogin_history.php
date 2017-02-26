<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ReloginHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relogin_history', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('nominal_user');
            $table->integer('to_user');
            $table->string('action');
            $table->string('entity');
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
        //
    }
}
