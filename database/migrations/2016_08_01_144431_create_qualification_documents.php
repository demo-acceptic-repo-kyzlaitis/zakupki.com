<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQualificationDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qualification_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('orig_id')->index();
            $table->integer('qualification_id')->index();
            $table->integer('type_id');
            $table->string('path');
            $table->string('title')->nullable();
            $table->string('url')->nullable();
            $table->string('format')->nullable();
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
        Schema::drop('qualification_documents');
    }
}
