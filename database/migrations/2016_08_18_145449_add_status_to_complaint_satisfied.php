<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStatusToComplaintSatisfied extends Migration
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
            'status' => 'satisfied',
            'description' => 'Вирішено',
            'style' => 'success',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('statuses')
            ->where('namespace', 'complaint')
            ->where('status', 'satisfied')
            ->delete();
    }
}
