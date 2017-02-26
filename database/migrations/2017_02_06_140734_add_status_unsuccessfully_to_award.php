<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusUnsuccessfullyToAward extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
	DB::table('statuses')->insert(array(
            array('namespace' => 'award', 'status' => 'unsuccessfully', 'description' => 'В процесі відхилення' , 'style' => 'default')
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
	DB::table('statuses')->where(['namespace','=','award'],['status','=','unsuccessfully'])->delete();
    }
}
