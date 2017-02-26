<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeDateNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dateTime('date')->nullable()->change();
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dateTime('date')->nullable()->change();
        });
        Schema::table('lots', function (Blueprint $table) {
            $table->dateTime('date')->nullable()->change();
        });
        Schema::table('cancellations', function (Blueprint $table) {
            $table->dateTime('date')->nullable()->change();
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
