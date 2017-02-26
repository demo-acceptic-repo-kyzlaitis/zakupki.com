<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('statuses')->insert([
            'namespace' => 'complaint',
            'status' => 'mistaken',
            'description' => 'Помилково',
            'style' => 'danger'
        ]);
        DB::table('statuses')->insert([
            'namespace' => 'complaint',
            'status' => 'stopped',
            'description' => 'Зупинено',
            'style' => 'danger'
        ]);
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
