<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contract_id')->index();
            $table->integer('tender_id')->index();
            $table->string('cbd_id')->index();
            $table->string('status')->index();
            $table->string('rationale');
            $table->integer('rationale_type_id');
            $table->string('contract_number');
            $table->dateTime('date')->nullable();
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
        Schema::drop('contract_changes');
    }
}
