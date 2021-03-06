<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmptyClassifierInCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('codes')->insert([
            'parent_id' => '0',
            'type' => '6',
            'code' => '0',
            'description' => 'Відсутні'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('codes')->where('type', '6')->where('code', '0')->delete();
    }
}
