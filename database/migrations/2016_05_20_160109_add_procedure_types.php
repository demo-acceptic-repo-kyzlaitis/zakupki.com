<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddProcedureTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('procedure_types')->truncate();

        $procedureTypes = [
            'sp'       => 'Допорогова закупівля', // subthreshold purchase -> Допорогова закупівля
            'op'       => 'Відкриті торги',
            'ope'      => 'Відкриті торги з публікацією англ.мовою',
            'ra'       => 'Звіт про укладений договір',
            'np'       => 'Переговорна процедура закупівлі',
            'ppznp'    => 'Переговорна процедура за нагальною потребою',
            'bzestdpp' => 'Без застосування електронної системи тільки для публікації планів',
            'pp'       => 'Переговорна процедура (потреби оборони)',
        ];
        foreach($procedureTypes as $type => $type_name){
            DB::table('procedure_types')->insert([
                'procedure_type' => $type,
                'procedure_name' => $type_name
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

        DB::table('procedure_types')->truncate();

        $procedureTypes = [
            'sp' => 'Допорогова закупівля', // subthreshold purchase -> Допорогова закупівля
            'cd' => 'Конкурентний діалог', // competitive dialogue -> Конкурентний діалог
            'np' => 'Переговорна процедура закупівлі',  // Negotiating purchase -> Переговорна процедура закупівлі (неактивна)
        ];

        foreach($procedureTypes as $type => $type_name){
            DB::table('procedure_types')->insert([
                'procedure_type' => $type,
                'procedure_name' => $type_name
            ]);
        }

    }
}
