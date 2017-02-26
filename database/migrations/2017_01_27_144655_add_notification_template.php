<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notification_tmp')->insert([
            'alias' => 'bid.lose',
            'title' => 'По закупівлі визначено переможця',
            'description' => 'По закупівлі, в якій Ви приймали участь (№ [[tender_link]]). ' .
                'Замовник оприлюднив повідомлення про намір укласти договір з учасником [[organization_name]], що знаходиться за адресою  ' .
                '[[organization_address]. У Вас є можливість оскаржити рішення Замовника [[text]]',
            'lang' => 'ua',
            'active' => 1,
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
