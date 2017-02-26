<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanDocumnts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('orig_id', 255)->index();
            $table->integer('plan_id')->index();
            $table->string('path', 1024);
            $table->string('format', 255);
            $table->string('url', 255);
            $table->string('title', 512);
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
        Schema::drop('plan_documents');
    }
}
