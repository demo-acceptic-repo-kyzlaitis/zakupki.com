<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieildSignedToAwards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    	Schema::table('awards', function (Blueprint $table) {
    		$table->integer('signed')->after('status');
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
    	Schema::table('awards', function (Blueprint $table) {
    		$table->dropColumn('signed');
    	});
    }
}
