<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationTMP extends Migration
{
    private $templates = [
        [
            'alias' => 'claim.tender.new.info',
            'title' => 'Вимога',
            'description' => 'На закупівлю № [[tender_link]] на яку ви подали пропозицію надійшла Вимога.',
            'lang' => 'ua',
            'active' => 1,
        ],
        [
            'alias' => 'complaint.tender.new.info',
            'title' => 'Скарга',
            'description' => 'На закупівлю № [[tender_link]] на яку ви подали пропозицію надійшла Скарга.',
            'lang' => 'ua',
            'active' => 1,
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->templates as $template) {
            DB::table('notification_tmp')->insert([
                'alias' => $template['alias'],
                'title' => $template['title'],
                'description' => $template['description'],
                'lang' => $template['lang'],
                'active' => $template['active'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->templates as $template) {
            DB::table('notification_tmp')->where('alias', $template['alias']);
        }
    }
}
