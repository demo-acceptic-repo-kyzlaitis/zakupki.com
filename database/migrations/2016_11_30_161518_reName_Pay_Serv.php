<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReNamePayServ extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('payment_services')->truncate();

        DB::table('payment_services')->insert([
            'id' => '1',
            'name' => 'Cashless',
        ]);
        DB::table('payment_services')->insert([
            'id' => '3',
            'name' => 'Billing',
        ]);
        DB::table('payment_services')->insert([
            'id' => '4',
            'name' => 'LiqPay',
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
