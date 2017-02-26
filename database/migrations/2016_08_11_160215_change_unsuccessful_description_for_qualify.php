<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeUnsuccessfulDescriptionForQualify extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->text('unsuccessful_description')->nullable()->change();
        });
        Schema::table('qualifications', function (Blueprint $table) {
            $table->text('unsuccessful_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->string('unsuccessful_description')->nullable()->change();
        });
        Schema::table('qualifications', function (Blueprint $table) {
            $table->string('unsuccessful_description')->nullable()->change();
        });
    }
}
