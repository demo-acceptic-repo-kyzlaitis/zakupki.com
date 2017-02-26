<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cbd_id', 255)->index();
            $table->string('access_token');
            $table->string('planID', 255)->index();
            $table->bigInteger('amount_net');
            $table->integer('organization_id');
            $table->string('description', 255);
            $table->text('notes');
            $table->string('year');
            $table->bigInteger('amount');
            $table->integer('currency_id');
            $table->integer('procedure_id');
            $table->integer('code_id');
            $table->integer('code_additional_id');
            $table->integer('code_kekv_id');
            $table->date('start_date');
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
        Schema::drop('plans');
    }
}
