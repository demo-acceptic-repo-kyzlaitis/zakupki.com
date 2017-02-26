<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddValuesToProsandPs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('products')->truncate();
        DB::table('payment_services')->truncate();


        DB::table('products')->insert([
            'id' => '1',
            'name' => 'Подача предложений',
        ]);
        DB::table('products')->insert([
            'id' => '2',
            'name' => 'Ручной ввод',
        ]);

        DB::table('payment_services')->insert([
            'id' => '1',
            'name' => 'LiqPay',
        ]);
        DB::table('payment_services')->insert([
            'id' => '2',
            'name' => 'Cashless',
        ]);
        DB::table('payment_services')->insert([
            'id' => '3',
            'name' => 'Billing',
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
