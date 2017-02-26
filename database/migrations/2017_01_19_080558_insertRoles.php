<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')->insert(array(
            array('code' => 'admin', 'name' => 'Администратор', 'value' => '1'),
            array('code' => 'manager', 'name' => 'Менеджер', 'value' => '10'),
            array('code' => 'business', 'name' => 'Бизнес пертнер', 'value' => '20')
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->where('code', 'admin')->delete();
        DB::table('roles')->where('code', 'manager')->delete();
        DB::table('roles')->where('code', 'business')->delete();
    }
}
