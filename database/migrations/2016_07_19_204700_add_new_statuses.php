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
            'namespace' => 'tender',
            'status' => 'active.pre-qualification.stand-still',
            'description' => 'Прекваліфікація (період оскаржень)',
            'style' => 'warning'
        ]);
        DB::table('statuses')->insert([
            'namespace' => 'tender',
            'status' => 'active.pre-qualification',
            'description' => 'Прекваліфікація',
            'style' => 'warning'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('statuses')->where('status', 'active.pre-qualification.stand-still')->delete();
        DB::table('statuses')->where('status', 'active.pre-qualification')->delete();
    }
}
